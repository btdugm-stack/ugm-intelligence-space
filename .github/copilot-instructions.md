# UGM Intelligence Space - Copilot Instructions# UGM Intelligence Space - Copilot Instructions# UGM Intelligence Space - Copilot Instructions



## Quick Start

- **Dev server**: No build needed—open `http://localhost/ugm-intelligence-space-poc/` (Apache/Laragon)

- **Admin login**: `admin` / `admin123` (hardcoded in `login.php` for PoC)## Quick Start## Project Overview

- **Data storage**: `data/dashboards.json` (JSON array, not a database)

- **All pages load functions**: `require_once __DIR__ . '/functions.php'` before using data operations- **Dev server**: No build needed—open `http://localhost/ugm-intelligence-space-poc/` (Apache/Laragon)A PHP-based dashboard catalog portal for UGM (Universitas Gadjah Mada) with dual interfaces: public (read-only) and admin (CRUD). This is a proof-of-concept demonstrating dashboard governance without a database—using JSON file storage for rapid prototyping.



## Architecture Overview- **Admin login**: `admin` / `admin123` (hardcoded in `login.php` for PoC)



### Data Flow- **Data storage**: `data/dashboards.json` (JSON array, not a database)## Architecture

- **Single source of truth**: `data/dashboards.json` (flat JSON array of dashboard objects)

- **Load/Save pattern**: `$dashboards = load_dashboards()` → modify → `save_dashboards($dashboards)`- **All pages load functions**: `require_once __DIR__ . '/functions.php'` before using data operations

- **Important**: `save_dashboards()` auto-reindexes array with `array_values()`, so array keys reset after each save

- **Access control**: Public pages filter `access === 'Public'`; admin bypasses filters entirely### Data Layer



### Core Files & Responsibilities## Architecture Overview- **Single source of truth**: `data/dashboards.json` - flat JSON array of dashboard metadata

| File | Purpose |

|------|---------|- **No database**: All CRUD operations use `load_dashboards()` and `save_dashboards()` in `functions.php`

| `functions.php` | Data I/O (`load_dashboards()`, `save_dashboards()`), escaping (`e()`), validation, icons |

| `bootstrap.php` | Optional centralized initialization; loads env + functions + config files (use for production) |### Data Flow- Dashboard schema: `id`, `name`, `description`, `domain`, `owner`, `update_frequency`, `last_updated`, `status`, `access`, `audience`, `url`, `tags`

| `auth.php` | Session guard; redirects unauthenticated users to `login.php` |

| `config/security.php` | CSRF tokens, session init, rate limiting, security headers |- **Single source of truth**: `data/dashboards.json` (flat JSON array of dashboard objects)

| `config/logger.php` | `Logger` class for audit trails (JSON logs in `logs/` directory) |

| `admin.php` | CRUD form + POST handler; validates against `$GLOBALS['valid_domains']`, statuses, accesses |- **Load/Save pattern**: `$dashboards = load_dashboards()` → modify → `save_dashboards($dashboards)`### File Structure

| `index.php`, `detail.php` | Public catalog; filter & display public dashboards |

| `login.php` | Authentication form; hardcoded credentials for PoC |- **Important**: `save_dashboards()` auto-reindexes array with `array_values()`, so array keys reset after each save- **Public pages**: `index.php` (catalog), `detail.php` (dashboard detail)



## Critical Patterns- **Access control**: Public pages filter `access === 'Public'`; admin bypasses filters entirely- **Admin pages**: `login.php`, `admin.php` (CRUD interface), `logout.php`



### 1. HTML Escaping (Security)- **Auth**: `auth.php` session guard (redirects if not logged in)

**Always use `e()` for untrusted output:**

```php### Core Files & Responsibilities- **Utilities**: `functions.php` (data access, HTML escaping, icon mapping)

<?= e($dashboard['name']) ?>        // ✓ Safe

<?= $dashboard['name'] ?>           // ✗ XSS vulnerability| File | Purpose |- **Assets**: `assets/style.css` (single CSS file with UGM blue/gold design system)

```

|------|---------|

### 2. JSON Data Safety (Defensive Programming)

**Use null coalescing + type casting for JSON fields:**| `functions.php` | Data I/O (`load_dashboards()`, `save_dashboards()`), escaping (`e()`), validation, icons |## Critical Patterns

```php

$domain = ($d['domain'] ?? '')           // String, safe default| `bootstrap.php` | Optional centralized initialization; loads env + functions + config files (use for production) |

$updated = (int)($d['last_updated'] ?? 0)  // Type casting

// Never assume JSON keys exist| `auth.php` | Session guard; redirects unauthenticated users to `login.php` |### Security & Data Handling

```

| `config/security.php` | CSRF tokens, session init, rate limiting, security headers |- **Always use `e()` function** for HTML output escaping: `<?= e($dashboard['name']) ?>`

