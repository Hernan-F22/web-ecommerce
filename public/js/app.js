/**
 * Main Application Script (Vanilla JS)
 * E-Commerce & Digital Marketing Operations Dashboard
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // ---------------------------------------------------------------------------
  // Global State
  // ---------------------------------------------------------------------------
  const apiBase = window.APP_BASE_PATH || '';

  const state = {
    startDate: '',
    endDate: '',
    cachedProducts: [],
    cachedAds: [],
    isSyncing: false
  };

  // ---------------------------------------------------------------------------
  // DOM Elements
  // ---------------------------------------------------------------------------
  const elements = {
    // KPI elements
    valRevenue: document.getElementById('kpi-revenue'),
    valAdSpend: document.getElementById('kpi-ad-spend'),
    valRoas: document.getElementById('kpi-roas'),
    valCr: document.getElementById('kpi-cr'),
    valNetMargin: document.getElementById('kpi-net-margin'),

    // Ads comparison container
    adsComparisonGrid: document.getElementById('ads-comparison-grid'),

    // Inventory elements
    inventoryTableBody: document.getElementById('inventory-table-body'),
    inventorySearch: document.getElementById('inventory-search'),
    categoryFilter: document.getElementById('category-filter'),
    statusFilter: document.getElementById('status-filter'),
    inventoryCountBadge: document.getElementById('inventory-count-badge'),

    // Date & Controls
    btnSync: document.getElementById('btn-sync'),
    inputStartDate: document.getElementById('input-start-date'),
    inputEndDate: document.getElementById('input-end-date'),
    presetPills: document.querySelectorAll('.pill-btn'),

    // Modals
    modalAdjustStock: document.getElementById('modal-adjust-stock'),
    modalAddSku: document.getElementById('modal-add-sku'),
    modalChangePassword: document.getElementById('modal-change-password'),
    modalEditProduct: document.getElementById('modal-edit-product'),
    modalDeleteProduct: document.getElementById('modal-delete-product'),
    modalRecordMetric: document.getElementById('modal-record-metric'),
    formAdjustStock: document.getElementById('form-adjust-stock'),
    formAddSku: document.getElementById('form-add-sku'),
    formChangePassword: document.getElementById('form-change-password'),
    formEditProduct: document.getElementById('form-edit-product'),
    formDeleteProduct: document.getElementById('form-delete-product'),
    formRecordMetric: document.getElementById('form-record-metric'),
    btnOpenChangePassword: document.getElementById('btn-open-change-password'),
    btnOpenRecordMetric: document.getElementById('btn-open-record-metric'),
    changePwdAlert: document.getElementById('change-pwd-alert'),

    // Export Buttons
    btnExportInventory: document.getElementById('btn-export-inventory'),
    btnExportAds: document.getElementById('btn-export-ads'),

    // Toast Container
    toastContainer: document.getElementById('toast-container')
  };

  // ---------------------------------------------------------------------------
  // Formatting Utilities
  // ---------------------------------------------------------------------------
  const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0
    }).format(val || 0);
  };

  const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0);
  };

  const showToast = (message, type = 'success') => {
    if (!elements.toastContainer) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <span>${type === 'success' ? '✓' : '⚠'}</span>
      <span>${message}</span>
    `;
    elements.toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  };

  // ---------------------------------------------------------------------------
  // Date Helpers & Presets
  // ---------------------------------------------------------------------------
  const setDateRangePreset = (days) => {
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - (days - 1));

    const formatDateStr = (d) => d.toISOString().split('T')[0];

    state.startDate = formatDateStr(start);
    state.endDate = formatDateStr(end);

    if (elements.inputStartDate) elements.inputStartDate.value = state.startDate;
    if (elements.inputEndDate) elements.inputEndDate.value = state.endDate;

    refreshAllData();
  };

  // ---------------------------------------------------------------------------
  // Data Fetching: Dashboard Summary
  // ---------------------------------------------------------------------------
  const fetchDashboardSummary = async () => {
    try {
      let url = `${apiBase}/api/dashboard-summary`;
      const params = new URLSearchParams();
      if (state.startDate) params.append('start_date', state.startDate);
      if (state.endDate) params.append('end_date', state.endDate);
      if ([...params].length > 0) url += `?${params.toString()}`;

      const res = await fetch(url);
      const json = await res.json();

      if (json.status === 'success') {
        renderKPIs(json.data.kpi);
        renderAdsComparison(json.data.ads_comparison);
        state.cachedAds = json.data.ads_comparison;
      } else {
        showToast(json.message || 'Gagal mengambil ringkasan dashboard', 'error');
      }
    } catch (err) {
      console.error('Error fetching dashboard summary:', err);
      showToast('Koneksi server terganggu', 'error');
    }
  };

  const renderKPIs = (kpi) => {
    if (!kpi) return;
    if (elements.valRevenue) elements.valRevenue.textContent = formatCurrency(kpi.total_revenue);
    if (elements.valAdSpend) elements.valAdSpend.textContent = formatCurrency(kpi.blended_ad_spend);
    if (elements.valRoas) elements.valRoas.textContent = `${kpi.blended_roas}x`;
    if (elements.valCr) elements.valCr.textContent = `${kpi.conversion_rate}%`;
    if (elements.valNetMargin) elements.valNetMargin.textContent = `${kpi.net_margin_pct}%`;
  };

  const renderAdsComparison = (adsList) => {
    if (!elements.adsComparisonGrid) return;

    if (!Array.isArray(adsList) || adsList.length === 0) {
      elements.adsComparisonGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 2rem;">
          Tidak ada data komparasi iklan pada periode ini.
        </div>
      `;
      return;
    }

    // Find max revenue for proportional progress bars
    const maxRev = Math.max(...adsList.map(a => a.total_revenue || 1));

    elements.adsComparisonGrid.innerHTML = adsList.map(ad => {
      let badgeClass = 'shopee';
      let progressColor = 'var(--platform-shopee)';

      if (ad.platform.includes('Meta')) {
        badgeClass = 'meta';
        progressColor = 'var(--platform-meta)';
      } else if (ad.platform.includes('TikTok')) {
        badgeClass = 'tiktok';
        progressColor = 'var(--platform-tiktok)';
      }

      const revPercent = Math.min(100, Math.round((ad.total_revenue / maxRev) * 100));

      return `
        <div class="ad-platform-card">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="platform-pill">
              <span class="platform-indicator ${badgeClass}"></span>
              ${ad.platform}
            </span>
            <span class="badge ${ad.roas >= 4.0 ? 'badge-safe' : 'badge-warning'}">
              ROAS ${ad.roas}x
            </span>
          </div>

          <div class="platform-metric-row">
            <div class="sub-metric">
              <span class="sub-metric-label">Ad Spend</span>
              <span class="sub-metric-val">${formatCurrency(ad.total_ad_spend)}</span>
            </div>
            <div class="sub-metric">
              <span class="sub-metric-label">Attributed Revenue</span>
              <span class="sub-metric-val roas-highlight">${formatCurrency(ad.total_revenue)}</span>
            </div>
          </div>

          <div>
            <div style="display: flex; justify-content: space-between; font-size: 0.725rem; color: var(--text-muted); margin-bottom: 0.2rem;">
              <span>Volume Kontribusi Pendapatan</span>
              <span>${revPercent}%</span>
            </div>
            <div class="progress-bar-container">
              <div class="progress-fill" style="width: ${revPercent}%; background: ${progressColor};"></div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; border-top: 1px solid var(--border-subtle); padding-top: 0.75rem;">
            <div class="sub-metric">
              <span class="sub-metric-label">CTR</span>
              <span style="font-size: 0.85rem; font-weight: 700;">${ad.ctr}%</span>
            </div>
            <div class="sub-metric">
              <span class="sub-metric-label">CPC</span>
              <span style="font-size: 0.85rem; font-weight: 700;">${formatCurrency(ad.cpc)}</span>
            </div>
            <div class="sub-metric">
              <span class="sub-metric-label">Orders</span>
              <span style="font-size: 0.85rem; font-weight: 700;">${formatNumber(ad.total_orders)}</span>
            </div>
          </div>
        </div>
      `;
    }).join('');
  };

  // ---------------------------------------------------------------------------
  // Data Fetching: Inventory List
  // ---------------------------------------------------------------------------
  const fetchInventory = async () => {
    try {
      const search = elements.inventorySearch ? elements.inventorySearch.value.trim() : '';
      const category = elements.categoryFilter ? elements.categoryFilter.value : '';
      const status = elements.statusFilter ? elements.statusFilter.value : '';

      const params = new URLSearchParams();
      if (search) params.append('search', search);
      if (category && category !== 'All') params.append('category', category);
      if (status && status !== 'All') params.append('status', status);

      const url = `${apiBase}/api/inventory?${params.toString()}`;
      const res = await fetch(url);
      const json = await res.json();

      if (json.status === 'success') {
        state.cachedProducts = json.data.products;
        renderInventoryTable(state.cachedProducts);
        if (elements.inventoryCountBadge) {
          elements.inventoryCountBadge.textContent = `${json.data.count} SKU`;
        }
      }
    } catch (err) {
      console.error('Error fetching inventory:', err);
    }
  };

  const renderInventoryTable = (products) => {
    if (!elements.inventoryTableBody) return;

    if (!Array.isArray(products) || products.length === 0) {
      elements.inventoryTableBody.innerHTML = `
        <tr>
          <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
            Tidak ada SKU yang cocok dengan kriteria pencarian.
          </td>
        </tr>
      `;
      return;
    }

    elements.inventoryTableBody.innerHTML = products.map(item => {
      let badgeClass = 'badge-safe';
      if (item.status === 'Low Stock') badgeClass = 'badge-warning';
      if (item.status === 'Out of Stock') badgeClass = 'badge-danger';

      return `
        <tr>
          <td><span class="sku-code">${item.sku}</span></td>
          <td>
            <div class="product-cell">
              <span class="product-name">${item.name}</span>
              <span class="product-cat">${item.category}</span>
            </div>
          </td>
          <td><span style="font-weight: 700;">${formatNumber(item.stock_physical)}</span></td>
          <td><span style="color: var(--text-secondary);">${formatNumber(item.stock_reserved)}</span></td>
          <td>
            <span style="font-weight: 800; font-size: 0.95rem; color: ${item.stock_available <= 0 ? 'var(--status-danger)' : 'var(--text-primary)'}">
              ${formatNumber(item.stock_available)}
            </span>
          </td>
          <td>${formatCurrency(item.selling_price)}</td>
          <td>
            <span class="badge ${badgeClass}">
              <span class="badge-dot"></span>
              ${item.status}
            </span>
          </td>
          <td>
            <div class="table-actions-group">
              <button class="btn-action-icon btn-action-edit btn-edit-product" 
                      data-sku="${item.sku}" 
                      data-name="${encodeURIComponent(item.name)}" 
                      data-category="${encodeURIComponent(item.category)}"
                      data-cost="${item.cost_price}"
                      data-price="${item.selling_price}"
                      data-physical="${item.stock_physical}" 
                      data-reserved="${item.stock_reserved}"
                      title="Update Data Produk">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit
              </button>

              <button class="btn-action-icon btn-action-stock btn-adjust-stock" 
                      data-sku="${item.sku}" 
                      data-name="${encodeURIComponent(item.name)}" 
                      data-physical="${item.stock_physical}" 
                      data-reserved="${item.stock_reserved}"
                      title="Sesuaikan Stok">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                  <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                Stok
              </button>

              <button class="btn-action-icon btn-action-delete btn-delete-product" 
                      data-sku="${item.sku}" 
                      data-name="${encodeURIComponent(item.name)}" 
                      title="Hapus Produk">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  <line x1="10" y1="11" x2="10" y2="17"></line>
                  <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                Hapus
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  };

  // ---------------------------------------------------------------------------
  // Modal Operations
  // ---------------------------------------------------------------------------
  const openEditProductModal = (item) => {
    const modal = document.getElementById('modal-edit-product');
    if (!modal) return;

    const elEditSku = document.getElementById('edit-sku');
    const elEditSkuDisp = document.getElementById('edit-sku-display');
    const elEditName = document.getElementById('edit-name');
    const elEditCat = document.getElementById('edit-category');
    const elEditCost = document.getElementById('edit-cost');
    const elEditPrice = document.getElementById('edit-price');
    const elEditPhysical = document.getElementById('edit-physical');
    const elEditReserved = document.getElementById('edit-reserved');

    if (elEditSku) elEditSku.value = item.sku || '';
    if (elEditSkuDisp) elEditSkuDisp.value = item.sku || '';
    if (elEditName) elEditName.value = item.name || '';
    if (elEditCat) elEditCat.value = item.category || 'Electronics';
    if (elEditCost) elEditCost.value = item.cost_price || 0;
    if (elEditPrice) elEditPrice.value = item.selling_price || 0;
    if (elEditPhysical) elEditPhysical.value = item.physical || 0;
    if (elEditReserved) elEditReserved.value = item.reserved || 0;

    modal.classList.add('active');
  };

  const openDeleteProductModal = (sku, name) => {
    const modal = document.getElementById('modal-delete-product');
    if (!modal) return;

    const elDeleteInput = document.getElementById('delete-sku-input');
    const elDeleteDisplay = document.getElementById('delete-sku-display');

    if (elDeleteInput) elDeleteInput.value = sku;
    if (elDeleteDisplay) elDeleteDisplay.textContent = `${sku} (${name})`;

    modal.classList.add('active');
  };

  const openAdjustStockModal = (sku, name, physical, reserved) => {
    const modal = document.getElementById('modal-adjust-stock');
    if (!modal) return;

    const elAdjustDisplay = document.getElementById('adjust-sku-display');
    const elAdjustInput = document.getElementById('adjust-sku-input');
    const elAdjustPhys = document.getElementById('adjust-physical-input');
    const elAdjustRes = document.getElementById('adjust-reserved-input');

    if (elAdjustDisplay) elAdjustDisplay.textContent = `${sku} - ${name}`;
    if (elAdjustInput) elAdjustInput.value = sku;
    if (elAdjustPhys) elAdjustPhys.value = physical;
    if (elAdjustRes) elAdjustRes.value = reserved;

    modal.classList.add('active');
  };

  const closeModals = () => {
    document.querySelectorAll('.modal-overlay').forEach(modal => {
      modal.classList.remove('active');
    });
  };

  // ---------------------------------------------------------------------------
  // Refresh Orchestrator
  // ---------------------------------------------------------------------------
  const refreshAllData = async () => {
    if (state.isSyncing) return;
    state.isSyncing = true;

    if (elements.btnSync) {
      elements.btnSync.classList.add('spinning');
    }

    try {
      await Promise.all([fetchDashboardSummary(), fetchInventory()]);
      showToast('Data berhasil diperbarui');
    } catch (e) {
      console.error(e);
    } finally {
      state.isSyncing = false;
      if (elements.btnSync) {
        elements.btnSync.classList.remove('spinning');
      }
    }
  };

  // ---------------------------------------------------------------------------
  // Event Bindings
  // ---------------------------------------------------------------------------
  const setupEventListeners = () => {
    // 1. Sync button
    if (elements.btnSync) {
      elements.btnSync.addEventListener('click', refreshAllData);
    }

    // 2. Date presets
    elements.presetPills.forEach(pill => {
      pill.addEventListener('click', (e) => {
        elements.presetPills.forEach(p => p.classList.remove('active'));
        e.target.classList.add('active');
        const days = parseInt(e.target.getAttribute('data-days'), 10);
        setDateRangePreset(days);
      });
    });

    // 3. Date pickers
    if (elements.inputStartDate && elements.inputEndDate) {
      const handleDateChange = () => {
        state.startDate = elements.inputStartDate.value;
        state.endDate = elements.inputEndDate.value;
        elements.presetPills.forEach(p => p.classList.remove('active'));
        refreshAllData();
      };
      elements.inputStartDate.addEventListener('change', handleDateChange);
      elements.inputEndDate.addEventListener('change', handleDateChange);
    }

    // 4. Inventory Search & Filters (with debounce)
    let debounceTimer = null;
    if (elements.inventorySearch) {
      elements.inventorySearch.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchInventory, 250);
      });
    }

    if (elements.categoryFilter) {
      elements.categoryFilter.addEventListener('change', fetchInventory);
    }

    if (elements.statusFilter) {
      elements.statusFilter.addEventListener('change', fetchInventory);
    }

    // 4b. Inventory Table Row Actions Delegation (Edit, Stock Adjust, Delete)
    if (elements.inventoryTableBody) {
      elements.inventoryTableBody.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.btn-edit-product');
        if (editBtn) {
          const itemData = {
            sku: editBtn.getAttribute('data-sku'),
            name: decodeURIComponent(editBtn.getAttribute('data-name') || ''),
            category: decodeURIComponent(editBtn.getAttribute('data-category') || ''),
            cost_price: editBtn.getAttribute('data-cost'),
            selling_price: editBtn.getAttribute('data-price'),
            physical: editBtn.getAttribute('data-physical'),
            reserved: editBtn.getAttribute('data-reserved')
          };
          openEditProductModal(itemData);
          return;
        }

        const stockBtn = e.target.closest('.btn-adjust-stock');
        if (stockBtn) {
          const sku = stockBtn.getAttribute('data-sku');
          const name = decodeURIComponent(stockBtn.getAttribute('data-name') || '');
          const physical = stockBtn.getAttribute('data-physical');
          const reserved = stockBtn.getAttribute('data-reserved');
          openAdjustStockModal(sku, name, physical, reserved);
          return;
        }

        const deleteBtn = e.target.closest('.btn-delete-product');
        if (deleteBtn) {
          const sku = deleteBtn.getAttribute('data-sku');
          const name = decodeURIComponent(deleteBtn.getAttribute('data-name') || '');
          openDeleteProductModal(sku, name);
          return;
        }
      });
    }

    // 5. Stock Adjustment Form Submission
    if (elements.formAdjustStock) {
      elements.formAdjustStock.addEventListener('submit', async (e) => {
        e.preventDefault();
        const sku = document.getElementById('adjust-sku-input').value;
        const physical = parseInt(document.getElementById('adjust-physical-input').value, 10);
        const reserved = parseInt(document.getElementById('adjust-reserved-input').value, 10);

        try {
          const res = await fetch(`${apiBase}/api/inventory`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'adjust_stock',
              sku: sku,
              stock_physical: physical,
              stock_reserved: reserved
            })
          });

          const json = await res.json();
          if (json.status === 'success') {
            showToast(`Stok ${sku} berhasil diperbarui`);
            closeModals();
            fetchInventory();
          } else {
            showToast(json.message || 'Gagal mengubah stok', 'error');
          }
        } catch (err) {
          showToast('Terjadi kesalahan jaringan', 'error');
        }
      });
    }

    // 6. Add SKU Form Submission
    const btnOpenAddSku = document.getElementById('btn-open-add-sku');
    if (btnOpenAddSku) {
      btnOpenAddSku.addEventListener('click', () => {
        elements.modalAddSku.classList.add('active');
      });
    }

    if (elements.formAddSku) {
      elements.formAddSku.addEventListener('submit', async (e) => {
        e.preventDefault();
        const sku = document.getElementById('new-sku').value.trim();
        const name = document.getElementById('new-name').value.trim();
        const category = document.getElementById('new-category').value;
        const costPrice = parseFloat(document.getElementById('new-cost').value);
        const sellingPrice = parseFloat(document.getElementById('new-price').value);
        const physical = parseInt(document.getElementById('new-physical').value, 10);
        const reserved = parseInt(document.getElementById('new-reserved').value, 10);

        try {
          const res = await fetch(`${apiBase}/api/inventory`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'create',
              sku,
              name,
              category,
              cost_price: costPrice,
              selling_price: sellingPrice,
              stock_physical: physical,
              stock_reserved: reserved
            })
          });

          const json = await res.json();
          if (json.status === 'success') {
            showToast(`SKU ${sku} berhasil ditambahkan!`);
            elements.formAddSku.reset();
            closeModals();
            fetchInventory();
          } else {
            const errDetail = json.errors ? Object.values(json.errors).join(', ') : json.message;
            showToast(errDetail || 'Gagal menambahkan SKU', 'error');
          }
        } catch (err) {
          showToast('Terjadi kesalahan jaringan', 'error');
        }
      });
    }

    // 7. Change Password Handlers
    if (elements.btnOpenChangePassword) {
      elements.btnOpenChangePassword.addEventListener('click', () => {
        if (elements.changePwdAlert) elements.changePwdAlert.style.display = 'none';
        if (elements.formChangePassword) elements.formChangePassword.reset();
        if (elements.modalChangePassword) elements.modalChangePassword.classList.add('active');
      });
    }

    if (elements.formChangePassword) {
      elements.formChangePassword.addEventListener('submit', async (e) => {
        e.preventDefault();
        const curPwd = document.getElementById('current-password').value;
        const newPwd = document.getElementById('new-password').value;
        const confPwd = document.getElementById('confirm-password').value;

        if (newPwd !== confPwd) {
          if (elements.changePwdAlert) {
            elements.changePwdAlert.className = 'login-alert error';
            elements.changePwdAlert.textContent = 'Konfirmasi password baru tidak cocok';
            elements.changePwdAlert.style.display = 'block';
          }
          return;
        }

        const btnSubmit = document.getElementById('btn-submit-change-pwd');
        if (btnSubmit) {
          btnSubmit.disabled = true;
          btnSubmit.textContent = 'Menyimpan...';
        }

        try {
          const res = await fetch(`${apiBase}/api/auth/change-password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              current_password: curPwd,
              new_password: newPwd,
              confirm_password: confPwd
            })
          });

          const json = await res.json();
          if (res.ok && json.status === 'success') {
            showToast('Password Super Admin berhasil diperbarui!');
            closeModals();
            elements.formChangePassword.reset();
          } else {
            if (elements.changePwdAlert) {
              elements.changePwdAlert.className = 'login-alert error';
              elements.changePwdAlert.textContent = json.message || 'Gagal mengubah password';
              elements.changePwdAlert.style.display = 'block';
            }
          }
        } catch (err) {
          showToast('Terjadi kesalahan jaringan', 'error');
        } finally {
          if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Simpan Password Baru';
          }
        }
      });
    }

    // 8. Edit Product Form Submission
    if (elements.formEditProduct) {
      elements.formEditProduct.addEventListener('submit', async (e) => {
        e.preventDefault();
        const sku = document.getElementById('edit-sku').value.trim();
        const name = document.getElementById('edit-name').value.trim();
        const category = document.getElementById('edit-category').value;
        const costPrice = parseFloat(document.getElementById('edit-cost').value);
        const sellingPrice = parseFloat(document.getElementById('edit-price').value);
        const physical = parseInt(document.getElementById('edit-physical').value, 10);
        const reserved = parseInt(document.getElementById('edit-reserved').value, 10);

        try {
          const res = await fetch(`${apiBase}/api/inventory`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'update',
              sku,
              name,
              category,
              cost_price: costPrice,
              selling_price: sellingPrice,
              stock_physical: physical,
              stock_reserved: reserved
            })
          });

          const json = await res.json();
          if (res.ok && json.status === 'success') {
            showToast(`Data produk ${sku} berhasil diperbarui!`);
            closeModals();
            fetchInventory();
            fetchDashboardSummary();
          } else {
            showToast(json.message || 'Gagal memperbarui produk', 'error');
          }
        } catch (err) {
          showToast('Terjadi kesalahan jaringan', 'error');
        }
      });
    }

    // 9. Delete Product Form Submission
    if (elements.formDeleteProduct) {
      elements.formDeleteProduct.addEventListener('submit', async (e) => {
        e.preventDefault();
        const sku = document.getElementById('delete-sku-input').value;
        const btnDelete = document.getElementById('btn-submit-delete');
        if (btnDelete) btnDelete.disabled = true;

        try {
          const res = await fetch(`${apiBase}/api/inventory`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'delete',
              sku
            })
          });

          const json = await res.json();
          if (res.ok && json.status === 'success') {
            showToast(`Produk ${sku} berhasil dihapus!`);
            closeModals();
            fetchInventory();
            fetchDashboardSummary();
          } else {
            showToast(json.message || 'Gagal menghapus produk', 'error');
          }
        } catch (err) {
          showToast('Terjadi kesalahan jaringan', 'error');
        } finally {
          if (btnDelete) btnDelete.disabled = false;
        }
      });
    }

    // 10. Record Daily Marketing Metric
    const btnOpenMetric = elements.btnOpenRecordMetric || document.getElementById('btn-open-record-metric');
    if (btnOpenMetric) {
      btnOpenMetric.addEventListener('click', () => {
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('metric-date');
        if (dateInput && !dateInput.value) {
          dateInput.value = today;
        }
        const modal = elements.modalRecordMetric || document.getElementById('modal-record-metric');
        if (modal) modal.classList.add('active');
      });
    }

    const formMetric = elements.formRecordMetric || document.getElementById('form-record-metric');
    if (formMetric) {
      formMetric.addEventListener('submit', async (e) => {
        e.preventDefault();
        const date = document.getElementById('metric-date').value;
        const platform = document.getElementById('metric-platform').value;
        const adSpend = parseFloat(document.getElementById('metric-ad-spend').value || '0');
        const revenue = parseFloat(document.getElementById('metric-revenue').value || '0');
        const orders = parseInt(document.getElementById('metric-orders').value || '0', 10);
        const clicks = parseInt(document.getElementById('metric-clicks').value || '0', 10);

        const btnSubmit = document.getElementById('btn-submit-metric');
        if (btnSubmit) {
          btnSubmit.disabled = true;
          btnSubmit.textContent = 'Menyimpan...';
        }

        try {
          const res = await fetch(`${apiBase}/api/marketing`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              platform,
              date,
              ad_spend: adSpend,
              revenue,
              orders,
              clicks
            })
          });

          const json = await res.json();
          if (res.ok && json.status === 'success') {
            const roas = adSpend > 0 ? (revenue / adSpend).toFixed(2) : '0';
            showToast(`Metrik ${platform} berhasil disimpan! (ROAS: ${roas}x)`);
            closeModals();
            formMetric.reset();
            // Refresh summary and inventory
            refreshAllData();
          } else {
            const errDetail = json.errors ? Object.values(json.errors).join(', ') : json.message;
            showToast(errDetail || 'Gagal mencatat metrik', 'error');
          }
        } catch (err) {
          showToast('Terjadi kesalahan jaringan', 'error');
        } finally {
          if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Simpan & Hitung ROAS';
          }
        }
      });
    }

    // 11. Reset Marketing Metrics & Sales Orders
    const btnOpenReset = document.getElementById('btn-open-reset-metrics');
    if (btnOpenReset) {
      btnOpenReset.addEventListener('click', () => {
        const modal = document.getElementById('modal-reset-metrics');
        if (modal) modal.classList.add('active');
      });
    }

    const formReset = document.getElementById('form-reset-metrics');
    if (formReset) {
      formReset.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btnSubmit = document.getElementById('btn-submit-reset-metrics');
        if (btnSubmit) {
          btnSubmit.disabled = true;
          btnSubmit.textContent = 'Mereset...';
        }

        try {
          const res = await fetch(`${apiBase}/api/marketing/reset`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
          });

          const json = await res.json();
          if (res.ok && json.status === 'success') {
            showToast('Data angka demo berhasil dikosongkan (Rp 0)!');
            closeModals();
            refreshAllData();
          } else {
            showToast(json.message || 'Gagal mereset data', 'error');
          }
        } catch (err) {
          showToast('Terjadi kesalahan jaringan', 'error');
        } finally {
          if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Ya, Kosongkan Menjadi Rp 0';
          }
        }
      });
    }

    // 12. Modal close handlers
    document.querySelectorAll('.modal-close, .btn-modal-cancel').forEach(btn => {
      btn.addEventListener('click', closeModals);
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModals();
      });
    });

    // 8. CSV Export Triggers
    if (elements.btnExportInventory) {
      elements.btnExportInventory.addEventListener('click', () => {
        if (window.CSVExporter) {
          window.CSVExporter.exportInventory(state.cachedProducts);
        }
      });
    }

    if (elements.btnExportAds) {
      elements.btnExportAds.addEventListener('click', () => {
        if (window.CSVExporter) {
          window.CSVExporter.exportMarketing(state.cachedAds);
        }
      });
    }
  };

  // ---------------------------------------------------------------------------
  // Initialize App
  // ---------------------------------------------------------------------------
  setupEventListeners();
  setDateRangePreset(30); // Default to last 30 days
});
