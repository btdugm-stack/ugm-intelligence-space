# Phase 2: Database Migration - QUICK START GUIDE

**Status**: ✅ Implementation Complete  
**Timeline**: 2 weeks  
**Priority**: P0 - Critical

---

## 📋 What Was Created

### Configuration
- ✅ `config/database.php` - PDO connection manager with singleton pattern
- ✅ `migrations/run.php` - Migration runner for schema creation
- ✅ `.env` updated with database configuration options

### Schema Files
- ✅ `migrations/schemas/dashboards.sql` - Main dashboards table
- ✅ `migrations/schemas/audit_logs.sql` - Audit trail table
- ✅ `migrations/schemas/users.sql` - User management table

### Scripts
- ✅ `scripts/migrate_data.php` - JSON to database migration
- ✅ `scripts/backup_database.php` - Backup & restore utilities
- ✅ `backups/` directory - For storing backups

### Directories
- ✅ `migrations/schemas/` - SQL schema files
- ✅ `scripts/` - Utility scripts
- ✅ `backups/` - Backup storage

---

## 🚀 INSTALLATION STEPS

### Step 1: Setup MySQL Database (5 min)

**Create MySQL database and user:**

```sql
-- Login to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE ugm_intelligence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'ugm_app_user'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant permissions
GRANT ALL PRIVILEGES ON ugm_intelligence.* TO 'ugm_app_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### Step 2: Update Environment Configuration (2 min)

Edit `.env` file:

```env
DB_ENABLE=true
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ugm_intelligence
DB_USERNAME=ugm_app_user
DB_PASSWORD=strong_password
```

### Step 3: Run Database Migrations (2 min)

```powershell
# From project root
php migrations/run.php
```

Expected output:
```
Starting database migrations...
Database: ugm_intelligence @ localhost

✅ All migrations completed successfully!
```

### Step 4: Migrate Data from JSON (1 min)

```powershell
php scripts/migrate_data.php
```

Expected output:
```
🚀 Starting data migration...
Source: JSON file
Target: MySQL database
Database: ugm_intelligence @ localhost

📦 JSON file backed up to: dashboards_backup_2026-07-10_14-30-45.json
📂 Loading JSON file...
📊 Total records to migrate: 10

✅ Migrated 10/10 records

🔍 Validating migrated data...
✅ Data integrity verified: 10 records in database

📊 Migration Summary:
├─ Total records: 10
├─ Migrated: 10
├─ Failed: 0
└─ Success rate: 100.00%

✅ Migration completed successfully!
```

### Step 5: Create Initial Backup (1 min)

```powershell
php scripts/backup_database.php create
```

---

## 📊 Database Schema Overview

### dashboards Table
```sql
- id (VARCHAR 36) PRIMARY KEY
- name, description, domain, owner (Core metadata)
- update_frequency, last_updated, status (Update tracking)
- access (Public/Internal/Restricted)
- audience, url, tags (Additional metadata)
- created_at, updated_at, created_by, updated_by (Audit)
- is_deleted (Soft delete support)
```

### audit_logs Table
```sql
- id (BIGINT) PRIMARY KEY
- dashboard_id (Foreign key to dashboards)
- action, action_type (Operation type)
- old_values, new_values (JSON for before/after)
- username, ip_address, user_agent (User tracking)
- timestamp (When operation occurred)
```

### users Table
```sql
- id (INT) PRIMARY KEY
- username, display_name, email (User identity)
- role (admin/editor/viewer)
- status (active/inactive/suspended)
- last_login, login_attempts, locked_until (Security)
- created_at, updated_at (Timestamps)
```

---

## 🛠️ UTILITY COMMANDS

### Backup Management

**Create backup:**
```powershell
php scripts/backup_database.php create
```

**List backups:**
```powershell
php scripts/backup_database.php list
```

**Restore from backup:**
```powershell
php scripts/backup_database.php restore dashboards_2026-07-10_14-30-45.sql
```

**Cleanup old backups (>30 days):**
```powershell
php scripts/backup_database.php cleanup
```

**View backup statistics:**
```powershell
php scripts/backup_database.php stats
```

---

## 💾 DATA MIGRATION DETAILS

### What Gets Migrated
- ✅ Dashboard metadata (name, description, domain, etc)
- ✅ Access control (public/internal/restricted)
- ✅ Timestamps (last_updated)
- ✅ Relationships (owner, audience)
- ✅ Searchable content (name, tags, description indexed)

### What's NEW in Database
- ✅ Soft delete support (is_deleted flag)
- ✅ Full audit trail (audit_logs table)
- ✅ User management (users table)
- ✅ Full-text search on dashboards
- ✅ Automatic timestamps (created_at, updated_at)

### Backup Location
- JSON backup: `backups/dashboards_backup_YYYY-MM-DD_HH-MM-SS.json`
- Database backup: `backups/ugm_intelligence_YYYY-MM-DD_HH-MM-SS.sql`

---

## 🔍 VERIFICATION CHECKLIST

After running migrations, verify:

- [ ] `php migrations/run.php` runs successfully
- [ ] All 3 tables created (dashboards, audit_logs, users)
- [ ] `php scripts/migrate_data.php` completes 100%
- [ ] Database backup created in `backups/` folder
- [ ] JSON file backed up before deletion
- [ ] Can query dashboards table with records
- [ ] Audit logs table empty (ready for logging)
- [ ] Users table empty (ready for user data)

---

## 📝 CONFIGURATION REFERENCE

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| DB_ENABLE | false | Enable database (vs JSON) |
| DB_CONNECTION | mysql | Database system |
| DB_HOST | localhost | Database server |
| DB_PORT | 3306 | Database port |
| DB_DATABASE | ugm_intelligence | Database name |
| DB_USERNAME | root | Database user |
| DB_PASSWORD | (empty) | Database password |

### Connection Details

**Development:**
```env
DB_ENABLE=false          # Uses JSON file
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ugm_intelligence
```

**Production:**
```env
DB_ENABLE=true           # Uses database
DB_HOST=prod-db-server.internal
DB_PORT=3306
DB_DATABASE=ugm_intelligence_prod
DB_USERNAME=ugm_app_user
DB_PASSWORD=<strong-password>
```

---

## 🔐 SECURITY CONSIDERATIONS

### Database User Permissions
```sql
-- Minimal permissions for application user
GRANT SELECT, INSERT, UPDATE, DELETE ON ugm_intelligence.* TO 'ugm_app_user'@'localhost';

