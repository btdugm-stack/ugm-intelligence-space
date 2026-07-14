<?php
require_once __DIR__ . '/auth.php';

// Define globals early (before POST processing)
$GLOBALS['valid_domains'] = [
    'Executive Intelligence','Academic Intelligence','Research Intelligence','Finance Intelligence',
    'HR Intelligence','Student Intelligence','Service Intelligence','Infrastructure Intelligence',
    'Reputation Intelligence','Risk & Compliance Intelligence'
];
$GLOBALS['valid_statuses'] = ['Updated','Scheduled Update','Need Review','Delayed','Maintenance'];
$GLOBALS['valid_accesses'] = ['Public','Internal','Restricted'];

$dashboards = load_dashboards();
$message = '';
$message_type = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token()) {
        $errors[] = 'Security token invalid. Please refresh and try again.';
        Logger::warning('CSRF token validation failed in admin.php', ['user' => $_SESSION['username']]);
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'save') {
            // Validate input
            $id = trim($_POST['id'] ?? '') ?: 'dash-' . substr(md5(uniqid('', true)), 0, 8);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $domain = trim($_POST['domain'] ?? '');
            $owner = trim($_POST['owner'] ?? '');
            $update_frequency = trim($_POST['update_frequency'] ?? '');
            $last_updated = trim($_POST['last_updated'] ?? date('Y-m-d'));
            $status = trim($_POST['status'] ?? 'Updated');
            $access = trim($_POST['access'] ?? 'Public');
            $audience = trim($_POST['audience'] ?? '');
            $url = trim($_POST['url'] ?? '#');
            $tags = trim($_POST['tags'] ?? '');
            
            // Validation rules
            $validation_errors = validate_input([
                'name' => $name,
                'description' => $description,
                'domain' => $domain,
                'owner' => $owner,
                'url' => $url,
                'tags' => $tags,
            ], [
                'name' => 'required|min:3|max:255',
                'description' => 'required|min:10|max:500',
                'domain' => 'required|in:' . implode(',', $GLOBALS['valid_domains']),
                'owner' => 'required|min:2|max:100',
                'url' => 'url',
                'tags' => 'max:200',
            ]);
            
            if (!empty($validation_errors)) {
                foreach ($validation_errors as $field => $field_errors) {
                    $errors = array_merge($errors, $field_errors);
                }
            } elseif (!in_array($status, $GLOBALS['valid_statuses'])) {
                $errors[] = 'Invalid status selected.';
            } elseif (!in_array($access, $GLOBALS['valid_accesses'])) {
                $errors[] = 'Invalid access level selected.';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $last_updated)) {
                $errors[] = 'Invalid date format. Use YYYY-MM-DD.';
            } else {
                // Find existing record
                $found = false;
                $old_record = null;
                
                foreach ($dashboards as $i => $d) {
                    if (($d['id'] ?? '') === $id) {
                        $old_record = $d;
                        $found = true;
                        break;
                    }
                }
                
                $record = [
                    'id' => $id,
                    'name' => $name,
                    'description' => $description,
                    'domain' => $domain,
                    'owner' => $owner,
                    'update_frequency' => $update_frequency,
                    'last_updated' => $last_updated,
                    'status' => $status,
                    'access' => $access,
                    'audience' => $audience,
                    'url' => $url,
                    'tags' => $tags
                ];
                
                if ($found) {
                    $dashboards[$i] = $record;
                    Logger::log_audit('UPDATE', 'Dashboard', $id, ['old' => $old_record, 'new' => $record]);
                } else {
                    $dashboards[] = $record;
                    Logger::log_audit('CREATE', 'Dashboard', $id, ['record' => $record]);
                }
                
                save_dashboards($dashboards);
                $message = $found ? 'Dashboard updated successfully.' : 'New dashboard created successfully.';
                $message_type = 'success';
            }
        } elseif ($action === 'delete') {
            $id = trim($_POST['id'] ?? '');
            
            if (empty($id)) {
                $errors[] = 'Invalid dashboard ID.';
            } else {
                $found_record = null;
                foreach ($dashboards as $d) {
                    if (($d['id'] ?? '') === $id) {
                        $found_record = $d;
                        break;
                    }
                }
                
                if ($found_record) {
                    $dashboards = array_values(array_filter($dashboards, fn($d) => ($d['id'] ?? '') !== $id));
                    save_dashboards($dashboards);
                    Logger::log_audit('DELETE', 'Dashboard', $id, ['record' => $found_record]);
                    $message = 'Dashboard deleted successfully.';
                    $message_type = 'success';
                } else {
                    $errors[] = 'Dashboard not found.';
                }
            }
        }
    }
}

$editId = trim($_GET['edit'] ?? '');
$edit = null;

if (!empty($editId)) {
    foreach ($dashboards as $d) {
        if (($d['id'] ?? '') === $editId) {
            $edit = $d;
            break;
        }
    }
}

