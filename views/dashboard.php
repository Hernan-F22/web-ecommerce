<?php
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim(str_ends_with($scriptDir, '/api') ? dirname($scriptDir) : $scriptDir, '/');

$adminUser = $_SESSION['admin_user'] ?? [
  'name'  => 'M. Hernan F.',
  'email' => 'admin@nexuscommerce.com',
  'role'  => 'Super Admin'
];
$adminName = $adminUser['name'] ?? 'Admin';
$adminRole = $adminUser['role'] ?? 'Operations Lead';

$parts = explode(' ', trim($adminName));
$adminInitials = '';
foreach ($parts as $p) {
  if (!empty($p)) {
    $adminInitials .= strtoupper($p[0]);
  }
  if (strlen($adminInitials) >= 2) break;
}
if (empty($adminInitials)) $adminInitials = 'AD';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="E-Commerce & Digital Marketing Operations Dashboard - High-Performance Analytics and Inventory Management">
  <title>Nexus Commerce | E-Commerce & Digital Marketing Operations Dashboard</title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Favicon / Tab Icons -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= $basePath ?>/img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= $basePath ?>/img/favicon-16x16.png">
  <link rel="icon" type="image/svg+xml" href="<?= $basePath ?>/img/favicon.svg">
  <link rel="shortcut icon" href="<?= $basePath ?>/img/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= $basePath ?>/img/apple-touch-icon.png">

  <!-- Core Dark Slate Theme -->
  <link rel="stylesheet" href="<?= $basePath ?>/css/style.css?v=<?= time() ?>">
  <script>
    window.APP_BASE_PATH = "<?= htmlspecialchars($basePath) ?>";
  </script>
</head>

