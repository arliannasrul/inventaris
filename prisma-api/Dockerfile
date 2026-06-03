FROM node:20-alpine

WORKDIR /usr/src/app

# Install OpenSSL required by Prisma
RUN apk add --no-cache openssl

COPY package*.json ./
RUN npm install

COPY prisma ./prisma

COPY src ./src

EXPOSE 3000

CMD ["sh", "-c", "npx prisma db push --skip-generate --accept-data-loss && npm start"]
