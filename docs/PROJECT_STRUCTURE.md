# 📂 UGM Intelligence Space - Project Structure

## 🎯 Struktur Direktori Terorganisir

```
ugm-intelligence-space-poc/
│
├── 📄 FILE INTI APLIKASI (di root)
│   ├── index.php              ← Public page - dashboard catalog
│   ├── detail.php             ← Dashboard detail view
│   ├── login.php              ← Admin login
│   ├── admin.php              ← Admin panel CRUD
│   ├── auth.php               ← Session middleware
│   ├── logout.php             ← Logout handler
│   ├── functions.php          ← Core functions (400+ lines)
│   └── bootstrap.php          ← Bootstrap initialization
│
├── 📁 docs/                   ← DOKUMENTASI
│   ├── INDEX.md               ← Start here - daftar dokumentasi
│   ├── README_MAIN.md         ← Dokumentasi utama
│   ├── START_HERE.md          ← Panduan pemula
│   ├── QUICK_REFERENCE.md     ← Referensi cepat
│   ├── COMPLETE_FIX_REPORT.txt
│   ├── ERROR_5_FINAL_FIX.md
│   ├── PRODUCTION_ROADMAP.md
│   ├── TESTING_INSTRUCTIONS.md
│   └── ... (25+ dokumentasi lainnya)
│
├── 📁 tests/                  ← TEST & VERIFICATION
│   ├── final-verify.php       ← Main verification script
│   ├── check-syntax.php
│   ├── diagnostic.php
│   ├── test-*.php             ← Various test files
│   └── verify-*.php
│
├── 📁 config/                 ← KONFIGURASI
│   └── environment.php        ← Load .env configuration
│
├── 📁 data/                   ← DATA (JSON Storage)
│   └── dashboards.json        ← Database - dashboard records
│
├── 📁 assets/                 ← STATIC ASSETS
│   └── style.css              ← Main CSS styling
│
├── 📁 logs/                   ← APPLICATION LOGS
│   ├── auth.log               ← Authentication attempts
│   ├── audit.log              ← Admin operation audit trail
│   └── app.log                ← Application events
│
├── 📁 scripts/                ← UTILITY SCRIPTS
│   └── (scripts untuk automation)
│
├── 📁 views/                  ← VIEW TEMPLATES
│   └── (view templates jika ada)
│
├── 📁 backups/                ← DATA BACKUPS
│   └── (backup files)
│
├── 📁 upload/                 ← UPLOAD DIRECTORY
│   └── (user uploads)
│
├── 📁 vendor/                 ← DEPENDENCIES
│   └── (Composer packages)
│
├── 📁 migrations/             ← DATABASE MIGRATIONS
│   └── (untuk production DB)
│
├── 📁 .github/                ← GITHUB CONFIG
│   └── workflows/
│
├── 📁 .qodo/                  ← CODE ANALYSIS
│   └── (analysis files)
│
├── ⚙️ CONFIG FILES
│   ├── .env                   ← Environment variables
│   ├── .env.example           ← Template .env
│   ├── .env.production        ← Production config
│   ├── composer.json          ← Composer dependencies
│   ├── .gitignore             ← Git ignore rules
│   └── .htaccess              ← Apache config (jika ada)
│
└── 📚 PROJECT FILES
    └── README_MAIN.md         ← README project (jika di root)

```

---

## 📖 File Penting di Setiap Folder

### ✅ Aplikasi Inti (Root)
**Fungsi:** Menjalankan aplikasi
- `index.php` - Entry point publik
- `admin.php` - Admin panel
- `functions.php` - Business logic (PRIORITAS TINGGI)

### 📄 docs/
**Fungsi:** Dokumentasi & referensi
- `INDEX.md` - Navigasi dokumentasi
- `QUICK_REFERENCE.md` - Cheat sheet
- `TROUBLESHOOTING_GUIDE.md` - Problem solving

### 🧪 tests/
**Fungsi:** Testing & verification
- `final-verify.php` - Verify all systems
- `check-syntax.php` - Syntax checking
- `test-*.php` - Feature tests

### ⚙️ config/
**Fungsi:** Konfigurasi aplikasi
- `environment.php` - Load .env

### 📦 data/
**Fungsi:** Penyimpanan data (JSON)
- `dashboards.json` - Dashboard records

### 🎨 assets/
**Fungsi:** Frontend resources
- `style.css` - Styling UGM theme

### 📊 logs/
**Fungsi:** Application logging
- `auth.log` - Login attempts
- `audit.log` - Admin operations
- `app.log` - General events

---

## 🚀 Cara Menggunakan Struktur

### 1. Untuk Development
```
Fokus pada file di root + functions.php
Lihat docs/ jika butuh referensi
Jalankan tests/final-verify.php untuk testing
```

### 2. Untuk Maintenance
```
- Bug fix? → Lihat functions.php
- Styling? → Edit assets/style.css
- Data? → Check data/dashboards.json
- Logs? → Check logs/auth.log, audit.log
```

### 3. Untuk Troubleshooting
```
1. Baca docs/TROUBLESHOOTING_GUIDE.md
2. Jalankan tests/final-verify.php
3. Check logs/app.log
4. Review functions.php untuk error
```

### 4. Untuk Production
```
1. Review docs/PRODUCTION_ROADMAP.md
2. Update .env.production
3. Backup data/dashboards.json
4. Test dengan tests/final-verify.php
5. Monitor logs/
```

---

## 📊 File Count Summary

| Folder | Count | Tipe |
|--------|-------|------|
| Root (PHP) | 8 | Core files |
| docs/ | 30+ | Dokumentasi |
| tests/ | 18+ | Test files |
| config/ | 1 | Config |
| data/ | 1 | JSON data |
| assets/ | 1+ | CSS/images |
| logs/ | 3+ | Log files |
| **Total** | **100+** | Mixed |

---

## 🎯 File Priority

### 🔴 CRITICAL (Jangan dihapus)
- `index.php` - Public page
- `admin.php` - Admin functionality
- `functions.php` - Core logic
- `data/dashboards.json` - Database

### 🟡 IMPORTANT
- `auth.php` - Security
- `config/environment.php` - Config
- `assets/style.css` - UI

### 🟢 OPTIONAL
- `bootstrap.php` - Legacy
- Test files - Testing
- Doc files - Reference

---

## ✨ Tips Organisasi

1. **Jangan modifikasi structure tanpa alasan**
2. **Backup data/dashboards.json secara regular**
3. **Cek logs/ jika ada issues**
4. **Gunakan tests/final-verify.php untuk QA**
5. **Update docs/ jika ada changes**

---

Generated: July 10, 2026
Last Organized: July 10, 2026
