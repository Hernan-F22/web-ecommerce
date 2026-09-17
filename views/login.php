<?php
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim(str_ends_with($scriptDir, '/api') ? dirname($scriptDir) : $scriptDir, '/');
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Login Admin - Nexus Commerce Operations Dashboard">
  <title>Admin Login | Nexus Commerce Operations Hub</title>

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

  <!-- Core Theme -->
  <link rel="stylesheet" href="<?= $basePath ?>/css/style.css?v=<?= time() ?>">
  <script>
    window.APP_BASE_PATH = "<?= htmlspecialchars($basePath) ?>";
  </script>
</head>

<body class="login-body">

  <div class="login-wrapper">
    <!-- Brand Header -->
    <div class="login-header">
      <div class="brand-badge large">NC</div>
      <h2>Nexus Operations Hub</h2>
      <p>Marketplace & Performance Control Center</p>
    </div>

    <!-- Login Card -->
    <div class="login-card">
      <div class="login-card-header">
        <h3>Admin Authentication</h3>
        <p>Silakan masuk menggunakan akun operasional Anda</p>
      </div>

      <!-- Alert Box -->
      <div id="login-alert" class="login-alert" style="display: none;"></div>

      <form id="form-login" class="login-form">
        <div class="form-group">
          <label for="login-email">Email Admin</label>
          <div class="input-with-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <input type="email" id="login-email" name="email" placeholder="nama@domain.com" required autocomplete="email">
          </div>
        </div>

        <div class="form-group">
          <label for="login-password">Kata Sandi</label>
          <div class="input-with-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input type="password" id="login-password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" id="btn-submit-login" class="btn btn-primary btn-block">
          <span>Masuk ke Dashboard</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </form>
    </div>

    <!-- Security Footer Note -->
    <div class="login-footer-note">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
      </svg>
      <span>Enkripsi Sesi TLS 1.3 & Proteksi CSRF Aktif</span>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const apiBase = window.APP_BASE_PATH || '';
      const form = document.getElementById('form-login');
      const emailInput = document.getElementById('login-email');
      const passwordInput = document.getElementById('login-password');
      const alertBox = document.getElementById('login-alert');
      const btnSubmit = document.getElementById('btn-submit-login');

      // Submit Form
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        alertBox.style.display = 'none';
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span>Memverifikasi...</span>';

        const payload = {
          email: emailInput.value.trim(),
          password: passwordInput.value
        };

        try {
          const res = await fetch(`${apiBase}/api/auth/login`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
          });

          let json = null;
          try {
            json = await res.json();
          } catch (e) {
            // Non-JSON response (e.g. 500/502 error)
          }

          if (json && res.ok && json.status === 'success') {
            alertBox.className = 'login-alert success';
            alertBox.textContent = 'Login berhasil! Mengalihkan...';
            alertBox.style.display = 'block';

            setTimeout(() => {
              window.location.href = `${apiBase}/`;
            }, 500);
          } else {
            alertBox.className = 'login-alert error';
            alertBox.textContent = (json && json.message) ? json.message : (res.status ? `Error ${res.status}: Gagal memproses login di server.` : 'Gagal menghubungi server.');
            alertBox.style.display = 'block';
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<span>Masuk ke Dashboard</span>';
          }
        } catch (err) {
          alertBox.className = 'login-alert error';
          alertBox.textContent = 'Gagal menghubungi server. Pastikan koneksi internet Anda stabil.';
          alertBox.style.display = 'block';
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = '<span>Masuk ke Dashboard</span>';
        }
      });
    });
  </script>
</body>

</html>