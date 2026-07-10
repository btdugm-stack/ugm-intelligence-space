<?php
require_once __DIR__ . '/functions.php';
$id = $_GET['id'] ?? '';
$dashboards = load_dashboards();
$dashboard = null;
foreach ($dashboards as $d) {
    if (($d['id'] ?? '') === $id && ($d['access'] ?? '') === 'Public') {
        $dashboard = $d;
        break;
    }
}
if (!$dashboard) {
    http_response_code(404);
    echo "Dashboard tidak ditemukan atau tidak tersedia untuk publik.";
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($dashboard['name']) ?> - UGM Intelligence Space</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="container nav">
    <a href="index.php" class="brand">
      <div class="logo">UGM</div>
      <div><strong>UGM Intelligence Space</strong><span>Dashboard Detail</span></div>
    </a>
    <a class="btn btn-secondary" href="index.php">← Kembali</a>
  </div>
</header>
<main class="section">
  <div class="container">
    <div class="hero-card">
      <span class="eyebrow"><?= dashboard_icon($dashboard['domain'] ?? '') ?> <?= e($dashboard['domain']) ?></span>
      <h1><?= e($dashboard['name']) ?></h1>
      <p class="lead"><?= e($dashboard['description']) ?></p>
      <div class="quick-intents">
        <span class="badge <?= status_class($dashboard['status']) ?>"><?= e($dashboard['status']) ?></span>
        <span class="chip">Owner: <?= e($dashboard['owner']) ?></span>
        <span class="chip">Update: <?= e($dashboard['update_frequency']) ?></span>
        <span class="chip">Access: <?= e($dashboard['access']) ?></span>
      </div>
      <div class="panel" style="margin-top:24px">
        <div class="form-grid">
          <div><label>Business Question</label><p>Dashboard ini membantu pengguna memahami kondisi, tren, dan area yang membutuhkan perhatian pada domain <?= e($dashboard['domain']) ?>.</p></div>
          <div><label>Primary Audience</label><p><?= e($dashboard['audience']) ?></p></div>
          <div><label>Last Updated</label><p><?= e($dashboard['last_updated']) ?></p></div>
          <div><label>Tags</label><p><?= e($dashboard['tags']) ?></p></div>
          <div class="full"><label>Recommended Usage</label><p>Gunakan dashboard ini sebagai referensi awal untuk rapat koordinasi, monitoring capaian, evaluasi program, dan pengambilan keputusan berbasis data.</p></div>
        </div>
      </div>
      <a class="btn btn-primary" target="_blank" href="<?= e($dashboard['url']) ?>">Open Dashboard</a>
    </div>
  </div>
</main>
</body>
</html>
