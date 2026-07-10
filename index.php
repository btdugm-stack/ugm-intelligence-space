<?php
// Load functions (simpler than bootstrap)
require_once __DIR__ . '/functions.php';

// Initialize session if function exists
if (function_exists('init_session')) {
    @init_session();
}

// Apply security headers if function exists
if (function_exists('apply_security_headers')) {
    @apply_security_headers();
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Load data
$dashboards = load_dashboards();
$publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));
$domains = array_values(array_unique(array_map(fn($d) => $d['domain'] ?? '', $publicDashboards)));
$updatedToday = count(array_filter($publicDashboards, fn($d) => ($d['last_updated'] ?? '') === date('Y-m-d')));
$needAttention = count(array_filter($publicDashboards, fn($d) => in_array(($d['status'] ?? ''), ['Delayed','Need Review','Maintenance'])));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UGM Intelligence Space</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="container nav">
    <a href="index.php" class="brand">
      <div class="logo">UGM</div>
      <div>
        <strong>UGM Intelligence Space</strong>
        <span>Dashboard Gateway & Decision Intelligence Portal</span>
      </div>
    </a>
    <div class="nav-actions">
      <a class="btn btn-secondary" href="#catalog">Explore Dashboard</a>
      <a class="btn btn-primary" href="login.php">Admin Login</a>
    </div>
  </div>
</header>

