# UGM Intelligence Space - Git Deployment Guide

## Repository Target
- **URL**: https://github.com/btdugm-stack/ugm-intelligence-space.git
- **Organization**: btdugm-stack
- **Project**: ugm-intelligence-space

## Prerequisites
1. **Git installed** - Verifikasi dengan `git --version`
2. **GitHub account** - Dengan akses ke repository
3. **SSH keys configured** (recommended) atau credentials siap
4. **Project at**: `c:\laragon\www\ugm-intelligence-space-poc`

## Metode Deployment

### METODE 1: Automated PowerShell Script (RECOMMENDED)

#### Step 1: Jalankan Setup Script
```powershell
cd "c:\laragon\www\ugm-intelligence-space-poc"
.\setup-git.ps1
```

#### Step 2: Review Output
- Verifikasi semua file tercakup
- Periksa "Staged files summary"
- Pastikan tidak ada file sensitif

#### Step 3: Push ke GitHub (Manual untuk keamanan)
```powershell
git push -u origin main
```

#### Step 3 (Alternatif): Push Otomatis
```powershell
.\setup-git.ps1 -Push
```

---

### METODE 2: Batch Script (Windows)

#### Step 1: Jalankan Setup
```batch
setup-git.bat
```

#### Step 2: Follow on-screen instructions
Script akan:
- Initialize Git repository
- Configure user
- Add remote origin
- Stage all files
- Show status

#### Step 3: Manual push
```batch
git push -u origin main
```

---

### METODE 3: Manual Commands (Full Control)

#### Step 1: Initialize Repository
```bash
cd c:\laragon\www\ugm-intelligence-space-poc
git init
```

#### Step 2: Configure Git
```bash
git config user.name "UGM Development Team"
git config user.email "dev@ugm.ac.id"
```

#### Step 3: Add Remote
```bash
git remote add origin https://github.com/btdugm-stack/ugm-intelligence-space.git
```

#### Step 4: Stage Files
```bash
git add .
```

#### Step 5: Review Changes
```bash
git status
git diff --cached --name-status
```

#### Step 6: Create Initial Commit
```bash
git commit -m "Initial commit: UGM Intelligence Space PoC"
```

#### Step 7: Setup Main Branch
```bash
git branch -M main
```

#### Step 8: Push to GitHub
```bash
# First push (requires authentication)
git push -u origin main

# Subsequent pushes
git push origin main
```

---

## Authentication Methods

### Method A: HTTPS with Credentials
1. When prompted, enter GitHub username
2. For password, use Personal Access Token (PAT):
   - Go to GitHub → Settings → Developer settings → Personal access tokens
   - Generate new token with `repo` scope
   - Use token as password in Git

### Method B: SSH (Recommended for automation)
1. Generate SSH key (if not exists):
```bash
ssh-keygen -t ed25519 -C "dev@ugm.ac.id"
```

2. Add to GitHub:
   - Copy public key from `~/.ssh/id_ed25519.pub`
   - Add to GitHub → Settings → SSH and GPG keys

3. Update remote URL:
```bash
git remote set-url origin git@github.com:btdugm-stack/ugm-intelligence-space.git
```

---

## Verification Checklist

After successful push, verify:

- [ ] Repository exists on GitHub
- [ ] All files are present
- [ ] `.env` files are NOT pushed (check .gitignore)
- [ ] Commit message is correct
- [ ] Branch is `main`
- [ ] `.github/copilot-instructions.md` is present
- [ ] `data/dashboards.json` is in repository
- [ ] No sensitive files exposed

```bash
# View remote tracking
git remote -v

# View commit history
git log --oneline

# Verify branch
git branch -v

# Check what's on remote
git ls-remote origin
```

---

## Troubleshooting

### Error: "Repository already exists"
```bash
# Solution: If .git already exists but needs reset
rm -r .git
git init
# Then follow normal steps
```

### Error: "Authentication failed"
```bash
# For HTTPS - Update credentials
git config --global credential.helper store
git pull  # Will prompt for credentials again

# For SSH - Check key
ssh -T git@github.com
```

### Error: "Remote origin already exists"
```bash
# Update existing remote
git remote set-url origin https://github.com/btdugm-stack/ugm-intelligence-space.git
```

### Unwanted files were pushed
```bash
# Remove from history (dangerous, use with care)
git rm --cached filename
git commit --amend
git push -f origin main
```

---

## After Successful Deployment

1. **Configure Repository Settings** on GitHub:
   - Add description
   - Add topics: `ugm`, `dashboard`, `intelligence`, `php`, `poc`
   - Configure branch protection for `main`

2. **Setup GitHub Actions** (optional):
   - Create workflow for auto-deployment
   - Setup CI/CD pipeline

3. **Document Repository**:
   - README.md already exists
   - Add contributing guidelines
   - Add code of conduct

4. **Team Access**:
   - Add collaborators from btdugm-stack organization
   - Set appropriate permissions

---

## Quick Reference Commands

```bash
# Status
git status
git log --oneline

# Add changes
git add .
git add filename

# Commit
git commit -m "message"
git commit --amend  # Modify last commit

# Push/Pull
git push origin main
git pull origin main

# Branch management
git branch              # List branches
git branch -M main      # Rename to main
git checkout main       # Switch branch

# Remote
git remote -v                              # List remotes
git remote add origin https://...          # Add remote
git remote set-url origin https://...      # Update remote

# Undo changes
git restore filename                       # Undo unstaged changes
git restore --staged filename              # Unstage
git reset HEAD~1                           # Undo last commit
```

---

## Security Notes

⚠️ **Important**:
- Never commit `.env` files - they contain secrets
- Never commit `vendor/` directory
- Never commit database passwords
- Check `.gitignore` before every push
- Use `git diff --cached` to review before commit

✓ **Good Practice**:
- Create `.env.example` with dummy values
- Document required environment variables
- Use SSH keys instead of HTTPS tokens
- Enable 2FA on GitHub account
- Review commits before push

---

## Support & Help

If issues occur:
1. Check Git status: `git status`
2. View logs: `git log --oneline`
3. Check remote: `git remote -v`
4. See diffs: `git diff`
5. Force sync: `git fetch origin` then `git reset --hard origin/main`

For GitHub help: https://docs.github.com
For Git help: https://git-scm.com/doc