$domains = $GLOBALS['valid_domains'];
$statuses = $GLOBALS['valid_statuses'];
$accesses = $GLOBALS['valid_accesses'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - UGM Intelligence Space</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="admin-shell">
  <aside class="sidebar">
    <div class="brand">
      <div class="logo">UGM</div>
      <div><strong style="color:#fff">Admin Console</strong><span style="color:rgba(255,255,255,.65)">Intelligence Space</span></div>
    </div>
    <nav class="menu">
      <a class="active" href="admin.php">Dashboard Content</a>
      <a href="index.php" target="_blank">Preview Public</a>
      <a href="logout.php">Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <div class="admin-header">
      <div>
        <h1>Manage Dashboard</h1>
        <p class="lead" style="font-size:15px;margin:8px 0 0">Logged in as: <strong><?= e($_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Unknown') ?></strong></p>
      </div>
      <a class="btn btn-primary" href="admin.php">+ Add New</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert" style="background:#fff3cd;border-color:#ffc107;color:#664d03">
      <strong>Validation Errors:</strong>
      <ul style="margin:8px 0 0;padding-left:20px">
        <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <?php if ($message): ?><div class="success"><?= e($message) ?></div><?php endif; ?>

    <section class="panel">
      <h2 style="margin-top:0;color:var(--ugm-blue)"><?= $edit ? 'Edit Dashboard' : 'Add Dashboard' ?></h2>
      <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div>
            <label>Dashboard Name *</label>
            <input name="name" value="<?= e($edit['name'] ?? '') ?>" placeholder="e.g., Executive Intelligence Dashboard" required>
          </div>
          <div>
            <label>Domain *</label>
            <select name="domain" required>
              <option value="">-- Select Domain --</option>
              <?php foreach ($domains as $domain): ?>
                <option <?= (($edit['domain'] ?? '') === $domain) ? 'selected' : '' ?>><?= e($domain) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="full">
            <label>Description *</label>
            <textarea name="description" rows="3" placeholder="Describe what this dashboard monitors..." required><?= e($edit['description'] ?? '') ?></textarea>
          </div>
          <div>
            <label>Owner *</label>
            <input name="owner" value="<?= e($edit['owner'] ?? '') ?>" placeholder="e.g., DTI, Biro Perencanaan" required>
          </div>
          <div>
            <label>Audience</label>
            <input name="audience" value="<?= e($edit['audience'] ?? '') ?>" placeholder="e.g., University Leadership, Faculty">
          </div>
          <div>
            <label>Update Frequency</label>
            <select name="update_frequency">
              <option value="">-- Select Frequency --</option>
              <?php foreach (['Daily','Weekly','Monthly','Quarterly','Semester'] as $f): ?>
                <option <?= (($edit['update_frequency'] ?? '') === $f) ? 'selected' : '' ?>><?= e($f) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label>Last Updated</label>
            <input type="date" name="last_updated" value="<?= e($edit['last_updated'] ?? date('Y-m-d')) ?>">
          </div>
          <div>
            <label>Status</label>
            <select name="status">
              <option value="">-- Select Status --</option>
              <?php foreach ($statuses as $status): ?>
                <option <?= (($edit['status'] ?? '') === $status) ? 'selected' : '' ?>><?= e($status) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label>Access Level</label>
            <select name="access">
              <option value="">-- Select Access --</option>
              <?php foreach ($accesses as $access): ?>
                <option <?= (($edit['access'] ?? '') === $access) ? 'selected' : '' ?>><?= e($access) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="full">
            <label>Dashboard URL *</label>
            <input name="url" value="<?= e($edit['url'] ?? '') ?>" type="url" placeholder="https://example.com/dashboard" required>
          </div>
          <div class="full">
            <label>Tags</label>
            <input name="tags" value="<?= e($edit['tags'] ?? '') ?>" placeholder="e.g., strategic, kpi, executive (comma separated)">
          </div>
        </div>
        <br>
        <button class="btn btn-primary" type="submit">Save Dashboard</button>
      </form>
    </section>

    <section class="panel">
      <h2 style="margin-top:0;color:var(--ugm-blue)">Dashboard Content List</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Dashboard</th>
              <th>Domain</th>
              <th>Owner</th>
              <th>Status</th>
              <th>Access</th>
              <th>Last Update</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dashboards as $d): ?>
              <tr>
                <td><strong><?= e($d['name'] ?? '') ?></strong><br><small><?= e($d['description'] ?? '') ?></small></td>
                <td><?= e($d['domain'] ?? '') ?></td>
                <td><?= e($d['owner'] ?? '') ?></td>
                <td><span class="badge <?= status_class($d['status'] ?? '') ?>"><?= e($d['status'] ?? '') ?></span></td>
                <td><?= e($d['access'] ?? '') ?></td>
                <td><?= e($d['last_updated'] ?? '') ?></td>
                <td>
                  <div class="action-inline">
                    <a href="admin.php?edit=<?= e($d['id'] ?? '') ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this dashboard?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= e($d['id'] ?? '') ?>">
                      <button class="danger" type="submit">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>
</body>
</html>
