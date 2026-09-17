# E-Commerce & Digital Marketing Operations Dashboard

Web aplikasi dashboard operasional korporat performa tinggi bertema **"E-Commerce & Digital Marketing Operations Dashboard"**. Dibangun dengan stack murni: **HTML5, Modern CSS3 (Slate Dark High-Contrast), Vanilla JavaScript, PHP 8.x (arsitektur MVC modular tanpa framework)**, dan skema basis data yang sepenuhnya kompatibel dengan **TiDB Cloud Serverless (MySQL)** serta siap di-deploy langsung ke **Vercel** via runtime `vercel-php`.

---

## Fitur Utama

1. **Autentikasi Admin & Proteksi Sesi**:
   - Sistem login aman menggunakan `password_hash` (bcrypt) dan session PHP terisolasi.
   - Proteksi otomatis (*Auth Guard*): Pengguna yang belum login otomatis dialihkan ke halaman `/login`.
   - Halaman login bernuansa Dark Slate korporat dengan fitur 1-klik pengisi kredensial demo.
   - Profil admin dinamis pada header dashboard beserta tombol **Logout**.
   - **Kredensial Default**: Email `admin@nexuscommerce.com`, Password `admin123`.

2. **Header Operasional Interaktif**:
   - Profil Digital Marketing & Marketplace Specialist lengkap dengan status beacon aktif.
   - Filter periode dinamis (Preset 7 Hari, 14 Hari, 30 Hari, dan custom date picker).
   - Tombol sinkronisasi data real-time dengan animasi rotasi micro-interaction.

3. **Kartu Analitik KPI Terpadu**:
   - **Total Revenue**: Total pendapatan penjualan omnichannel.
   - **Blended Ad Spend**: Total biaya iklan lintas platform (Shopee, Meta, TikTok).
   - **Blended ROAS**: Kalkulasi efisiensi belanja iklan ($\text{ROAS} = \frac{\text{Revenue}}{\text{Ad Spend}}$).
   - **Conversion Rate (CR)**: Rasio konversi pesanan dari total kunjungan/klik iklan.
   - **Net Margin**: Estimasi margin laba bersih operasional setelah dipotong ad spend.

4. **Komparasi Kinerja Iklan Multi-Platform**:
   - Analisis mendalam: **Shopee Ads vs Meta Ads Manager vs TikTok Ads**.
   - Metrik komparatif: Ad Spend, Attributed Revenue, ROAS, CTR (Click-Through Rate), CPC (Cost Per Click), dan Orders.
   - Visual progress bar kontribusi volume pendapatan.

5. **SKU & Multi-Channel Inventory Tracker**:
   - Monitoring stok fisik gudang vs stok terpesan (*reserved*) di platform marketplace.
   - Perhitungan otomatis ketersediaan stok siap kirim: $\text{Stok Tersedia} = \text{Stok Fisik} - \text{Stok Reserved}$.
   - Indikator badge status stok berkontras tinggi:
     - **Aman**: Stok tersedia > 20 unit.
     - **Low Stock**: Stok tersedia 1 – 20 unit.
     - **Out of Stock**: Stok tersedia $\le$ 0 unit.
   - Pencarian real-time (debounced) & filter kategori / status stok.
   - Modal penyesuaian stok manual (*instant sync*).
   - Modal pendaftaran SKU baru dengan validasi keunikan SKU.

6. **Client-Side CSV Data Exporter**:
   - Ekspor laporan inventaris & laporan performa iklan langsung ke format CSV standar RFC-4180 menggunakan Vanilla JavaScript murni (dengan UTF-8 BOM untuk kompatibilitas Microsoft Excel).

---

## Struktur Direktori

```text
web_ecommerce/
├── api/
│   └── index.php             # Entrypoint serverless Vercel & local PHP router
├── config/
│   └── database.php          # Koneksi PDO MySQL dengan SSL wajib TiDB Cloud Serverless
├── database/
│   └── schema.sql            # Skema tabel TiDB/MySQL + 30 hari data seed realistis
├── public/
│   ├── css/
│   │   └── style.css         # Modern Slate Dark styling, CSS Grid/Flexbox
│   └── js/
│       ├── app.js            # Main controller, state management, AJAX fetch
│       └── export.js         # Pure Vanilla JS RFC-4180 CSV Exporter
├── src/
│   ├── Controllers/
│   │   ├── DashboardController.php   # Handler GET /api/dashboard-summary
│   │   ├── InventoryController.php   # Handler GET/POST /api/inventory
│   │   └── ReportController.php      # Handler GET /api/reports
│   ├── Models/
│   │   ├── Product.php               # Query & mutasi produk, fallback mock dataset
│   │   ├── MarketingMetric.php       # Agregasi performa iklan multi-platform
│   │   └── SalesOrder.php            # Agregasi pesanan & margin penjualan
│   └── Utils/
│       ├── Router.php                # Lightweight request router
│       └── Response.php              # Standardized JSON response emitter
├── views/
│   └── dashboard.php         # Template semantik HTML5 / PHP UI
├── .env.example              # Template variabel environment TiDB & MySQL
├── .htaccess                 # Konfigurasi Apache rewrite untuk local XAMPP
├── vercel.json               # Konfigurasi deployment Vercel (vercel-php@0.7.1)
└── README.md                 # Dokumentasi arsitektur dan panduan setup
```

