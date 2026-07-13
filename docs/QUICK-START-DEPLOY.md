# UGM Intelligence Space - Deployment Quick Start

**Repository**: https://github.com/btdugm-stack/ugm-intelligence-space.git

## 🚀 Deploy ke GitHub dalam 3 Langkah

### Step 1: Buka PowerShell Admin
```
cd c:\laragon\www\ugm-intelligence-space-poc
```

### Step 2: Jalankan Setup Script
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process
.\setup-git.ps1
```

### Step 3: Push ke GitHub
Ketika diminta credentials, gunakan:
- **Username**: GitHub username
- **Password**: Personal Access Token (PAT)

Atau gunakan SSH jika sudah configured.

---

## 📋 Verification Checklist

Setelah push, pastikan:

- [x] Repository ada di GitHub
- [x] Branch adalah `main`
- [x] Semua file ter-upload
- [x] `.env` file TIDAK ter-upload
- [x] `vendor/` directory TIDAK ter-upload
- [x] `.github/copilot-instructions.md` ada
- [x] Initial commit terlihat di GitHub

---

## 🔍 Verifikasi Status

```bash
# Lihat remote configuration
git remote -v

# Lihat commit history
git log --oneline

# Lihat status
git status

# Lihat branch
git branch -v

# Lihat apa yang ada di GitHub
git ls-remote origin
```

---

## 📁 File Structure yang Akan Di-Upload

```
ugm-intelligence-space/
├── .git/                          # Git metadata
├── .github/
│   └── copilot-instructions.md   # AI instructions
├── .gitignore                     # Git ignore rules
├── .gitmessage                    # Commit template
├── admin.php                      # Admin interface
├── auth.php                       # Auth guard
├── bootstrap.php                  # App initialization
├── index.php                      # Public interface
├── login.php                      # Login page
├── functions.php                  # Utility functions
├── composer.json                  # PHP dependencies
├── assets/
│   └── style.css                 # Styling
├── config/
│   ├── environment.php           # Env loader
│   ├── logger.php                # Logging
│   ├── security.php              # Security
│   └── ldap_authenticator.php    # LDAP auth
├── data/
│   └── dashboards.json           # Dashboard data
├── logs/                          # Log files (tracked)
├── migrations/                    # DB migrations
├── scripts/                       # Utility scripts
├── tests/                         # Test files
├── docs/                          # Documentation
│   ├── README.md
│   ├── DEPLOYMENT-GUIDE.md       # This file
│   └── (other docs)
├── README.md                      # Main readme
├── DEPLOYMENT-GUIDE.md            # Deployment docs
├── setup-git.bat                  # Batch setup
└── setup-git.ps1                  # PowerShell setup
```

## ❌ Files yang TIDAK akan Di-Upload (`.gitignore`)

```
.env                    # Environment variables
.env.local             # Local overrides
vendor/                # Composer packages
node_modules/          # NPM packages
composer.lock          # Dependency lock
.vscode/               # IDE config
.idea/                 # IDE config
logs/                  # Log files
*.log                  # Log files
tmp/                   # Temp files
cache/                 # Cache files
backups/               # Backup files
upload/                # User uploads
.DS_Store              # macOS
Thumbs.db              # Windows
```

---

## 🔑 GitHub Authentication

### Option 1: HTTPS + Personal Access Token (Quick & Safe)
1. Go to: https://github.com/settings/tokens
2. Click "Generate new token (classic)"
3. Select scope: `repo`
4. Copy token
5. Use as password in Git prompt

### Option 2: SSH (Recommended for automation)
```bash
# Generate key if needed
ssh-keygen -t ed25519 -C "dev@ugm.ac.id"

# Add to GitHub: Settings → SSH and GPG keys
# Copy content from ~/.ssh/id_ed25519.pub

# Update remote if needed
git remote set-url origin git@github.com:btdugm-stack/ugm-intelligence-space.git

# Test connection
ssh -T git@github.com
```

---

## ⚠️ Important Notes

**Before Pushing:**
1. ✅ Verify `.gitignore` is correct
2. ✅ Check no `.env` files are staged
3. ✅ Confirm no sensitive data in commits
4. ✅ Review `git status` output

**After Pushing:**
1. ✅ Check GitHub repository page
2. ✅ Verify branch is `main`
3. ✅ Confirm all files uploaded
4. ✅ Check commit history

---

## 🆘 Troubleshooting

### "Remote already exists"
```bash
git remote set-url origin https://github.com/btdugm-stack/ugm-intelligence-space.git
```

### "Authentication failed"
- HTTPS: Update GitHub token (may have expired)
- SSH: Run `ssh -T git@github.com` to verify

### "Git not found"
- Install Git from https://git-scm.com
- Restart terminal after install

### "Permission denied (publickey)"
- SSH key not configured in GitHub
- Check: https://github.com/settings/keys

---

## 📞 Support

- **GitHub Docs**: https://docs.github.com
- **Git Docs**: https://git-scm.com/doc
- **Project Issues**: Create issue on GitHub repository
- **Team Help**: Contact UGM Development Team

---

## ✨ Next Steps After Successful Deployment

1. **Repository Settings** (GitHub → Settings):
   - Add description
   - Add topics: `ugm`, `dashboard`, `php`, `poc`
   - Enable "Discussions" if needed

2. **Branching Strategy**:
   ```bash
   git checkout -b develop
   git push -u origin develop
   ```

3. **Team Access**:
   - Add collaborators from btdugm-stack org
   - Set branch protection rules

4. **Documentation**:
   - Update README with deployment info
   - Add contributing guidelines
   - Document API endpoints

---

**Status**: ✅ Ready for deployment  
**Repository**: https://github.com/btdugm-stack/ugm-intelligence-space.git  
**Last Updated**: July 10, 2026
