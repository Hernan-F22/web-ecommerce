/**
 * Client-side CSV Exporter
 * Pure Vanilla JavaScript conforming to RFC 4180 standard
 */

(function (window) {
  'use strict';

  const CSVExporter = {
    /**
     * Escape and quote a single cell value
     */
    escapeCell: function (value) {
      if (value === null || value === undefined) {
        return '""';
      }
      const str = String(value);
      // If cell contains commas, quotes, or newlines, wrap in quotes and escape internal quotes
      if (str.includes(',') || str.includes('"') || str.includes('\n') || str.includes('\r')) {
        return `"${str.replace(/"/g, '""')}"`;
      }
      return `"${str}"`;
    },

    /**
     * Convert headers and rows array into standard CSV content
     */
    buildCSV: function (headers, rows) {
      const headerLine = headers.map(this.escapeCell).join(',');
      const dataLines = rows.map(row => {
        return row.map(this.escapeCell).join(',');
      });
      return [headerLine, ...dataLines].join('\r\n');
    },

    /**
     * Trigger browser file download from CSV string
     */
    downloadFile: function (csvContent, fileName) {
      // Prepend UTF-8 BOM so Excel opens Indonesian/UTF-8 characters properly
      const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');

      link.setAttribute('href', url);
      link.setAttribute('download', fileName);
      link.style.visibility = 'hidden';

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    },

    /**
     * Export Inventory dataset to CSV
     */
    exportInventory: function (products) {
      if (!Array.isArray(products) || products.length === 0) {
        alert('Tidak ada data inventaris untuk diekspor.');
        return;
      }

      const headers = [
        'SKU Code',
        'Product Name',
        'Category',
        'Cost Price (IDR)',
        'Selling Price (IDR)',
        'Physical Stock',
        'Reserved Stock',
        'Available Stock',
        'Stock Status',
        'Created At'
      ];

      const rows = products.map(p => [
        p.sku,
        p.name,
        p.category,
        p.cost_price,
        p.selling_price,
        p.stock_physical,
        p.stock_reserved,
        p.stock_available,
        p.status,
        p.created_at
      ]);

      const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '');
      const csv = this.buildCSV(headers, rows);
      this.downloadFile(csv, `inventory_report_${dateStr}.csv`);
    },

    /**
     * Export Marketing Performance dataset to CSV
     */
    exportMarketing: function (adsData) {
      if (!Array.isArray(adsData) || adsData.length === 0) {
        alert('Tidak ada data marketing untuk diekspor.');
        return;
      }

      const headers = [
        'Platform',
        'Ad Spend (IDR)',
        'Attributed Revenue (IDR)',
        'Impressions',
        'Clicks',
        'Orders',
        'ROAS (x)',
        'CTR (%)',
        'CPC (IDR)',
        'CR (%)'
      ];

      const rows = adsData.map(ad => [
        ad.platform,
        ad.total_ad_spend,
        ad.total_revenue,
        ad.total_impressions,
        ad.total_clicks,
        ad.total_orders,
        ad.roas,
        ad.ctr + '%',
        ad.cpc,
        ad.cr + '%'
      ]);

      const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '');
      const csv = this.buildCSV(headers, rows);
      this.downloadFile(csv, `ads_performance_report_${dateStr}.csv`);
    }
  };

  window.CSVExporter = CSVExporter;
})(window);