-- Backup user (for mysqldump)
CREATE USER 'backup_user'@'localhost' IDENTIFIED BY 'backup_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW ON ugm_intelligence.* TO 'backup_user'@'localhost';
```

### Backup Security
- Store backups outside web root: ✅ `backups/` directory
- Use strong encryption password for backup files
- Limit backup directory permissions: `chmod 700 backups/`
- Store off-site copies of backups
- Test restore procedures regularly

### Connection Security
- Use prepared statements: ✅ All queries use PDO prepare
- Disable SQL error output in production: ✅ APP_DEBUG=false
- Use connection timeout: Configured in PDO
- Connection pooling ready: Singleton pattern

---

## 🚨 TROUBLESHOOTING

### Issue: "Access denied for user 'root'@'localhost'"
**Solution**: 
```powershell
# Test MySQL connection
mysql -h localhost -u root -p

# Verify credentials in .env
```

### Issue: "Cannot find MySQL/PHP extension"
**Solution**:
```powershell
# Check PHP extensions
php -m | findstr -i pdo

# Install if needed via Laragon extensions panel
```

### Issue: "Migration fails with charset error"
**Solution**:
```sql
-- Ensure utf8mb4 support
ALTER DATABASE ugm_intelligence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Issue: "Backup file too large"
**Solution**:
- Enable gzip compression (automatic on Linux/Mac)
- Increase disk space
- Clean up old logs from audit_logs table
- Archive historical data

---

## 📈 NEXT STEPS

### Immediate (Today)
- [ ] Create MySQL database
- [ ] Update .env with credentials
- [ ] Run migrations
- [ ] Migrate data from JSON
- [ ] Create initial backup

### This Week
- [ ] Update application to use database
- [ ] Test all CRUD operations
- [ ] Verify audit logging works
- [ ] Setup backup scheduling

### Next Phase (Phase 3)
- [ ] Enhance error handling
- [ ] Setup Sentry integration
- [ ] Create log analysis dashboard
- [ ] Implement data export

---

## 📚 Related Documentation

- [PHASE_2_PLAN.md](PHASE_2_PLAN.md) - Detailed implementation plan
- [PHASE_2_LOG.md](PHASE_2_LOG.md) - Implementation log (to be created)
- [PRODUCTION_ROADMAP.md](PRODUCTION_ROADMAP.md) - Full 8-phase roadmap
- `.env.example` - Configuration template

---

## ✅ COMPLETION CHECKLIST

Phase 2 is complete when:

- [ ] Database created and configured
- [ ] All migrations run successfully
- [ ] Data migrated from JSON (100% success)
- [ ] All 3 tables verified with data
- [ ] First backup created
- [ ] JSON file backed up
- [ ] All tests passing
- [ ] Documentation complete

---

**Ready to proceed to Phase 3?** 
Confirm all checkboxes above are checked, then proceed with Phase 3: Error Handling & Logging

