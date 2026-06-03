import cors from 'cors';
import express from 'express';
import helmet from 'helmet';
import morgan from 'morgan';
import { PrismaClient } from '@prisma/client';
import { z } from 'zod';

const prisma = new PrismaClient();
const app = express();
const port = Number(process.env.PORT || 3000);
const apiToken = process.env.API_TOKEN || 'change-me';

app.use(helmet());
app.use(cors());
app.use(express.json({ limit: '1mb' }));
app.use(morgan('combined'));

app.use((req, res, next) => {
  if (req.path === '/health') return next();
  const token = (req.headers.authorization || '').replace('Bearer ', '');
  if (token !== apiToken) return res.status(401).json({ message: 'Unauthorized' });
  next();
});

const itemSchema = z.object({
  sku: z.string().trim().min(1).max(80),
  name: z.string().trim().min(1).max(160),
  category: z.string().trim().min(1).max(120),
  location: z.string().trim().min(1).max(120),
  supplier: z.string().trim().max(120).optional().nullable(),
  unit: z.string().trim().min(1).max(40),
  quantity: z.coerce.number().int().min(0),
  minimum_stock: z.coerce.number().int().min(0),
  unit_price: z.coerce.number().min(0),
  notes: z.string().trim().max(1000).optional().nullable()
});

const movementSchema = z.object({
  type: z.enum(['IN', 'OUT', 'DAMAGED', 'ADJUSTMENT']),
  quantity: z.coerce.number().int().min(1),
  reference: z.string().trim().max(120).optional().nullable(),
  actor: z.string().trim().min(1).max(120),
  notes: z.string().trim().max(1000).optional().nullable()
});

const messageSchema = z.object({
  author: z.string().trim().min(1).max(120),
  message: z.string().trim().min(1).max(1000)
});

app.get('/health', async (_req, res) => {
  await prisma.$queryRaw`select 1`;
  res.json({ ok: true, service: 'prisma-api' });
});

app.get('/dashboard', async (_req, res) => {
  const [items, recentMovements] = await Promise.all([
    prisma.item.findMany({ orderBy: { createdAt: 'desc' } }),
    prisma.stockMovement.findMany({ take: 10, orderBy: { createdAt: 'desc' }, include: { item: true } })
  ]);

  const summary = summarizeItems(items);
  const lowStockItems = items
    .filter((item) => item.quantity <= item.minimumStock)
    .sort((a, b) => a.quantity - b.quantity)
    .slice(0, 8);
  summary.lowStock = items.filter((item) => item.quantity <= item.minimumStock).length;

  res.json({ summary, lowStockItems, recentMovements });
});

app.get('/items', async (req, res) => {
  const where = buildItemWhere(req.query);
  let items = await prisma.item.findMany({ where, orderBy: [{ quantity: 'asc' }, { name: 'asc' }] });
  if (req.query.stock === 'low') items = items.filter((item) => item.quantity <= item.minimumStock);
  res.json({ items, summary: summarizeItems(items) });
});

app.post('/items', async (req, res) => {
  const payload = itemSchema.parse(req.body);
  const item = await prisma.item.create({
    data: {
      sku: payload.sku,
      name: payload.name,
      category: payload.category,
      location: payload.location,
      supplier: payload.supplier || null,
      unit: payload.unit,
      quantity: payload.quantity,
      minimumStock: payload.minimum_stock,
      unitPrice: payload.unit_price,
      notes: payload.notes || null,
      auditLogs: {
        create: {
          action: 'ITEM_CREATED',
          actor: 'system',
          payload
        }
      }
    }
  });

  await createStockNotification(item);
  res.status(201).json(item);
});

app.get('/items/:id', async (req, res) => {
  const item = await prisma.item.findUnique({
    where: { id: req.params.id },
    include: {
      movements: { orderBy: { createdAt: 'desc' }, take: 30 },
      messages: { orderBy: { createdAt: 'desc' }, take: 30 },
      auditLogs: { orderBy: { createdAt: 'desc' }, take: 20 }
    }
  });
  if (!item) return res.status(404).json({ message: 'Item not found' });
  res.json(item);
});

app.post('/items/:id/movements', async (req, res) => {
  const payload = movementSchema.parse(req.body);
  const result = await prisma.$transaction(async (tx) => {
    const item = await tx.item.findUnique({ where: { id: req.params.id } });
    if (!item) throw Object.assign(new Error('Item not found'), { status: 404 });

    const nextQuantity = calculateNextQuantity(item.quantity, payload.type, payload.quantity);
    if (nextQuantity < 0) throw Object.assign(new Error('Stok tidak cukup untuk transaksi ini'), { status: 422 });

    const movement = await tx.stockMovement.create({
      data: {
        itemId: item.id,
        type: payload.type,
        quantity: payload.quantity,
        reference: payload.reference || null,
        actor: payload.actor,
        notes: payload.notes || null
      },
      include: { item: true }
    });

    const updated = await tx.item.update({
      where: { id: item.id },
      data: {
        quantity: nextQuantity,
        auditLogs: {
          create: {
            action: `STOCK_${payload.type}`,
            actor: payload.actor,
            payload: { before: item.quantity, after: nextQuantity, movement: payload }
          }
        }
      }
    });

    await tx.notification.create({
      data: {
        type: 'MOVEMENT',
        title: `Stok ${movement.item.name} diperbarui`,
        body: `${payload.actor} mencatat ${payload.type} sebanyak ${payload.quantity} ${movement.item.unit}.`
      }
    });

    return { movement, item: updated };
  });

  await createStockNotification(result.item);
  res.status(201).json(result);
});