### 3. CRUD Pattern (Data Operations)

```php| `config/logger.php` | `Logger` class for audit trails (JSON logs in `logs/` directory) |- **Access control**: Public pages filter by `access === 'Public'`, admin bypasses filters

// Always follow this sequence

$dashboards = load_dashboards();                    // 1. Read| `admin.php` | CRUD form + POST handler; validates against `$GLOBALS['valid_domains']`, statuses, accesses |- **Demo credentials**: `admin` / `admin123` hardcoded in `login.php` (session-based auth)

$dashboards[] = $newRecord;                         // 2. Modify (or $dashboards[$i] = $record for updates)

save_dashboards($dashboards);                       // 3. Write + auto-reindex| `index.php`, `detail.php` | Public catalog; filter & display public dashboards |- **Array safety**: Use null coalescing `$d['field'] ?? ''` everywhere—JSON data may have missing keys

```

| `login.php` | Authentication form; hardcoded credentials for PoC |

### 4. Array Filtering (Public View Access)

```php### Data Operations

// Standard pattern—preserve only public dashboards

$public = array_values(## Critical Patterns```php

    array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public')

);// Standard CRUD pattern

// `array_values()` resets keys to 0-based after filtering

```### 1. HTML Escaping (Security)$dashboards = load_dashboards();                    // Read JSON



### 5. POST Form Handling (Admin Actions)**Always use `e()` for untrusted output:**$dashboards[] = $newRecord;                          // Modify

```php

// In admin.php—route by action```phpsave_dashboards($dashboards);                        // Write (auto-reindexes array)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token()) { /* error */ }<?= e($dashboard['name']) ?>        // ✓ Safe```

    $action = $_POST['action'] ?? '';

    <?= $dashboard['name'] ?>           // ✗ XSS vulnerability

    if ($action === 'save') { /* create or update */ }

    elseif ($action === 'delete') { /* remove */ }```### Domain Intelligence Types

}

```Fixed domains in `admin.php`: Executive, Academic, Research, Finance, HR, Student, Service, Infrastructure, Reputation, Risk & Compliance. Icons mapped in `dashboard_icon()` function.



### 6. Logging & Audit Trail### 2. JSON Data Safety (Defensive Programming)

```php

// Log all admin changes**Use null coalescing + type casting for JSON fields:**### Status Workflow

Logger::log_audit('UPDATE', 'Dashboard', $id, ['old' => $old_record, 'new' => $record]);

Logger::warning('Security event', ['user' => $_SESSION['username']]);```php5 statuses: `Updated`, `Scheduled Update`, `Need Review`, `Delayed`, `Maintenance`. CSS classes auto-generated via `status_class()` (lowercase + hyphens).

```

$domain = ($d['domain'] ?? '')           // String, safe default

## Data Schema (Dashboard Object)

```json$updated = (int)($d['last_updated'] ?? 0)  // Type casting## Development Workflow

{

    "id": "dash-001",// Never assume JSON keys exist

    "name": "Executive Intelligence Dashboard",

    "description": "Strategic KPIs for leadership",```### Local Setup (Laragon/XAMPP)

    "domain": "Executive Intelligence",

    "owner": "Biro Perencanaan",1. Place project in `C:/laragon/www/` or `C:/xampp/htdocs/`

    "update_frequency": "Harian",

    "last_updated": "2026-07-08",### 3. CRUD Pattern (Data Operations)2. Start Apache (no MySQL needed)

    "status": "Updated",

    "access": "Public",```php3. Access: `http://localhost/ugm-intelligence-space-poc/`

    "audience": "Pimpinan Universitas",

    "url": "https://example.com/dashboard/executive",// Always follow this sequence

    "tags": "strategis,kpi,pimpinan"

}$dashboards = load_dashboards();                    // 1. Read### Making Changes

```

$dashboards[] = $newRecord;                         // 2. Modify (or $dashboards[$i] = $record for updates)- **Add new fields**: Update JSON schema in `data/dashboards.json`, add form inputs in `admin.php`, display in `index.php`/`detail.php`

**Domains** (fixed in `admin.php` lines 7-10): Executive, Academic, Research, Finance, HR, Student, Service, Infrastructure, Reputation, Risk & Compliance

save_dashboards($dashboards);                       // 3. Write + auto-reindex- **Styling**: Single CSS file uses UGM color vars (`--ugm-blue`, `--gold`, `--cyan`), gradient backgrounds, shadow system

**Statuses** (line 11): Updated, Scheduled Update, Need Review, Delayed, Maintenance

```- **Client-side filtering**: JavaScript `filterCards()` in `index.php` filters by search, domain, status using data attributes

**Access levels** (line 12): Public, Internal, Restricted



## Configuration & Environment

### 4. Array Filtering (Public View Access)## Key Conventions

