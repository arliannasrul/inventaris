@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Simulasi Sistem</p>
        <h1>Simulasi Pembelian E-commerce</h1>
    </div>
    <a class="button secondary" href="{{ route('orders.index') }}">
        ⬅️ Kembali
    </a>
</header>

<div class="grid two">
    <!-- Form Pemesanan -->
    <section class="panel">
        <div class="panel-head">
            <h2>Detail Pelanggan & Alamat</h2>
        </div>
        
        <form id="simulationForm" method="POST" action="{{ route('orders.simulate') }}">
            @csrf
            <div class="stack" style="gap: 16px;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 6px; color: var(--muted);">Nama Penerima</label>
                    <input type="text" name="customer_name" required placeholder="Contoh: Budi Santoso" value="{{ old('customer_name') }}">
                </div>
                
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 6px; color: var(--muted);">No. Telepon / WhatsApp</label>
                    <input type="text" name="customer_phone" required placeholder="Contoh: 08123456789" value="{{ old('customer_phone') }}">
                    <small style="color: var(--muted); display: block; margin-top: 4px;">Gunakan nomor aktif untuk testing CRM WhatsApp.</small>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 6px; color: var(--muted);">Alamat Lengkap</label>
                    <textarea name="customer_address" required placeholder="Nama jalan, nomor rumah, RT/RW, kecamatan...">{{ old('customer_address') }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; color: var(--muted);">Kota Tujuan</label>
                        <select name="destination_city" id="destinationCity" required>
                            @foreach ($cities as $city)
                                <option value="{{ $city['id'] }}" {{ old('destination_city') == $city['id'] ? 'selected' : '' }}>
                                    {{ $city['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; color: var(--muted);">Ekspedisi</label>
                        <select name="courier" id="courier" required>
                            <option value="jne" {{ old('courier') == 'jne' ? 'selected' : '' }}>JNE</option>
                            <option value="jnt" {{ old('courier') == 'jnt' ? 'selected' : '' }}>J&T</option>
                            <option value="sicepat" {{ old('courier') == 'sicepat' ? 'selected' : '' }}>SiCepat</option>
                            <option value="anteraja" {{ old('courier') == 'anteraja' ? 'selected' : '' }}>Anteraja</option>
                        </select>
                    </div>
                </div>

                <!-- Input Hidden untuk Layanan Ongkir Terpilih -->
                <input type="hidden" name="shipping_service" id="shippingServiceInput" required>
                <input type="hidden" name="shipping_cost" id="shippingCostInput" required>

                <hr style="border: 0; border-top: 1px solid var(--line); margin: 10px 0;">

                <div class="panel-head" style="margin-bottom: 10px;">
                    <h2>Keranjang Belanja</h2>
                    <button type="button" class="button" id="addItemRow" style="padding: 4px 10px; min-height: auto; font-size: 0.8rem;">
                        ➕ Tambah Barang
                    </button>
                </div>

                <div id="itemsContainer" class="stack" style="gap: 10px;">
                    <!-- Item Row template -->
                    <div class="item-row" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; align-items: center;">
                        <div>
                            <select name="items[0][id]" class="item-select" required>
                                <option value="" data-price="0">-- Pilih Barang --</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}" data-price="{{ $item->unit_price }}">
                                        {{ $item->name }} (Stok: {{ $item->quantity }}) - Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="number" name="items[0][quantity]" class="item-qty" min="1" value="1" required placeholder="Qty">
                        </div>
                        <div>
                            <button type="button" class="button danger remove-item-row" style="padding: 10px; min-height: auto;">✕</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="button primary" id="submitOrderBtn" disabled style="width: 100%; margin-top: 20px;">
                    🛒 Checkout & Buat Pesanan
                </button>
            </div>
        </form>
    </section>

    <!-- Ringkasan Checkout & Cek Tarif -->
    <div>
        <section class="panel">
            <div class="panel-head">
                <h2>Ringkasan Pembayaran</h2>
            </div>
            
            <div class="stack" style="gap: 12px; font-size: 0.95rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--muted);">Subtotal Produk</span>
                    <strong id="summarySubtotal">Rp 0</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--muted);">Estimasi Berat</span>
                    <strong id="summaryWeight">0 gram</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--muted);">Ongkos Kirim</span>
                    <strong id="summaryShipping" style="color: var(--accent);">Pilih layanan pengiriman</strong>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--line); margin: 8px 0;">

                <div style="display: flex; justify-content: space-between; font-size: 1.15rem;">
                    <strong>Total Bayar</strong>
                    <strong id="summaryTotal" style="color: var(--accent);">Rp 0</strong>
                </div>
            </div>
        </section>

        <!-- Layanan Pengiriman Kiriminaja -->
        <section class="panel">
            <div class="panel-head">
                <h2>Cek Tarif Pengiriman (API Kiriminaja)</h2>
            </div>
            
            <p style="font-size: 0.88rem; color: var(--muted); margin-bottom: 16px;">
                Hitung tarif ongkos kirim real-time dari Sleman (Yogyakarta) ke kota tujuan menggunakan KiriminAja sandbox.
            </p>

            <button type="button" class="button" id="btnCheckRates" style="width: 100%; margin-bottom: 20px;">
                ⚡ Hitung Ongkos Kirim
            </button>

            <div id="ratesLoader" style="display: none; text-align: center; padding: 20px;">
                <div style="display: inline-block; width: 24px; height: 24px; border: 3px solid var(--line); border-radius: 50%; border-top-color: var(--accent); animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 8px; font-size: 0.9rem; color: var(--muted);">Menghubungi API KiriminAja...</p>
            </div>

            <div id="ratesContainer" class="stack" style="gap: 10px;">
                <p class="empty" style="padding: 10px !important;">Klik tombol di atas untuk memuat pilihan paket pengiriman.</p>
            </div>
        </section>
    </div>