app.post('/items/:id/messages', async (req, res) => {
  const payload = messageSchema.parse(req.body);
  const item = await prisma.item.findUnique({ where: { id: req.params.id } });
  if (!item) return res.status(404).json({ message: 'Item not found' });

  const message = await prisma.message.create({
    data: {
      itemId: item.id,
      author: payload.author,
      message: payload.message
    }
  });

  await prisma.notification.create({
    data: {
      type: 'SYSTEM',
      title: `Pesan baru untuk ${item.name}`,
      body: `${payload.author}: ${payload.message}`
    }
  });

  res.status(201).json(message);
});

app.get('/reports', async (req, res) => {
  const itemWhere = buildItemWhere(req.query);
  const movementWhere = buildMovementWhere(req.query);
  const [items, movements] = await Promise.all([
    prisma.item.findMany({ where: itemWhere, orderBy: { name: 'asc' } }),
    prisma.stockMovement.findMany({
      where: { ...movementWhere, item: itemWhere },
      include: { item: true },
      orderBy: { createdAt: 'desc' },
      take: 500
    })
  ]);

  const summary = summarizeItems(items);
  summary.in = movements.filter((movement) => movement.type === 'IN').reduce((sum, movement) => sum + movement.quantity, 0);
  summary.out = movements
    .filter((movement) => ['OUT', 'DAMAGED'].includes(movement.type))
    .reduce((sum, movement) => sum + movement.quantity, 0);

  res.json({ summary, items, movements });
});

app.get('/notifications', async (_req, res) => {
  const notifications = await prisma.notification.findMany({ orderBy: { createdAt: 'desc' }, take: 80 });
  res.json({ notifications });
});

app.post('/notifications/:id/read', async (req, res) => {
  const notification = await prisma.notification.update({
    where: { id: req.params.id },
    data: { readAt: new Date() }
  });
  res.json(notification);
});

app.use((err, _req, res, _next) => {
  if (err instanceof z.ZodError) return res.status(422).json({ message: 'Validation error', errors: err.flatten() });
  res.status(err.status || 500).json({ message: err.message || 'Internal server error' });
});

function buildItemWhere(query) {
  const where = {};
  if (query.q) {
    where.OR = [
      { sku: { contains: String(query.q), mode: 'insensitive' } },
      { name: { contains: String(query.q), mode: 'insensitive' } }
    ];
  }
  if (query.category) where.category = { contains: String(query.category), mode: 'insensitive' };
  if (query.location) where.location = { contains: String(query.location), mode: 'insensitive' };
  if (query.stock === 'empty') where.quantity = 0;
  return where;
}

function buildMovementWhere(query) {
  const where = {};
  if (query.from || query.to) {
    where.createdAt = {};
    if (query.from) where.createdAt.gte = new Date(`${query.from}T00:00:00.000Z`);
    if (query.to) where.createdAt.lte = new Date(`${query.to}T23:59:59.999Z`);
  }
  return where;
}

function summarizeItems(items) {
  return items.reduce((summary, item) => {
    summary.items += 1;
    summary.stock += item.quantity;
    summary.value += Number(item.unitPrice) * item.quantity;
    return summary;
  }, { items: 0, stock: 0, value: 0, lowStock: 0 });
}

function calculateNextQuantity(current, type, quantity) {
  if (type === 'IN') return current + quantity;
  if (type === 'ADJUSTMENT') return quantity;
  return current - quantity;
}

async function createStockNotification(item) {
  if (item.quantity === 0) {
    await prisma.notification.create({
      data: {
        type: 'OUT_OF_STOCK',
        title: `${item.name} habis`,
        body: `Stok ${item.sku} di ${item.location} sudah 0 ${item.unit}.`
      }
    });
  } else if (item.quantity <= item.minimumStock) {
    await prisma.notification.create({
      data: {
        type: 'LOW_STOCK',
        title: `${item.name} stok rendah`,
        body: `Sisa ${item.quantity} ${item.unit}; minimum yang disarankan ${item.minimumStock}.`
      }
    });
  }
}

process.on('SIGINT', async () => {
  await prisma.$disconnect();
  process.exit(0);
});

app.listen(port, () => {
  console.log(`Prisma inventory API listening on ${port}`);
});