### Environment Variables (`.env` or `.env.production`)

Located in project root (loaded by `config/environment.php`):```php

```

APP_ENV=development|production// Standard pattern—preserve only public dashboards### PHP Style

APP_DEBUG=true|false

LOG_LEVEL=debug|info|warning|error|critical$public = array_values(- **No classes/namespaces**: Procedural PHP with global functions

LOG_PATH=logs

SESSION_TIMEOUT=1800    array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public')- **Short tags**: Use `<?= ?>` for output, `<?php ?>` for logic blocks

CSRF_TOKEN_LENGTH=32

SESSION_COOKIE_SECURE=true|false);- **Inline templates**: PHP and HTML mixed (no templating engine)

SESSION_COOKIE_HTTPONLY=true

```// `array_values()` resets keys to 0-based after filtering- **POST actions**: Form submissions check `$_POST['action']` to route save/delete



### Logging```

- Logs stored in `logs/YYYY-MM-DD.log` (JSON format)

- Critical errors also go to `logs/error.log`### CSS Naming

- `Logger::info()`, `Logger::warning()`, `Logger::log_audit()` available globally

### 5. POST Form Handling (Admin Actions)- Utility classes: `.btn`, `.card`, `.badge`, `.chip`, `.panel`

## Styling System (Single CSS File)

```php- Color system: `--ugm-blue` (primary), `--gold` (accent), `--cyan` (interactive)

**Color variables** in `assets/style.css`:

```css// In admin.php—route by action- Layout: Grid-based (`.grid`, `.hero-grid`, `.form-grid`)

--ugm-blue    /* Primary: #003366 */

--gold        /* Accent: #FFB81C */if ($_SERVER['REQUEST_METHOD'] === 'POST') {

--cyan        /* Interactive: #00BCD4 */

```    if (!verify_csrf_token()) { /* error */ }### Filtering Logic



**Core component classes**:    $action = $_POST['action'] ?? '';Public views filter `$dashboards` arrays using `array_filter()` + arrow functions. Example:

- `.btn`, `.btn-primary`, `.btn-secondary` (buttons)

- `.card`, `.dashboard-card` (content containers)    ```php

- `.badge`, `.chip` (tags/labels)

- `.grid`, `.hero-grid`, `.form-grid` (layouts)    if ($action === 'save') { /* create or update */ }$publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));

- Status classes auto-generated: `.updated`, `.scheduled-update`, `.need-review`, etc.

    elseif ($action === 'delete') { /* remove */ }```

**Responsive**: Mobile-first with `@media (min-width: 768px)` breakpoints

}

## Development Workflows

```## Future Production Notes (from README)

### Adding a New Dashboard Field

1. Add key to JSON schema in `data/dashboards.json` with default valueCurrent PoC limitations: hardcoded auth, JSON storage, no audit trail. Production roadmap includes SSO, MySQL/PostgreSQL, RBAC, BI tool integration.

2. Add form input in `admin.php` (lines 97–140, save logic around line 27)

3. Add display/filter in `index.php` and `detail.php`### 6. Logging & Audit Trail

4. If searchable: include in `data-search` attribute for client-side filtering

5. Update validation rules in `admin.php` form handler if needed```php## Common Tasks



### Modifying Form Validation// Log all admin changes

- Edit validation rules in `admin.php` POST handler (around line 50–65)

- Rules format: `'fieldname' => 'required|min:3|max:100'`Logger::log_audit('UPDATE', 'Dashboard', $id, ['old' => $old_record, 'new' => $record]);**Add new dashboard domain**: Update `$domains` array in `admin.php` and `dashboard_icon()` in `functions.php`.

- Supported: `required`, `min:N`, `max:N`, `in:val1,val2`, `url`

- Custom validation: extend `validate_input()` in `functions.php` (lines 42–100)Logger::warning('Security event', ['user' => $_SESSION['username']]);



### Session & Authentication```**Change styling**: Edit `assets/style.css` CSS variables or component classes.

- Session initialized by `init_session()` in `config/security.php`

- Session timeout: `SESSION_TIMEOUT` env var (default 30 min)

- CSRF token generated per session: use `<?= csrf_field() ?>` in forms

- Admin check in `auth.php`: `if (!isset($_SESSION['admin_logged_in']))` → redirect## Data Schema (Dashboard Object)**Modify form fields**: Update form in `admin.php` (lines 97-140), save logic (lines 8-35), and display templates in `index.php`/`detail.php`.



## Testing & Validation```json

