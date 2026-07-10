# 🔧 TROUBLESHOOTING & FIXES GUIDE

**Last Updated**: July 10, 2026  
**Status**: Error fixes applied ✅

---

## 🔴 KNOWN ISSUES & SOLUTIONS

### Issue #1: vendor/autoload.php Not Found

**Error Message**:
```
Warning: require_once(vendor/autoload.php): Failed to open stream
Fatal error: Uncaught Error: Failed opening required 'vendor/autoload.php'
```

**Status**: ✅ **FIXED**

**Root Cause**:
- Composer dependencies not installed
- System doesn't have Composer command available
- Code was depending on external php-dotenv library

**Solution Applied**:
1. ✅ Removed vendor/autoload.php requirement
2. ✅ Implemented native .env file parsing
3. ✅ Added error handling to bootstrap.php
4. ✅ Created .env file from template

**Files Changed**:
- ✅ config/environment.php (removed Composer dependency)
- ✅ bootstrap.php (added error handling)
- ✅ .env (created from template)

**How to Verify**:
```bash
# Option 1: Run verification script
php verify.php

# Option 2: Open browser
http://localhost/ugm-intelligence-space-poc/

# Option 3: Check error logs
tail -f logs/app.log
```

**Expected Result**:
```
✅ ALL CHECKS PASSED
✅ Application is ready to use
```

---

## 📋 QUICK FIX CHECKLIST

If you get an error on page load:

- [ ] Step 1: Check `.env` file exists
  ```bash
  ls -la .env
  # Should show: .env file with size > 0
  ```

- [ ] Step 2: Check `.env` is readable
  ```bash
  cat .env | head -5
  # Should show: APP_ENV=development
  ```

- [ ] Step 3: Check config files exist
  ```bash
  ls -la config/
  # Should show: environment.php, security.php, logger.php, etc.
  ```

- [ ] Step 4: Run verification
  ```bash
  php verify.php
  # Should show: ALL CHECKS PASSED
  ```

- [ ] Step 5: Clear browser cache
  ```
  Hard refresh: Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)
  ```

---

## 🆘 TROUBLESHOOTING BY ERROR

### Error: "APP_ENV not defined"
**Solution**:
1. Check .env file exists: `ls -la .env`
2. Check file is not empty: `cat .env | wc -l`
3. Check format: `grep "^APP_ENV=" .env`
4. If not found, copy template:
   ```bash
   cp .env.example .env
   ```

### Error: "config/environment.php not found"
**Solution**:
1. Check file exists: `ls -la config/environment.php`
2. Check it's readable: `head -5 config/environment.php`
3. If problem, restore from backup:
   ```bash
   git checkout config/environment.php
   ```

### Error: "database.php not found" (Phase 2)
**Solution**:
1. Check if Phase 2 was completed
2. If Phase 2 not done, DB_ENABLE=false is correct
3. Once Phase 2 done, run migrations:
   ```bash
   php migrations/run.php
   ```

### Error: "logs directory not writable"
**Solution**:
1. Create logs directory:
   ```bash
   mkdir -p logs
   chmod 755 logs
   ```
2. Verify writable:
   ```bash
   touch logs/test.log && rm logs/test.log
   ```

---

## 🧪 VERIFICATION SCRIPT

**File**: `verify.php`

**Usage**:
```bash
php verify.php
```

**Checks**:
1. ✅ .env file exists
2. ✅ environment.php loads
3. ✅ Constants defined
4. ✅ Environment variables loaded
5. ✅ All required files present
6. ✅ logs/ directory writable