---

## Panduan Menjalankan Secara Lokal

### Opsi A: PHP Built-in Server (Direkomendasikan)
Jalankan perintah berikut di root proyek:
```bash
php -S 127.0.0.1:8000 api/index.php
```
Buka peramban di: `http://127.0.0.1:8000`

### Opsi B: XAMPP Apache
1. Letakkan folder proyek di `d:/xampp/htdocs/web_ecommerce`.
2. Pastikan modul Apache di XAMPP Control Panel aktif.
3. Buka peramban di: `http://localhost/web_ecommerce/`

> [!NOTE]
> Proyek dilengkapi dengan mekanisme **Graceful Degradation Mock Fallback**. Jika database belum dikonfigurasi saat pertama kali dijalankan, sistem otomatis menyajikan data simulasi realistis 30 hari terakhir sehingga UI dan fungsionalitas dapat langsung diuji secara penuh tanpa error!

---

## Konfigurasi TiDB Cloud Serverless

1. Buat database gratis di [TiDB Cloud](https://tidbcloud.com/).
2. Buat file `.env` di root proyek atau atur Environment Variables di hosting Anda:
   ```env
   DB_HOST=gateway01.us-east-1.prod.aws.tidbcloud.com
   DB_PORT=4000
   DB_NAME=ecommerce_ops
   DB_USER=your_tidb_user.root
   DB_PASSWORD=your_tidb_password
   # DB_SSL_CA=/etc/ssl/certs/ca-certificates.crt (Otomatis dideteksi pada Linux/Vercel)
   ```
3. Import skema dan data seed ke TiDB:
   ```bash
   mysql --host gateway01.us-east-1.prod.aws.tidbcloud.com --port 4000 -u your_tidb_user.root -p --ssl-mode=VERIFY_IDENTITY < database/schema.sql
   ```

---

## Panduan Deployment ke Vercel

Proyek ini telah dikonfigurasi siap pakai untuk Vercel melalui `vercel.json` dengan runtime `vercel-php@0.7.1`.

1. Install Vercel CLI (jika belum):
   ```bash
   npm i -g vercel
   ```
2. Hubungkan dan deploy ke Vercel:
   ```bash
   vercel
   ```
3. Tambahkan Environment Variables di dashboard Vercel (**Project Settings > Environment Variables**):
   - `DB_HOST`
   - `DB_PORT`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASSWORD`
4. Deploy ke tahap produksi:
   ```bash
   vercel --prod
   ```

---

## REST API Reference

Semua response API dikembalikan dalam format JSON standar:
```json
{
  "status": "success",
  "message": "Deskripsi pesan",
  "data": { ... },
  "timestamp": "2026-09-17T16:40:00+07:00"
}
```

### 1. GET `/api/dashboard-summary`
- **Query Params**: `start_date` (YYYY-MM-DD), `end_date` (YYYY-MM-DD)
- **Fungsi**: Mengambil ringkasan metrik KPI terpadu, komparasi performa iklan per platform, pesanan terbaru, dan status kesehatan inventaris.

### 2. GET `/api/inventory`
- **Query Params**: `search` (string), `category` (string), `status` ('Aman' | 'Low Stock' | 'Out of Stock')
- **Fungsi**: Mengambil daftar inventaris produk dengan kalkulasi stok fisik vs reserved.

### 3. POST `/api/inventory`
- **Body Payload (Penyesuaian Stok)**:
  ```json
  {
    "action": "adjust_stock",
    "sku": "SKU-ELC-001",
    "stock_physical": 160,
    "stock_reserved": 20
  }
  ```
- **Body Payload (Tambah SKU Baru)**:
  ```json
  {
    "action": "create",
    "sku": "SKU-ELC-004",
    "name": "Mechanical Gaming Keyboard RGB",
    "category": "Electronics",
    "cost_price": 250000,
    "selling_price": 499000,
    "stock_physical": 50,
    "stock_reserved": 5
  }
  ```

### 4. GET `/api/reports`
- **Query Params**: `type` ('inventory' | 'marketing' | 'orders' | 'all'), `start_date`, `end_date`
- **Fungsi**: Mengambil dataset agregasi untuk kebutuhan pelaporan internal dan export.
