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
    </div>
  </div>
</header>

<main>
  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-card">
        <span class="eyebrow">✦ Intelligence Space for Smart Campus</span>
        <h1>Unified Dashboard Intelligence Hub for UGM</h1>
        <p class="lead">Access strategic analytics dashboards across all university domains. Discover data insights, monitor KPIs, and support data-driven decision making through our comprehensive analytics gateway.</p>
        <div class="search-panel">
          <input id="searchInput" type="search" placeholder="Cari dashboard, indikator, unit, domain, atau topik strategis...">
          <button class="btn btn-primary" onclick="filterCards()">Cari</button>
        </div>
        <div class="quick-intents">
          <button class="chip" onclick="setSearch('strategis')">Strategic KPIs</button>
          <button class="chip" onclick="setSearch('layanan')">Service Status</button>
          <button class="chip" onclick="setSearch('akademik')">Academic</button>
          <button class="chip" onclick="setSearch('risiko')">Finance</button>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-title">
        <div>
          <h2>Featured Intelligence</h2>
          <p>Priority dashboards for rapid monitoring and strategic decision-making.</p>
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
          <p>Discover dashboards across domains, data status, and analytical needs.</p>
        </div>
      </div>

      <div class="filters">
        <input id="catalogSearch" type="search" placeholder="Search catalog..." oninput="filterCards()">
        <select id="domainFilter" onchange="filterCards()">
          <option value="">All Domains</option>
          <?php foreach ($domains as $domain): ?>
            <option value="<?= e($domain) ?>"><?= e($domain) ?></option>
          <?php endforeach; ?>
        </select>
        <select id="statusFilter" onchange="filterCards()">
          <option value="">All Status</option>
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
  <div class="container">UGM Intelligence Space — Proof of Concept. Strategic dashboard analytics catalog and admin-managed content.</div>
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
