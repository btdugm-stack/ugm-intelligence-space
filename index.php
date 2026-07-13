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
$statuses = array_values(array_unique(array_map(fn($d) => $d['status'] ?? '', $publicDashboards)));
$updatedToday = count(array_filter($publicDashboards, fn($d) => ($d['last_updated'] ?? '') === date('Y-m-d')));
$needAttention = count(array_filter($publicDashboards, fn($d) => in_array(($d['status'] ?? ''), ['Delayed','Need Review','Maintenance'])));
$activeDomains = count($domains);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UGM Intelligence Space - Dashboard Catalog</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    /* Enhanced statistics grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 32px;
    }
    
    .stat-card {
      background: linear-gradient(135deg, var(--primary-lighter) 0%, rgba(32, 191, 212, 0.08) 100%);
      border: 1px solid var(--border-light);
      border-radius: 16px;
      padding: 20px;
      text-align: center;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
      border-color: var(--primary);
    }
    
    .stat-card.accent-1 {
      background: linear-gradient(135deg, rgba(45, 184, 159, 0.1) 0%, rgba(45, 184, 159, 0.05) 100%);
      border-color: rgba(45, 184, 159, 0.2);
    }
    
    .stat-card.accent-1:hover {
      border-color: var(--status-success);
    }
    
    .stat-card.accent-2 {
      background: linear-gradient(135deg, rgba(201, 169, 97, 0.1) 0%, rgba(201, 169, 97, 0.05) 100%);
      border-color: rgba(201, 169, 97, 0.2);
    }
    
    .stat-card.accent-2:hover {
      border-color: var(--accent);
    }
    
    .stat-card.accent-3 {
      background: linear-gradient(135deg, rgba(217, 124, 124, 0.1) 0%, rgba(217, 124, 124, 0.05) 100%);
      border-color: rgba(217, 124, 124, 0.2);
    }
    
    .stat-card.accent-3:hover {
      border-color: var(--status-danger);
    }
    
    .stat-value {
      font-size: 36px;
      font-weight: 900;
      color: var(--primary);
      margin-bottom: 8px;
    }
    
    .stat-card.accent-1 .stat-value {
      color: var(--status-success);
    }
    
    .stat-card.accent-2 .stat-value {
      color: var(--accent);
    }
    
    .stat-card.accent-3 .stat-value {
      color: var(--status-danger);
    }
    
    .stat-label {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    
    /* Breadcrumb navigation */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 24px;
      font-size: 13px;
      color: var(--text-secondary);
    }
    
    .breadcrumb a {
      color: var(--primary);
      font-weight: 600;
      transition: all 0.2s ease;
    }
    
    .breadcrumb a:hover {
      color: var(--primary-light);
      text-decoration: underline;
    }
    
    .breadcrumb span {
      color: var(--border-light);
    }
    
    /* Enhanced featured section */
    .featured-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .featured-badge {
      position: absolute;
      top: 14px;
      right: 14px;
      background: linear-gradient(135deg, var(--accent), var(--secondary));
      color: #fff;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      z-index: 5;
    }
    
    /* Mobile stats grid */
    @media (max-width: 900px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (max-width: 640px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .featured-grid {
        grid-template-columns: 1fr;
      }
      
      .stat-value {
        font-size: 28px;
      }
    }
  </style>
</head>
<body>
<!-- Header Navigation -->
<header class="topbar">
  <div class="container nav">
    <a href="index.php" class="brand">
      <div class="logo">UGM</div>
      <div>
        <strong>UGM Intelligence Space</strong>
        <span>Dashboard Catalog & Analytics Gateway</span>
      </div>
    </a>
    <div class="nav-actions">
      <a class="btn btn-secondary" href="#catalog">Browse Dashboard</a>
      <a class="btn btn-primary" href="login.php">Admin Access</a>
    </div>
  </div>
</header>

<!-- Main Content -->
<main>
  <!-- Hero Section with Enhanced Layout -->
  <section class="hero">
    <div class="container">
      <div class="hero-grid">
        <div class="hero-card">
          <span class="eyebrow">✦ Intelligence Platform for Smart Campus</span>
          <h1>Unified Dashboard Intelligence Hub for UGM</h1>
          <p class="lead">Access strategic analytics dashboards across all university domains. Discover data insights, monitor KPIs, and support data-driven decision making through our comprehensive analytics gateway.</p>
          
          <!-- Search Panel -->
          <div class="search-panel">
            <input 
              id="searchInput" 
              type="search" 
              placeholder="Search dashboards, metrics, departments, domains, or strategic topics..."
              onkeypress="if(event.key==='Enter') filterCards()"
            >
            <button class="btn btn-primary" onclick="filterCards()">Search</button>
          </div>
          
          <!-- Quick Intent Chips -->
          <div class="quick-intents">
            <button class="chip" onclick="setSearch('strategis')">Strategic KPIs</button>
            <button class="chip" onclick="setSearch('layanan')">Service Status</button>
            <button class="chip" onclick="setSearch('akademik')">Academic</button>
            <button class="chip" onclick="setSearch('keuangan')">Finance</button>
          </div>
        </div>

        <!-- Pulse Card - Right Side -->
        <aside class="pulse-card">
          <h3>📊 University Pulse</h3>
          <div class="pulse-list">
            <div class="pulse-item">
              <span>Public Dashboards</span>
              <strong><?= count($publicDashboards) ?></strong>
            </div>
            <div class="pulse-item">
              <span>Updated Today</span>
              <strong><?= $updatedToday ?></strong>
            </div>
            <div class="pulse-item">
              <span>Requiring Attention</span>
              <strong><?= $needAttention ?></strong>
            </div>
            <div class="pulse-item">
              <span>Active Domains</span>
              <strong><?= $activeDomains ?></strong>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2>📈 Dashboard Statistics</h2>
        <p>Real-time overview of data landscape</p>
      </div>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-value"><?= count($publicDashboards) ?></div>
          <div class="stat-label">Total Dashboards</div>
        </div>
        
        <div class="stat-card accent-1">
          <div class="stat-value"><?= count(array_filter($publicDashboards, fn($d) => ($d['status'] ?? '') === 'Updated')) ?></div>
          <div class="stat-label">Updated</div>
        </div>
        
        <div class="stat-card accent-2">
          <div class="stat-value"><?= count(array_filter($publicDashboards, fn($d) => ($d['update_frequency'] ?? '') === 'Harian')) ?></div>
          <div class="stat-label">Daily Updates</div>
        </div>
        
        <div class="stat-card accent-3">
          <div class="stat-value"><?= $needAttention ?></div>
          <div class="stat-label">Attention Needed</div>
        </div>
      </div>
      
      <!-- Breadcrumb Navigation -->
      <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span>/</span>
        <a href="#catalog">Dashboard Catalog</a>
        <span>/</span>
        <span id="currentFilter">All Dashboards</span>
      </div>
    </div>
  </section>

  <!-- Featured Intelligence Section -->
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2>⭐ Featured Intelligence</h2>
        <p>Most important dashboards for university leadership</p>
      </div>
      
      <div class="featured-grid">
        <?php foreach (array_slice($publicDashboards, 0, 3) as $i => $d): ?>
        <a href="detail.php?id=<?= urlencode($d['id'] ?? '') ?>" class="card dashboard-card" style="position: relative;">
          <div class="featured-badge">Featured</div>
          <div class="card-head">
            <div class="icon"><?= dashboard_icon($d['domain'] ?? '') ?></div>
            <div class="badge badge-<?= strtolower(str_replace(' ', '-', $d['status'] ?? '')) ?>"><?= e($d['status'] ?? 'Unknown') ?></div>
          </div>
          <h3><?= e($d['name'] ?? 'Unnamed') ?></h3>
          <p><?= e($d['description'] ?? 'No description available') ?></p>
          <div class="meta">
            <div><strong>Owner:</strong> <span><?= e($d['owner'] ?? 'N/A') ?></span></div>
            <div><strong>Domain:</strong> <span><?= e($d['domain'] ?? 'N/A') ?></span></div>
            <div><strong>Updated:</strong> <span><?= e($d['last_updated'] ?? 'N/A') ?></span></div>
          </div>
          <div class="card-actions">
            <button class="btn btn-primary" onclick="event.preventDefault(); window.open('<?= e($d['url'] ?? '#') ?>', '_blank');">Open Dashboard</button>
            <button class="btn btn-secondary" onclick="event.preventDefault(); window.location.href='detail.php?id=<?= urlencode($d['id'] ?? '') ?>';">Details</button>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Filters & Catalog Section -->
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2>📊 Intelligence Catalog</h2>
        <p>Browse all available dashboards and analytics resources</p>
      </div>
      
      <!-- Filter Controls -->
      <div class="filters">
        <div>
          <label for="domainFilter">Domain</label>
          <select id="domainFilter" class="filter-select" onchange="filterCards()">
            <option value="">All Domains</option>
            <?php foreach ($domains as $domain): ?>
            <option value="<?= e($domain) ?>"><?= e($domain) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="statusFilter">Status</label>
          <select id="statusFilter" class="filter-select" onchange="filterCards()">
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $status): ?>
            <option value="<?= e($status) ?>"><?= e($status) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="frequencyFilter">Update Frequency</label>
          <select id="frequencyFilter" class="filter-select" onchange="filterCards()">
            <option value="">All Frequencies</option>
            <option value="Harian">Daily</option>
            <option value="Mingguan">Weekly</option>
            <option value="Bulanan">Monthly</option>
            <option value="Triwulanan">Quarterly</option>
            <option value="Tahunan">Yearly</option>
          </select>
        </div>
        <div>
          <label for="searchFilter">Quick Search</label>
          <input 
            id="searchFilter" 
            type="text" 
            placeholder="Search in catalog..."
            onkeyup="filterCards()"
          >
        </div>
      </div>
      
      <!-- Dashboard Grid -->
      <div id="catalogGrid" class="grid">
        <?php foreach ($publicDashboards as $d): ?>
        <a href="detail.php?id=<?= urlencode($d['id'] ?? '') ?>" 
           class="card dashboard-card" 
           data-domain="<?= e($d['domain'] ?? '') ?>"
           data-status="<?= e($d['status'] ?? '') ?>"
           data-frequency="<?= e($d['update_frequency'] ?? '') ?>"
           data-search="<?= e(strtolower($d['name'] . ' ' . ($d['description'] ?? '') . ' ' . ($d['domain'] ?? '') . ' ' . ($d['tags'] ?? ''))) ?>">
          <div class="card-head">
            <div class="icon"><?= dashboard_icon($d['domain'] ?? '') ?></div>
            <div class="badge badge-<?= strtolower(str_replace(' ', '-', $d['status'] ?? '')) ?>"><?= e($d['status'] ?? 'Unknown') ?></div>
          </div>
          <h3><?= e($d['name'] ?? 'Unnamed') ?></h3>
          <p><?= e(substr($d['description'] ?? '', 0, 120) . (strlen($d['description'] ?? '') > 120 ? '...' : '')) ?></p>
          <div class="meta">
            <div><strong>Owner:</strong> <span><?= e($d['owner'] ?? 'N/A') ?></span></div>
            <div><strong>Updated:</strong> <span><?= e($d['last_updated'] ?? 'N/A') ?></span></div>
            <?php if (!empty($d['tags'])): ?>
            <div><strong>Tags:</strong> <span><?= e(substr($d['tags'] ?? '', 0, 60)) ?></span></div>
            <?php endif; ?>
          </div>
          <div class="card-actions">
            <button class="btn btn-primary" onclick="event.preventDefault(); window.open('<?= e($d['url'] ?? '#') ?>', '_blank');">Open</button>
            <button class="btn btn-secondary" onclick="event.preventDefault(); window.location.href='detail.php?id=<?= urlencode($d['id'] ?? '') ?>';">Info</button>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      
      <!-- Empty State -->
      <div id="emptyState" class="text-center" style="display: none; padding: 60px 20px;">
        <h3>No dashboards found</h3>
        <p style="color: var(--text-secondary); margin: 12px 0 20px;">Try adjusting your filters or search terms.</p>
        <button class="btn btn-secondary" onclick="resetFilters()">Reset Filters</button>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-section">
          <h4>About UGM Intelligence Space</h4>
          <ul>
            <li><a href="#">Documentation</a></li>
            <li><a href="#">API Reference</a></li>
            <li><a href="#">Data Governance</a></li>
            <li><a href="#">FAQ</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Resources</h4>
          <ul>
            <li><a href="#">Data Quality Reports</a></li>
            <li><a href="#">Dashboard Guidelines</a></li>
            <li><a href="#">Request New Dashboard</a></li>
            <li><a href="#">Support</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Contact & Support</h4>
          <ul>
            <li><a href="mailto:intelligence@ugm.ac.id">intelligence@ugm.ac.id</a></li>
            <li><a href="#">Support Portal</a></li>
            <li><a href="#">Report Issue</a></li>
            <li><a href="#">Feedback Form</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2024 Universitas Gadjah Mada. UGM Intelligence Space &mdash; Strategic Analytics Platform for Decision Support</p>
      </div>
    </div>
  </footer>
