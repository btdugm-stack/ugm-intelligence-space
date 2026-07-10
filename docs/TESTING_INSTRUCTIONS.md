# 🧪 TESTING & VERIFICATION INSTRUCTIONS

**Objective**: Verify that the error fix is working correctly  
**Time**: ~5 minutes  
**Difficulty**: Easy

---

## 🎯 QUICK START (2 minutes)

### Step 1: Run Verification Script
```bash
cd c:\laragon\www\ugm-intelligence-space-poc
php verify.php
```

**Expected Output**:
```
✅ ALL CHECKS PASSED
✅ Application is ready to use
```

### Step 2: Open in Browser
```
URL: http://localhost/ugm-intelligence-space-poc/
```

**Expected Result**: You should see the dashboard without any PHP errors.

### Step 3: Success!
If both steps worked, the error is completely fixed ✅

---

## 📋 DETAILED VERIFICATION (5 minutes)

If you want to verify each component:

### 1. Check .env File
```bash
# Verify file exists
ls -la .env
# Should show: .env with size > 0

# View content
cat .env | head -10
# Should show:
# APP_ENV=development
# APP_DEBUG=true
# APP_URL=...
```

### 2. Check Configuration Loading
```bash
# Test environment.php loads
php -r "require_once 'config/environment.php'; echo 'SUCCESS: environment.php loaded';"
# Should output: SUCCESS: environment.php loaded
```

### 3. Check Constants Defined
```php
<?php
require_once 'config/environment.php';

echo "APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "\n";
echo "APP_DEBUG: " . (defined('APP_DEBUG') ? (APP_DEBUG ? 'true' : 'false') : 'NOT DEFINED') . "\n";
echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "\n";
?>
```

Save as `test-config.php` and run:
```bash
php test-config.php
# Should show all values
```

### 4. Check Bootstrap
```php
<?php
echo "Testing bootstrap...\n";
require_once 'bootstrap.php';
echo "✅ Bootstrap loaded successfully\n";
?>
```

Save as `test-bootstrap.php` and run:
```bash
php test-bootstrap.php
# Should show: Bootstrap loaded successfully
```

### 5. Check Logs Directory
```bash
# Create logs directory if needed
mkdir -p logs

# Check it's writable
touch logs/test.log && rm logs/test.log
# If successful, directory is writable ✅
```

---

## 🌐 BROWSER TESTING

### Test 1: Load Dashboard
1. Open: `http://localhost/ugm-intelligence-space-poc/`
2. Wait for page to load
3. Check: No PHP errors in page
4. Check: Dashboard displays properly

**Expected**: ✅ Clean dashboard view

### Test 2: Check Browser Console
1. Press: F12 (or Ctrl+Shift+I)
2. Go to: Console tab
3. Check: No red errors
4. Check: No JavaScript errors

**Expected**: ✅ Console clear (or only normal warnings)

### Test 3: Check Network Tab
1. Press: F12
2. Go to: Network tab
3. Reload page
4. Check: All files load (200 status)

**Expected**: ✅ All files with 200 status, no 404s or 500s

### Test 4: View Page Source
1. Right-click: View Page Source (or Ctrl+U)
2. Search: `<?php`
3. Check: No visible PHP code
4. Check: Clean HTML structure

**Expected**: ✅ Clean HTML, no PHP code visible

---

## 🔍 DETAILED VERIFICATION SCRIPT

### Run Full Verification
```bash
php verify.php
```

### Example Output
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

## ✅ COMPREHENSIVE CHECKLIST

### Configuration Files
- [ ] `.env` file exists and readable
- [ ] `config/environment.php` loads without errors
- [ ] `config/security.php` loads without errors
- [ ] `config/logger.php` loads without errors
- [ ] `config/ldap_authenticator.php` loads without errors
- [ ] `config/database.php` loads without errors

### Constants & Variables
- [ ] `APP_ENV` constant defined
- [ ] `APP_DEBUG` constant defined
- [ ] `APP_URL` constant defined
- [ ] `DB_ENABLE` constant defined
- [ ] `LDAP_ENABLED` constant defined
- [ ] `$_ENV` array populated

### Application Files
- [ ] `bootstrap.php` loads without errors
- [ ] `functions.php` loads without errors
- [ ] `index.php` loads in browser
- [ ] `admin.php` loads in browser
- [ ] `login.php` loads in browser