</div>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('itemsContainer');
    const addItemRowBtn = document.getElementById('addItemRow');
    const btnCheckRates = document.getElementById('btnCheckRates');
    const ratesContainer = document.getElementById('ratesContainer');
    const ratesLoader = document.getElementById('ratesLoader');
    const submitOrderBtn = document.getElementById('submitOrderBtn');
    
    let rowCount = 1;

    // Hitung ringkasan harga produk
    function updatePricingSummary() {
        let subtotal = 0;
        let totalWeight = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.item-select');
            const qtyInput = row.querySelector('.item-qty');
            
            const price = parseFloat(select.options[select.selectedIndex]?.dataset.price || 0);
            const qty = parseInt(qtyInput.value || 0);
            
            subtotal += price * qty;
            totalWeight += qty * 250; // Estimasi 250g per item
        });
        
        document.getElementById('summarySubtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('summaryWeight').innerText = totalWeight.toLocaleString('id-ID') + ' gram';
        
        // Reset shipping selection jika isi barang diubah
        resetShipping();
    }

    function resetShipping() {
        document.getElementById('shippingServiceInput').value = '';
        document.getElementById('shippingCostInput').value = '';
        document.getElementById('summaryShipping').innerText = 'Hitung & pilih layanan';
        document.getElementById('summaryShipping').style.color = 'var(--muted)';
        
        const subtotalText = document.getElementById('summarySubtotal').innerText;
        document.getElementById('summaryTotal').innerText = subtotalText;
        submitOrderBtn.disabled = true;
    }

    // Dynamic row addition
    addItemRowBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'item-row';
        newRow.style = 'display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; align-items: center; margin-top: 10px;';
        
        // Generate options
        const originalSelect = document.querySelector('.item-select');
        const optionsHtml = originalSelect.innerHTML;
        
        newRow.innerHTML = `
            <div>
                <select name="items[${rowCount}][id]" class="item-select" required>
                    ${optionsHtml}
                </select>
            </div>
            <div>
                <input type="number" name="items[${rowCount}][quantity]" class="item-qty" min="1" value="1" required placeholder="Qty">
            </div>
            <div>
                <button type="button" class="button danger remove-item-row" style="padding: 10px; min-height: auto;">✕</button>
            </div>
        `;
        
        itemsContainer.appendChild(newRow);
        rowCount++;
        
        // Bind events
        newRow.querySelector('.item-select').addEventListener('change', updatePricingSummary);
        newRow.querySelector('.item-qty').addEventListener('input', updatePricingSummary);
        newRow.querySelector('.remove-item-row').addEventListener('click', function() {
            newRow.remove();
            updatePricingSummary();
        });
        
        updatePricingSummary();
    });

    // Initial binding
    document.querySelector('.item-select').addEventListener('change', updatePricingSummary);
    document.querySelector('.item-qty').addEventListener('input', updatePricingSummary);
    document.querySelector('.remove-item-row').addEventListener('click', function(e) {
        // Jangan hapus baris pertama jika tinggal satu
        if(document.querySelectorAll('.item-row').length > 1) {
            e.target.closest('.item-row').remove();
            updatePricingSummary();
        }
    });

    // Trigger initial summary calculation
    updatePricingSummary();

    // Cek Tarif dengan API Kiriminaja via AJAX
    btnCheckRates.addEventListener('click', function() {
        const destinationCityId = document.getElementById('destinationCity').value;
        const courier = document.getElementById('courier').value;
        
        // Hitung total berat
        let totalWeight = 0;
        let valid = true;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.item-select');
            const qty = parseInt(row.querySelector('.item-qty').value || 0);
            if (!select.value) {
                valid = false;
            }
            totalWeight += qty * 250;
        });

        if (!valid || totalWeight === 0) {
            alert('Silakan pilih produk dan kuantitas terlebih dahulu.');
            return;
        }

        // Tampilkan loader
        ratesContainer.style.display = 'none';
        ratesLoader.style.display = 'block';

        fetch("{{ route('orders.api.rates') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                destination_city_id: destinationCityId,
                weight_grams: totalWeight,
                courier: courier
            })
        })
        .then(response => response.json())
        .then(data => {
            ratesLoader.style.display = 'none';
            ratesContainer.style.display = 'flex';
            ratesContainer.innerHTML = '';
            
            if (data.length === 0) {
                ratesContainer.innerHTML = '<p class="empty" style="padding:10px!important;">Layanan tidak tersedia untuk rute dan ekspedisi terpilih.</p>';
                return;
            }

            data.forEach((rate, index) => {
                const card = document.createElement('label');
                card.style = 'display: flex; justify-content: space-between; align-items: center; padding: 14px; background: rgba(255,255,255,0.02); border: 1px solid var(--line); border-radius: 8px; cursor: pointer; margin-bottom: 8px;';
                card.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="selected_rate" value="${rate.service}" data-cost="${rate.cost}" style="width:auto; cursor:pointer;">
                        <div>
                            <strong style="text-transform: uppercase;">${rate.courier} - ${rate.service}</strong>
                            <small style="display:block; color:var(--muted);">${rate.description} (${rate.etd})</small>
                        </div>
                    </div>
                    <strong style="color:var(--accent);">Rp ${rate.cost.toLocaleString('id-ID')}</strong>
                `;
                
                // Bind selection click
                card.querySelector('input[type="radio"]').addEventListener('change', function() {
                    selectRate(rate.service, rate.cost);
                });

                ratesContainer.appendChild(card);
            });
        })
        .catch(err => {
            ratesLoader.style.display = 'none';
            ratesContainer.style.display = 'flex';
            ratesContainer.innerHTML = '<p class="error" style="font-size:0.85rem;">Gagal memuat ongkir dari API Kiriminaja. Silakan coba lagi.</p>';
            console.error(err);
        });
    });

    function selectRate(service, cost) {
        document.getElementById('shippingServiceInput').value = service;
        document.getElementById('shippingCostInput').value = cost;
        
        document.getElementById('summaryShipping').innerText = 'Rp ' + cost.toLocaleString('id-ID');
        document.getElementById('summaryShipping').style.color = 'var(--success)';
        
        // Update total
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.item-select');
            const qty = parseInt(row.querySelector('.item-qty').value || 0);
            const price = parseFloat(select.options[select.selectedIndex]?.dataset.price || 0);
            subtotal += price * qty;
        });

        const grandTotal = subtotal + cost;
        document.getElementById('summaryTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        
        // Aktifkan submit
        submitOrderBtn.disabled = false;
    }
});
</script>
@endsection
