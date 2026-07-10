#!/bin/bash
# 🚀 Push Script - Pilih Platform untuk Push Git

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}🚀 UGM Intelligence Space - Git Push Script${NC}"
echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
echo ""

echo "Pilih platform untuk push repository:"
echo ""
echo "1. GitHub (https://github.com)"
echo "2. GitLab (https://gitlab.com)"
echo "3. Gitea (Self-hosted)"
echo "4. Batalkan"
echo ""
read -p "Pilih nomor (1-4): " choice

case $choice in
  1)
    echo -e "${YELLOW}📌 Setup GitHub Push${NC}"
    echo ""
    echo "Langkah-langkah:"
    echo "1. Go ke https://github.com/new"
    echo "2. Create repository 'ugm-intelligence-space'"
    echo "3. Copy HTTPS URL dari repository"
    echo ""
    read -p "Masukkan GitHub repository URL: " github_url
    
    git remote add origin "$github_url"
    git branch -M main
    git push -u origin main
    
    echo -e "${GREEN}✅ Push ke GitHub selesai!${NC}"
    ;;
    
  2)
    echo -e "${YELLOW}📌 Setup GitLab Push${NC}"
    echo ""
    echo "Langkah-langkah:"
    echo "1. Go ke https://gitlab.com/projects/new"
    echo "2. Create project 'ugm-intelligence-space'"
    echo "3. Copy HTTPS URL dari project"
    echo ""
    read -p "Masukkan GitLab project URL: " gitlab_url
    
    git remote add origin "$gitlab_url"
    git branch -M main
    git push -u origin main
    
    echo -e "${GREEN}✅ Push ke GitLab selesai!${NC}"
    ;;
    
  3)
    echo -e "${YELLOW}📌 Setup Gitea (Self-hosted) Push${NC}"
    echo ""
    read -p "Masukkan Gitea repository URL: " gitea_url
    read -p "Masukkan Gitea username: " gitea_user
    read -p "Masukkan Gitea token/password: " gitea_pass
    
    git remote add origin "$gitea_url"
    git branch -M main
    git push -u origin main
    
    echo -e "${GREEN}✅ Push ke Gitea selesai!${NC}"
    ;;
    
  4)
    echo -e "${YELLOW}❌ Push dibatalkan${NC}"
    exit 0
    ;;
    
  *)
    echo -e "${YELLOW}❌ Pilihan tidak valid${NC}"
    exit 1
    ;;
esac

echo ""
echo -e "${GREEN}🎉 Repository berhasil di-push!${NC}"
echo ""
echo "Perintah berikutnya untuk update:"
echo -e "${BLUE}git add -A${NC}"
echo -e "${BLUE}git commit -m 'feat: deskripsi perubahan'${NC}"
echo -e "${BLUE}git push${NC}"