<body>

  <div class="dashboard-container">

    <!-- ====================================================================
         1. OPERATIONAL HEADER
         ==================================================================== -->
    <header class="ops-header">
      <div class="header-brand">
        <div class="brand-badge">NC</div>
        <div class="brand-info">
          <h1>Nexus Operations Hub</h1>
          <p>Marketplace Performance & Inventory Control Center</p>
        </div>
      </div>

      <!-- Specialist Profile Card & Actions -->
      <div class="header-profile-section">
        <div class="specialist-profile">
          <div class="specialist-avatar"><?= htmlspecialchars($adminInitials) ?></div>
          <div class="specialist-meta">
            <span class="specialist-name">
              <span class="status-beacon" title="Live System Active"></span>
              <?= htmlspecialchars($adminName) ?>
            </span>
            <span class="specialist-role"><?= htmlspecialchars($adminRole) ?></span>
          </div>
        </div>
        <button type="button" class="btn-change-password" id="btn-open-change-password" title="Ganti Password Super Admin">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          Ganti Password
        </button>
        <a href="<?= $basePath ?>/logout" class="btn-logout" title="Keluar dari sesi admin">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          Logout
        </a>
      </div>

      <!-- Header Controls: Date Presets, Datepickers & Sync -->
      <div class="header-actions">
        <div class="date-filter-group">
          <div class="date-preset-pills">
            <button type="button" class="pill-btn" data-days="7">7 Hari</button>
            <button type="button" class="pill-btn" data-days="14">14 Hari</button>
            <button type="button" class="pill-btn active" data-days="30">30 Hari</button>
          </div>
          <div class="date-inputs">
            <input type="date" id="input-start-date" title="Tanggal Mulai">
            <span>s/d</span>
            <input type="date" id="input-end-date" title="Tanggal Akhir">
          </div>
        </div>

        <button type="button" class="btn btn-secondary btn-sync" id="btn-sync" title="Sinkronisasi Data Real-Time">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2" />
          </svg>
          Sync
        </button>
      </div>
    </header>

    <!-- ====================================================================
         2. INTEGRATED KPI METRIC CARDS
         ==================================================================== -->
    <section class="kpi-grid" aria-label="Ringkasan Metrik Utama">

      <!-- KPI 1: Total Revenue -->
      <div class="kpi-card" style="--card-accent: #38bdf8;">
        <div class="kpi-header">
          <span class="kpi-title">Total Revenue</span>
          <div class="kpi-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
        </div>
        <div class="kpi-value" id="kpi-revenue">Rp 0</div>
        <div class="kpi-footer">
          <span>Gross Omnichannel Sales</span>
          <span class="kpi-badge positive">Live</span>
        </div>
      </div>

      <!-- KPI 2: Blended Ad Spend -->
      <div class="kpi-card" style="--card-accent: #f59e0b;">
        <div class="kpi-header">
          <span class="kpi-title">Blended Ad Spend</span>
          <div class="kpi-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
          </div>
        </div>
        <div class="kpi-value" id="kpi-ad-spend">Rp 0</div>
        <div class="kpi-footer">
          <span>Shopee + Meta + TikTok</span>
          <span class="kpi-badge neutral">Aggregated</span>
        </div>
      </div>

      <!-- KPI 3: Blended ROAS -->
      <div class="kpi-card" style="--card-accent: #10b981;">
        <div class="kpi-header">
          <span class="kpi-title">Blended ROAS</span>
          <div class="kpi-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
              <polyline points="17 6 23 6 23 12"></polyline>
            </svg>
          </div>
        </div>
        <div class="kpi-value" id="kpi-roas">0.00x</div>
        <div class="kpi-footer">
          <span>Revenue / Total Ad Spend</span>
          <span class="kpi-badge positive">Target ≥ 4.0x</span>
        </div>
      </div>

      <!-- KPI 4: Conversion Rate (CR) -->
      <div class="kpi-card" style="--card-accent: #6366f1;">
        <div class="kpi-header">
          <span class="kpi-title">Conversion Rate (CR)</span>
          <div class="kpi-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
        </div>
        <div class="kpi-value" id="kpi-cr">0.00%</div>
        <div class="kpi-footer">
          <span>Orders / Total Clicks</span>
          <span class="kpi-badge neutral">Omni-Store</span>
        </div>
      </div>

      <!-- KPI 5: Net Margin -->
      <div class="kpi-card" style="--card-accent: #ec4899;">
        <div class="kpi-header">
          <span class="kpi-title">Net Margin</span>
          <div class="kpi-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
          </div>
        </div>
        <div class="kpi-value" id="kpi-net-margin">0.00%</div>
        <div class="kpi-footer">
          <span>EBITDA Margin Post-Ads</span>
          <span class="kpi-badge positive">Healthy</span>
        </div>
      </div>

    </section>

    <!-- ====================================================================
         3. ADS PERFORMANCE COMPARISON
         ==================================================================== -->
    <section class="section-card">
      <div class="section-header">
        <div class="section-title-wrap">
          <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="20" x2="18" y2="10"></line>
              <line x1="12" y1="20" x2="12" y2="4"></line>
              <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            Ads Performance Multi-Platform Comparison
          </h2>
          <p>Komparasi performa Shopee Ads vs Meta Ads Manager vs TikTok Ads (Biaya Iklan, ROAS, CPC, CTR)</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
          <button type="button" class="btn btn-primary" id="btn-open-record-metric" style="padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Catat Metrik Iklan</span>
          </button>
          <button type="button" class="btn btn-secondary" id="btn-open-reset-metrics" style="padding: 0.45rem 0.75rem; font-size: 0.8rem; color: #f87171; border-color: rgba(239, 68, 68, 0.35); display: inline-flex; align-items: center; gap: 0.35rem;" title="Kosongkan angka demo agar mulai dari Rp 0">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            <span>Reset Data Iklan</span>
          </button>
          <button type="button" class="btn btn-secondary" id="btn-export-ads">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export Ads CSV
          </button>
        </div>
      </div>

      <div class="ads-grid" id="ads-comparison-grid">
        <!-- Injected asynchronously via Vanilla JS -->
      </div>
    </section>

    <!-- ====================================================================
         4. SKU & INVENTORY TRACKER (PHYSICAL VS RESERVED)
         ==================================================================== -->
    <section class="section-card">
      <div class="section-header">
        <div class="section-title-wrap">
          <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
              <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            SKU & Multi-Channel Inventory Tracker
          </h2>
          <p>Sinkronisasi stok fisik gudang vs stok terpesan (reserved) di Shopee & TikTok Shop</p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <span class="badge badge-safe" id="inventory-count-badge">0 SKU</span>
          <button type="button" class="btn btn-secondary" id="btn-export-inventory">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export CSV
          </button>
          <button type="button" class="btn btn-primary" id="btn-open-add-sku">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Tambah SKU
          </button>
        </div>
      </div>

      <!-- Filter & Search Toolbar -->
      <div class="table-toolbar">
        <div class="table-filters">
          <div class="search-input-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="inventory-search" placeholder="Cari Kode SKU atau Nama Produk...">
          </div>

          <select class="select-filter" id="category-filter">
            <option value="All">Semua Kategori</option>
            <option value="Electronics">Electronics</option>
            <option value="Fashion">Fashion</option>
            <option value="Beauty & Care">Beauty & Care</option>
            <option value="Home & Living">Home & Living</option>
          </select>

          <select class="select-filter" id="status-filter">
            <option value="All">Semua Status</option>
            <option value="Aman">Aman (In Stock)</option>
            <option value="Low Stock">Low Stock</option>
            <option value="Out of Stock">Out of Stock</option>
          </select>
        </div>
      </div>

      <!-- Responsive Inventory Table -->
      <div class="table-responsive">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Kode SKU</th>
              <th>Nama Produk & Kategori</th>
              <th>Stok Fisik</th>
              <th>Stok Reserved</th>
              <th>Tersedia (Ready)</th>
              <th>Harga Jual</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="inventory-table-body">
            <!-- Injected asynchronously via Vanilla JS -->
          </tbody>
        </table>
      </div>
    </section>

  </div>

  <!-- ====================================================================
       MODAL 1: ADJUST STOCK
       ==================================================================== -->
  <div class="modal-overlay" id="modal-adjust-stock">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Penyesuaian Stok Manual</h3>
        <button type="button" class="modal-close" aria-label="Tutup">&times;</button>
      </div>
      <form id="form-adjust-stock">
        <div class="modal-body">
          <p id="adjust-sku-display" style="font-weight: 700; color: var(--accent-primary); font-size: 0.9rem;"></p>
          <input type="hidden" id="adjust-sku-input" name="sku">

          <div class="form-row">
            <div class="form-group">
              <label for="adjust-physical-input">Stok Fisik Gudang</label>
              <input type="number" id="adjust-physical-input" name="stock_physical" min="0" required>
            </div>
            <div class="form-group">
              <label for="adjust-reserved-input">Stok Terpesan (Reserved)</label>
              <input type="number" id="adjust-reserved-input" name="stock_reserved" min="0" required>
            </div>
          </div>
          <small style="color: var(--text-muted); font-size: 0.75rem;">
            * Stok Tersedia (Ready) akan otomatis dihitung dari: <code>Fisik - Reserved</code>.
          </small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 2: ADD NEW SKU
       ==================================================================== -->
  <div class="modal-overlay" id="modal-add-sku">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Registrasi SKU Baru</h3>
        <button type="button" class="modal-close" aria-label="Tutup">&times;</button>
      </div>
      <form id="form-add-sku">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label for="new-sku">Kode SKU (Unik) *</label>
              <input type="text" id="new-sku" placeholder="Contoh: SKU-ELC-004" required>
            </div>
            <div class="form-group">
              <label for="new-category">Kategori *</label>
              <select id="new-category" required>
                <option value="Electronics">Electronics</option>
                <option value="Fashion">Fashion</option>
                <option value="Beauty & Care">Beauty & Care</option>
                <option value="Home & Living">Home & Living</option>
                <option value="FMCG">FMCG</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="new-name">Nama Produk *</label>
            <input type="text" id="new-name" placeholder="Nama lengkap produk e-commerce" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="new-cost">Harga Modal / HPP (IDR)</label>
              <input type="number" id="new-cost" min="0" step="1000" value="0" required>
            </div>
            <div class="form-group">
              <label for="new-price">Harga Jual (IDR) *</label>
              <input type="number" id="new-price" min="0" step="1000" value="0" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="new-physical">Stok Fisik Awal</label>
              <input type="number" id="new-physical" min="0" value="0" required>
            </div>
            <div class="form-group">
              <label for="new-reserved">Stok Terpesan (Reserved)</label>
              <input type="number" id="new-reserved" min="0" value="0" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Batal</button>
          <button type="submit" class="btn btn-primary">Tambah SKU</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 3: CHANGE ADMIN PASSWORD
       ==================================================================== -->
  <div class="modal-overlay" id="modal-change-password">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Ganti Password Super Admin</h3>
        <button type="button" class="modal-close" aria-label="Tutup">&times;</button>
      </div>
      <form id="form-change-password">
        <div class="modal-body">
          <div id="change-pwd-alert" class="login-alert" style="display: none; margin-bottom: 0.5rem;"></div>

          <div class="form-group">
            <label for="current-password">Password Saat Ini *</label>
            <input type="password" id="current-password" name="current_password" placeholder="Masukkan password lama" required autocomplete="current-password">
          </div>

          <div class="form-group">
            <label for="new-password">Password Baru *</label>
            <input type="password" id="new-password" name="new_password" placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password">
          </div>

          <div class="form-group">
            <label for="confirm-password">Konfirmasi Password Baru *</label>
            <input type="password" id="confirm-password" name="confirm_password" placeholder="Ulangi password baru" required minlength="6" autocomplete="new-password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Batal</button>
          <button type="submit" id="btn-submit-change-pwd" class="btn btn-primary">Simpan Password Baru</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 4: EDIT PRODUCT DETAILS
       ==================================================================== -->
  <div class="modal-overlay" id="modal-edit-product">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Update Data Produk</h3>
        <button type="button" class="modal-close" aria-label="Tutup">&times;</button>
      </div>
      <form id="form-edit-product">
        <div class="modal-body">
          <input type="hidden" id="edit-sku" name="sku">

          <div class="form-row">
            <div class="form-group">
              <label>Kode SKU</label>
              <input type="text" id="edit-sku-display" disabled style="opacity: 0.7; font-family: monospace; font-weight: 700;">
            </div>
            <div class="form-group">
              <label for="edit-category">Kategori *</label>
              <select id="edit-category" name="category" required>
                <option value="Electronics">Electronics</option>
                <option value="Fashion">Fashion</option>
                <option value="Beauty & Care">Beauty & Care</option>
                <option value="Home & Living">Home & Living</option>
                <option value="FMCG">FMCG</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="edit-name">Nama Produk *</label>
            <input type="text" id="edit-name" name="name" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="edit-cost">Harga Modal / HPP (IDR)</label>
              <input type="number" id="edit-cost" name="cost_price" min="0" step="1000" required>
            </div>
            <div class="form-group">
              <label for="edit-price">Harga Jual (IDR) *</label>
              <input type="number" id="edit-price" name="selling_price" min="0" step="1000" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="edit-physical">Stok Fisik Gudang</label>
              <input type="number" id="edit-physical" name="stock_physical" min="0" required>
            </div>
            <div class="form-group">
              <label for="edit-reserved">Stok Terpesan (Reserved)</label>
              <input type="number" id="edit-reserved" name="stock_reserved" min="0" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Pembaruan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 5: DELETE PRODUCT CONFIRMATION
       ==================================================================== -->
  <div class="modal-overlay" id="modal-delete-product">
    <div class="modal-content" style="max-width: 440px;">
      <div class="modal-header">
        <h3 style="color: var(--status-danger); display: flex; align-items: center; gap: 0.5rem;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          Konfirmasi Hapus SKU
        </h3>
        <button type="button" class="modal-close" aria-label="Tutup">&times;</button>
      </div>
      <form id="form-delete-product">
        <div class="modal-body">
          <input type="hidden" id="delete-sku-input" name="sku">
          <p style="font-size: 0.9rem; line-height: 1.6; color: var(--text-secondary);">
            Apakah Anda yakin ingin menghapus produk <strong id="delete-sku-display" style="color: var(--text-primary);"></strong> dari inventaris?
          </p>
          <div style="background: var(--status-danger-bg); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); padding: 0.75rem; margin-top: 0.75rem; font-size: 0.8rem; color: #fca5a5;">
            * Data yang dihapus tidak dapat dipulihkan kembali dari sistem.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Batal</button>
          <button type="submit" id="btn-submit-delete" class="btn" style="background: var(--status-danger); color: #fff;">
            Hapus Permanen
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 6: RECORD DAILY MARKETING METRICS
       ==================================================================== -->
  <div class="modal-overlay" id="modal-record-metric">
    <div class="modal-content">
      <div class="modal-header">
        <h3 style="display: flex; align-items: center; gap: 0.5rem;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="20" x2="18" y2="10"></line>
            <line x1="12" y1="20" x2="12" y2="4"></line>
            <line x1="6" y1="20" x2="6" y2="14"></line>
          </svg>
          Catat Metrik Iklan & Penjualan Harian
        </h3>
        <button type="button" class="modal-close" aria-label="Tutup">&times;</button>
      </div>
      <form id="form-record-metric">
        <div class="modal-body">
          <p style="font-size: 0.825rem; color: var(--text-secondary); margin-bottom: 1rem; line-height: 1.5;">
            Input biaya iklan (*spend*) dan omset penjualan (*attributed revenue*) riil hari ini. Sistem otomatis menghitung ulang <strong>ROAS, Conversion Rate, dan Net Margin</strong> secara realtime.
          </p>

          <div class="form-row">
            <div class="form-group">
              <label for="metric-date">Tanggal Laporan *</label>
              <input type="date" id="metric-date" name="date" required>
            </div>
            <div class="form-group">
              <label for="metric-platform">Platform Iklan *</label>
              <select id="metric-platform" name="platform" required>
                <option value="Shopee Ads">Shopee Ads</option>
                <option value="Meta Ads">Meta Ads (FB / Instagram)</option>
                <option value="TikTok Ads">TikTok Ads</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="metric-ad-spend">Biaya Iklan / Ad Spend (IDR) *</label>
              <input type="number" id="metric-ad-spend" name="ad_spend" min="0" step="1000" placeholder="Misal: 1500000" required>
            </div>
            <div class="form-group">
              <label for="metric-revenue">Pendapatan / Revenue (IDR) *</label>
              <input type="number" id="metric-revenue" name="revenue" min="0" step="1000" placeholder="Misal: 7500000" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="metric-orders">Total Pesanan (Orders)</label>
              <input type="number" id="metric-orders" name="orders" min="0" placeholder="Misal: 35" value="0">
            </div>
            <div class="form-group">
              <label for="metric-clicks">Jumlah Klik (Opsional)</label>
              <input type="number" id="metric-clicks" name="clicks" min="0" placeholder="Misal: 950" value="0">
            </div>
          </div>
          <small style="color: var(--text-muted); font-size: 0.75rem;">
            * Jika klik/tayangan dikosongkan, sistem cerdas akan mengestimasinya berdasarkan rasio CTR standar platform.
          </small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Batal</button>
          <button type="submit" id="btn-submit-metric" class="btn btn-primary">Simpan & Hitung ROAS</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 7: RESET MARKETING & SALES DATA CONFIRMATION
       ==================================================================== -->
  <div class="modal-overlay" id="modal-reset-metrics">
    <div class="modal-content" style="max-width: 450px;">
      <div class="modal-header">
        <h3 style="color: var(--status-danger); display: flex; align-items: center; gap: 0.5rem;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          Reset Data Iklan & Penjualan
        </h3>
        <button type="button" class="modal-close" aria-label="Tutup">&times;</button>
      </div>
      <form id="form-reset-metrics">
        <div class="modal-body">
          <p style="font-size: 0.9rem; line-height: 1.6; color: var(--text-secondary);">
            Apakah Anda yakin ingin <strong>mengosongkan seluruh angka riwayat iklan dan penjualan demo</strong>?
          </p>
          <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); padding: 0.75rem; margin-top: 0.75rem; font-size: 0.8rem; color: #fca5a5; line-height: 1.5;">
            * Seluruh angka pengeluaran iklan, omset, dan pesanan demo akan di-reset menjadi <strong>Rp 0</strong>, sehingga dashboard Anda bersih dan siap diisi data asli toko Anda.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Batal</button>
          <button type="submit" id="btn-submit-reset-metrics" class="btn" style="background: var(--status-danger); color: #fff;">
            Ya, Kosongkan Menjadi Rp 0
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div class="toast-container" id="toast-container"></div>

  <!-- Pure Vanilla Scripts -->
  <script src="<?= $basePath ?>/js/export.js?v=<?= time() ?>"></script>
  <script src="<?= $basePath ?>/js/app.js?v=<?= time() ?>"></script>
</body>

</html>