<main>
  <section class="hero" style="background: linear-gradient(135deg, rgba(7,55,99,0.05) 0%, rgba(20,184,212,0.08) 50%, rgba(214,167,52,0.05) 100%), url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1200 600%22><defs><pattern id=%22dots%22 x=%2240%22 y=%2240%22 width=%2280%22 height=%2280%22 patternUnits=%22userSpaceOnUse%22><circle cx=%2210%22 cy=%2210%22 r=%222%22 fill=%22%230d4f8b%22 opacity=%220.04%22/></pattern><linearGradient id=%22grad%22 x1=%220%25%22 y1=%220%25%22 x2=%22100%25%22 y2=%22100%25%22><stop offset=%220%25%22 style=%22stop-color:%230d4f8b;stop-opacity:0.03%22/><stop offset=%22100%25%22 style=%22stop-color:%2314b8d4;stop-opacity:0.05%22/></linearGradient></defs><rect width=%221200%22 height=%22600%22 fill=%22url(%23grad)%22/><rect width=%221200%22 height=%22600%22 fill=%22url(%23dots)%22/></svg>'); background-size: cover, 80px 80px; background-attachment: fixed, scroll; padding: 54px 0 26px; position: relative;">
    <div class="container hero-grid">
      <div class="hero-card">
        <span class="eyebrow">✦ Intelligence Space for Smart Campus</span>
        <h1>Satu ruang untuk membaca insight strategis UGM.</h1>
        <p class="lead">Akses katalog dashboard analytics lintas domain universitas dengan pencarian cepat, konteks data, status update, dan navigasi berbasis kebutuhan pengguna.</p>
        <div class="search-panel">
          <input id="searchInput" type="search" placeholder="Cari dashboard, indikator, unit, domain, atau topik strategis...">
          <button class="btn btn-primary" onclick="filterCards()">Cari</button>
        </div>
        <div class="quick-intents">
          <button class="chip" onclick="setSearch('strategis')">Lihat Kinerja UGM</button>
          <button class="chip" onclick="setSearch('layanan')">Pantau Layanan</button>
          <button class="chip" onclick="setSearch('akademik')">Cek Akademik</button>
          <button class="chip" onclick="setSearch('risiko')">Pantau Risiko</button>
        </div>
      </div>

      <aside class="pulse-card">
        <h3>University Pulse</h3>
        <div class="pulse-list">
          <div class="pulse-item"><span>Public Dashboards</span><strong><?= count($publicDashboards) ?></strong></div>
          <div class="pulse-item"><span>Updated Today</span><strong><?= $updatedToday ?></strong></div>
          <div class="pulse-item"><span>Need Attention</span><strong><?= $needAttention ?></strong></div>
          <div class="pulse-item"><span>Active Domains</span><strong><?= count($domains) ?></strong></div>
        </div>
      </aside>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-title">
        <div>
          <h2>Featured Intelligence</h2>
          <p>Dashboard prioritas untuk pemantauan cepat dan pengambilan keputusan.</p>
        </div>
      </div>
      <div class="grid">
        <?php foreach (array_slice($publicDashboards, 0, 3) as $d): ?>
          <article class="card dashboard-card"
            data-search="<?= e(strtolower(($d['name'] ?? '').' '.($d['description'] ?? '').' '.($d['domain'] ?? '').' '.($d['owner'] ?? '').' '.($d['tags'] ?? ''))) ?>"
            data-domain="<?= e($d['domain'] ?? '') ?>"
            data-status="<?= e($d['status'] ?? '') ?>">
            <div class="card-head">
              <div class="icon"><?= dashboard_icon($d['domain'] ?? '') ?></div>
              <span class="badge <?= status_class($d['status'] ?? '') ?>"><?= e($d['status'] ?? '') ?></span>
            </div>
            <h3><?= e($d['name'] ?? '') ?></h3>
            <p><?= e($d['description'] ?? '') ?></p>
            <div class="meta">
              <div><span>Owner</span><strong><?= e($d['owner'] ?? '') ?></strong></div>
              <div><span>Update</span><strong><?= e($d['update_frequency'] ?? '') ?></strong></div>
              <div><span>Audience</span><strong><?= e($d['audience'] ?? '') ?></strong></div>
            </div>
            <div class="card-actions">
              <a class="btn btn-primary" target="_blank" href="<?= e($d['url'] ?? '#') ?>">Open Dashboard</a>
              <a class="btn btn-ghost" href="detail.php?id=<?= e($d['id'] ?? '') ?>">View Detail</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" id="catalog">
    <div class="container">
      <div class="section-title">
        <div>
          <h2>Intelligence Catalog</h2>
          <p>Temukan dashboard berdasarkan domain, status data, dan kebutuhan analisis.</p>
        </div>
      </div>

      <div class="filters">
        <input id="catalogSearch" type="search" placeholder="Search catalog..." oninput="filterCards()">
        <select id="domainFilter" onchange="filterCards()">
          <option value="">Semua Domain</option>
          <?php foreach ($domains as $domain): ?>
            <option value="<?= e($domain) ?>"><?= e($domain) ?></option>
          <?php endforeach; ?>
        </select>
        <select id="statusFilter" onchange="filterCards()">
          <option value="">Semua Status</option>
          <option>Updated</option>
          <option>Scheduled Update</option>
          <option>Need Review</option>
          <option>Delayed</option>
          <option>Maintenance</option>
        </select>
        <select id="accessFilter" disabled>
          <option>Public View</option>
        </select>
      </div>

      <div class="grid" id="dashboardGrid">
        <?php foreach ($publicDashboards as $d): ?>
          <article class="card dashboard-card"
            data-search="<?= e(strtolower(($d['name'] ?? '').' '.($d['description'] ?? '').' '.($d['domain'] ?? '').' '.($d['owner'] ?? '').' '.($d['tags'] ?? ''))) ?>"
            data-domain="<?= e($d['domain'] ?? '') ?>"
            data-status="<?= e($d['status'] ?? '') ?>">
            <div class="card-head">
              <div class="icon"><?= dashboard_icon($d['domain'] ?? '') ?></div>
              <span class="badge <?= status_class($d['status'] ?? '') ?>"><?= e($d['status'] ?? '') ?></span>
            </div>
            <h3><?= e($d['name'] ?? '') ?></h3>
            <p><?= e($d['description'] ?? '') ?></p>
            <div class="meta">
              <div><span>Domain</span><strong><?= e($d['domain'] ?? '') ?></strong></div>
              <div><span>Owner</span><strong><?= e($d['owner'] ?? '') ?></strong></div>
              <div><span>Last Updated</span><strong><?= e($d['last_updated'] ?? '') ?></strong></div>
            </div>
            <div class="card-actions">
              <a class="btn btn-primary" target="_blank" href="<?= e($d['url'] ?? '#') ?>">Open Dashboard</a>
              <a class="btn btn-ghost" href="detail.php?id=<?= e($d['id'] ?? '') ?>">View Detail</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<footer class="footer">
  <div class="container">UGM Intelligence Space — Proof of Concept. Dibuat sebagai katalog dashboard analytics publik dan admin-managed content.</div>
</footer>

<script>
function setSearch(value){
  document.getElementById('searchInput').value = value;
  document.getElementById('catalogSearch').value = value;
  filterCards();
  document.getElementById('catalog').scrollIntoView({behavior:'smooth'});
}
function filterCards(){
  const q = ((document.getElementById('catalogSearch').value || document.getElementById('searchInput').value || '')).toLowerCase();
  const domain = document.getElementById('domainFilter')?.value || '';
  const status = document.getElementById('statusFilter')?.value || '';
  document.querySelectorAll('.dashboard-card').forEach(card => {
    const matchQ = !q || card.dataset.search.includes(q);
    const matchDomain = !domain || card.dataset.domain === domain;
    const matchStatus = !status || card.dataset.status === status;
    card.style.display = (matchQ && matchDomain && matchStatus) ? 'flex' : 'none';
  });
}
</script>
</body>
</html>