**Output Example**:
```
======================================
UGM Intelligence Space - Verification
======================================

1. Checking .env file...
   ✅ .env file found
   📁 Size: 1234 bytes

2. Loading environment configuration...
   ✅ environment.php loaded successfully

3. Checking defined constants...
   ✅ APP_ENV = development
   ✅ APP_DEBUG = true
   ✅ APP_URL = http://localhost/ugm-intelligence-space-poc
   ✅ DB_ENABLE = false
   ✅ LDAP_ENABLED = false

4. Checking $_ENV array...
   ✅ $_ENV['APP_ENV'] found
   ✅ $_ENV['APP_DEBUG'] found
   ✅ $_ENV['APP_URL'] found

5. Checking required files...
   ✅ config/environment.php
   ✅ config/security.php
   ✅ config/logger.php
   ✅ config/ldap_authenticator.php
   ✅ config/database.php
   ✅ functions.php
   ✅ bootstrap.php
   ✅ .env

6. Checking logs directory...
   ✅ logs/ directory exists
   ✅ logs/ is writable

======================================
VERIFICATION SUMMARY
======================================
✅ ALL CHECKS PASSED
✅ Application is ready to use

📋 Next Steps:
   1. Open: http://localhost/ugm-intelligence-space-poc/
   2. Or run: php bootstrap.php
```

---

## 🔄 RECOVERY STEPS

If application is completely broken:

### Step 1: Reset Configuration
```bash
# Copy fresh configuration
cp .env.example .env
cp config/security.php.example config/security.php
```

### Step 2: Create Missing Directories
```bash
mkdir -p logs
mkdir -p backups
mkdir -p data
chmod -R 755 logs backups data
```

### Step 3: Verify Files
```bash
# Run verification
php verify.php
```

### Step 4: Clear Cache (Browser)
```
Press: Ctrl+Shift+Delete
Select: "All time"
Clear: Cookies and cached files
```

### Step 5: Restart Web Server
```bash
# If using Laragon, restart Apache
# Or restart your web server
```

---

## 📞 COMMON QUESTIONS

### Q: Do I need to install Composer?
**A**: No! The code now works without Composer. No vendor/ folder needed.

### Q: Will this work on shared hosting?
**A**: Yes! No external dependencies means better compatibility.

### Q: Is it secure to parse .env manually?
**A**: Yes, the implementation is secure:
- ✅ Filters comments
- ✅ Validates format
- ✅ Handles special characters
- ✅ Escapes values properly

### Q: What if .env is missing?
**A**: You'll get a clear error message:
```
RuntimeException: .env file not found. Please copy .env.example to .env
```

### Q: How do I update configuration?
**A**: Edit the `.env` file:
```bash
# Development
nano .env

# Or production
nano .env.production
```

---

## ✅ POST-FIX CHECKLIST

After applying fixes:

- [x] Removed vendor/autoload.php dependency
- [x] Implemented native .env parsing
- [x] Added error handling
- [x] Created .env file
- [x] Created verify.php script
- [x] Updated bootstrap.php
- [x] Updated environment.php
- [x] Created logs directory
- [x] All constants defined
- [x] Application loads without errors

---

## 📚 RELATED DOCUMENTATION

- [ERROR_FIX_REPORT.md](./ERROR_FIX_REPORT.md) - Detailed fix explanation
- [PHASE_1_README.md](./PHASE_1_README.md) - Security configuration
- [QUICK_REFERENCE.md](./QUICK_REFERENCE.md) - Quick lookup
- [verify.php](./verify.php) - Verification script

---

## 🎯 NEXT STEPS

### Immediate
1. Run: `php verify.php`
2. Check output: Should show "✅ ALL CHECKS PASSED"
3. Open browser: `http://localhost/ugm-intelligence-space-poc/`
4. Should see: Dashboard without errors

### If Still Issues
1. Check error message carefully
2. Follow troubleshooting guide above
3. Run: `php verify.php --verbose`
4. Check: `logs/app.log` for details

### Continue Development
1. Proceed with Phase 3
2. All dependencies are now resolved
3. Ready for production deployment

---

**Status**: ✅ All known issues fixed and documented

**Last Fix Date**: July 10, 2026  
**Next Review**: Phase 3 implementation