### Directories
- [ ] `logs/` directory exists
- [ ] `logs/` directory is writable
- [ ] `backups/` directory exists (Phase 2)
- [ ] `migrations/` directory exists (Phase 2)

### No Errors
- [ ] No PHP warnings in browser
- [ ] No PHP errors in browser
- [ ] No 404 errors in console
- [ ] No 500 errors in console
- [ ] Browser console clean

---

## 🐛 DEBUGGING IF ISSUES FOUND

### If verify.php shows failures:

**Issue**: "APP_ENV not defined"
```bash
# Solution: Check .env file
cat .env | grep APP_ENV
# Should show: APP_ENV=development
```

**Issue**: ".env file not found"
```bash
# Solution: Create from template
cp .env.example .env
```

**Issue**: "config/environment.php not found"
```bash
# Solution: Check file exists
ls -la config/environment.php
# If missing, restore from backup
git checkout config/environment.php
```

**Issue**: "logs/ not writable"
```bash
# Solution: Create and fix permissions
mkdir -p logs
chmod 755 logs
```

---

## 📊 TEST RESULTS TRACKER

After running tests, fill in this:

```
Test Date: ______________
Tested By: ______________

[  ] Verification script passed
[  ] Browser dashboard loads
[  ] Browser console clean
[  ] No PHP errors
[  ] All config files load
[  ] Constants defined
[  ] .env file readable
[  ] logs/ writable

Overall Status: ___________
Issues Found: ___________
Resolution: ___________
```

---

## 🎯 EXPECTED TEST RESULTS

### ✅ ALL TESTS PASS
```
- verify.php: ALL CHECKS PASSED
- Browser: Dashboard displays
- Console: No errors
- Config: All loaded
- Files: All readable
- Logs: Directory writable
Status: ✅ READY FOR PRODUCTION
```

### ⚠️ SOME TESTS FAIL
```
- Check error message carefully
- Follow troubleshooting guide
- Run: php verify.php
- Check: logs/app.log for details
- If stuck: See TROUBLESHOOTING_GUIDE.md
```

---

## 📞 WHAT TO DO IF TESTS FAIL

### Step 1: Identify the Issue
- Run `php verify.php` to see what fails
- Note the exact error message
- Check `logs/app.log` for details

### Step 2: Reference Guide
- See `TROUBLESHOOTING_GUIDE.md`
- Find your error in the guide
- Follow the suggested solution

### Step 3: Apply Fix
- Make the recommended change
- Re-run `php verify.php`
- Check if issue resolved

### Step 4: If Still Stuck
- Verify file permissions: `ls -la .env`
- Check file exists: `cat config/environment.php | head`
- Look at error log: `tail -f logs/app.log`
- Create fresh .env: `cp .env.example .env`

---

## 🎓 VERIFICATION DOCUMENTATION

For reference during testing:

| Document | Use |
|----------|-----|
| verify.php | Automated testing |
| TROUBLESHOOTING_GUIDE.md | Problem solving |
| ERROR_FIX_REPORT.md | Why this happened |
| ERROR_RESOLUTION_SUMMARY.md | What was fixed |

---

## 🚀 AFTER TESTING

### If All Tests Pass ✅
1. Continue with Phase 3 development
2. Application is ready for use
3. No further configuration needed

### If Some Tests Fail ⚠️
1. Follow troubleshooting guide
2. Fix identified issues
3. Re-run tests
4. Continue when all pass

---

## 📋 QUICK REFERENCE

**Verify in 30 seconds**:
```bash
php verify.php
```

**Test in browser**: 
```
http://localhost/ugm-intelligence-space-poc/
```

**Check config**:
```bash
cat .env | head -5
```

**View logs**:
```bash
tail -f logs/app.log
```

---

## ✨ SUCCESS INDICATORS

When everything is working:
- ✅ Dashboard loads without errors
- ✅ Browser console is clean
- ✅ verify.php shows all pass
- ✅ No PHP warnings/errors
- ✅ Can navigate application

---

**Testing Complete!** ✅

Once all tests pass, you're ready to proceed with Phase 3.

---

*Testing Guide Version: 1.0*  
*Last Updated: July 10, 2026*  
*Status: Ready for use*