</main>

<!-- Client-side Filtering JavaScript -->
<script>
function filterCards() {
  const searchValue = document.getElementById('searchFilter')?.value?.toLowerCase() || document.getElementById('searchInput')?.value?.toLowerCase() || '';
  const domainFilter = document.getElementById('domainFilter')?.value || '';
  const statusFilter = document.getElementById('statusFilter')?.value || '';
  const frequencyFilter = document.getElementById('frequencyFilter')?.value || '';
  
  const cards = document.querySelectorAll('.dashboard-card');
  let visibleCount = 0;
  
  cards.forEach(card => {
    const cardDomain = card.dataset.domain || '';
    const cardStatus = card.dataset.status || '';
    const cardFrequency = card.dataset.frequency || '';
    const cardSearch = card.dataset.search || '';
    
    const domainMatch = !domainFilter || cardDomain === domainFilter;
    const statusMatch = !statusFilter || cardStatus === statusFilter;
    const frequencyMatch = !frequencyFilter || cardFrequency === frequencyFilter;
    const searchMatch = !searchValue || cardSearch.includes(searchValue);
    
    const isVisible = domainMatch && statusMatch && frequencyMatch && searchMatch;
    card.style.display = isVisible ? '' : 'none';
    
    if (isVisible) visibleCount++;
  });
  
  // Update breadcrumb
  let filters = [];
  if (domainFilter) filters.push(domainFilter);
  if (statusFilter) filters.push(statusFilter);
  if (frequencyFilter) filters.push(frequencyFilter);
  if (searchValue) filters.push(`"${searchValue}"`);
  
  document.getElementById('currentFilter').textContent = filters.length > 0 ? filters.join(' + ') : 'All Dashboards';
  
  // Show/hide empty state
  document.getElementById('emptyState').style.display = visibleCount === 0 ? 'block' : 'none';
}

function setSearch(value) {
  document.getElementById('searchInput').value = value;
  document.getElementById('searchFilter').value = value;
  filterCards();
  document.getElementById('catalogGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetFilters() {
  document.getElementById('searchFilter').value = '';
  document.getElementById('domainFilter').value = '';
  document.getElementById('statusFilter').value = '';
  document.getElementById('frequencyFilter').value = '';
  document.getElementById('searchInput').value = '';
  filterCards();
}

// Initialize filters on page load
document.addEventListener('DOMContentLoaded', filterCards);
</script>
</body>
</html>