{

**No automated tests currently** (empty `tests/` folder). For production:    "id": "dash-001",

- `composer require phpunit/phpunit` (already in `composer.json`)    "name": "Executive Intelligence Dashboard",

- Create test fixtures in `tests/` (e.g., `DashboardTest.php`)    "description": "Strategic KPIs for leadership",

- Run: `vendor/bin/phpunit`    "domain": "Executive Intelligence",

    "owner": "Biro Perencanaan",

**Local testing**:    "update_frequency": "Harian",

1. Start Laragon/XAMPP Apache    "last_updated": "2026-07-08",

2. Clear logs: `rm logs/*.log`    "status": "Updated",

3. Visit `http://localhost/ugm-intelligence-space-poc/` (public) or `/login.php` (admin)    "access": "Public",

4. Monitor logs: `tail -f logs/*.log` to debug    "audience": "Pimpinan Universitas",

    "url": "https://example.com/dashboard/executive",

## Production Roadmap (Current Limitations)    "tags": "strategis,kpi,pimpinan"

- ✗ Hardcoded auth (plan: SSO/OIDC)}

- ✗ JSON storage (plan: MySQL/PostgreSQL)```

- ✗ No RBAC (plan: role-based access control)

- ✗ No BI integration (plan: metadata sync from BI tools)**Domains** (fixed in `admin.php` line 7-10): Executive, Academic, Research, Finance, HR, Student, Service, Infrastructure, Reputation, Risk & Compliance



**For production**: Migrate to `bootstrap.php` initialization + use `config/ldap_authenticator.php` + database queries instead of JSON I/O**Statuses** (line 11): `Updated`, `Scheduled Update`, `Need Review`, `Delayed`, `Maintenance`


**Access levels** (line 12): `Public`, `Internal`, `Restricted`

## Configuration & Environment

### Environment Variables (`.env` or `.env.production`)
Located in project root (loaded by `config/environment.php`):
```
APP_ENV=development|production
APP_DEBUG=true|false
LOG_LEVEL=debug|info|warning|error|critical
LOG_PATH=logs
SESSION_TIMEOUT=1800
CSRF_TOKEN_LENGTH=32
SESSION_COOKIE_SECURE=true|false
SESSION_COOKIE_HTTPONLY=true
```

### Logging
- Logs stored in `logs/YYYY-MM-DD.log` (JSON format)
- Critical errors also go to `logs/error.log`
- `Logger::info()`, `Logger::warning()`, `Logger::log_audit()` available globally

## Styling System (Single CSS File)

**Color variables** in `assets/style.css`:
```css
--ugm-blue    /* Primary: #003366 */
--gold        /* Accent: #FFB81C */
--cyan        /* Interactive: #00BCD4 */
```

**Core component classes**:
- `.btn`, `.btn-primary`, `.btn-secondary` (buttons)
- `.card`, `.dashboard-card` (content containers)
- `.badge`, `.chip` (tags/labels)
- `.grid`, `.hero-grid`, `.form-grid` (layouts)
- Status classes auto-generated: `.updated`, `.scheduled-update`, `.need-review`, etc.

**Responsive**: Mobile-first with `@media (min-width: 768px)` breakpoints

## Development Workflows

### Adding a New Dashboard Field
1. Add key to JSON schema in `data/dashboards.json` with default value
2. Add form input in `admin.php` (lines 97–140, save logic around line 27)
3. Add display/filter in `index.php` and `detail.php`
4. If searchable: include in `data-search` attribute for client-side filtering
5. Update validation rules in `admin.php` form handler if needed

### Modifying Form Validation
- Edit validation rules in `admin.php` POST handler (around line 50–65)
- Rules format: `'fieldname' => 'required|min:3|max:100'`
- Supported: `required`, `min:N`, `max:N`, `in:val1,val2`, `url`
- Custom validation: extend `validate_input()` in `functions.php` (lines 42–100)

### Session & Authentication
- Session initialized by `init_session()` in `config/security.php`
- Session timeout: `SESSION_TIMEOUT` env var (default 30 min)
- CSRF token generated per session: use `<?= csrf_field() ?>` in forms
- Admin check in `auth.php`: `if (!isset($_SESSION['admin_logged_in']))` → redirect

## Testing & Validation

**No automated tests currently** (empty `tests/` folder). For production:
- `composer require phpunit/phpunit` (already in `composer.json`)
- Create test fixtures in `tests/` (e.g., `DashboardTest.php`)
- Run: `vendor/bin/phpunit`

**Local testing**:
1. Start Laragon/XAMPP Apache
2. Clear logs: `rm logs/*.log`
3. Visit `http://localhost/ugm-intelligence-space-poc/` (public) or `/login.php` (admin)
4. Monitor logs: `tail -f logs/*.log` to debug

## Production Roadmap (Current Limitations)
- ✗ Hardcoded auth (plan: SSO/OIDC)
- ✗ JSON storage (plan: MySQL/PostgreSQL)
- ✗ No RBAC (plan: role-based access control)
- ✗ No BI integration (plan: metadata sync from BI tools)

**For production**: Migrate to `bootstrap.php` initialization + use `config/ldap_authenticator.php` + database queries instead of JSON I/O
