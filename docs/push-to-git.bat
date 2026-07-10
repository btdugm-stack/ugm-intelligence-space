@echo off
REM 🚀 Push Script - Pilih Platform untuk Push Git (Windows)

cls
echo.
echo ════════════════════════════════════════════════════════
echo 🚀 UGM Intelligence Space - Git Push Script
echo ════════════════════════════════════════════════════════
echo.
echo Pilih platform untuk push repository:
echo.
echo 1. GitHub (https://github.com)
echo 2. GitLab (https://gitlab.com)
echo 3. Gitea (Self-hosted)
echo 4. Batalkan
echo.

set /p choice="Pilih nomor (1-4): "

if "%choice%"=="1" goto github
if "%choice%"=="2" goto gitlab
if "%choice%"=="3" goto gitea
if "%choice%"=="4" goto cancel
goto invalid

:github
cls
echo.
echo 📌 Setup GitHub Push
echo.
echo Langkah-langkah:
echo 1. Go ke https://github.com/new
echo 2. Create repository 'ugm-intelligence-space'
echo 3. Copy HTTPS URL dari repository
echo.
set /p github_url="Masukkan GitHub repository URL: "

git remote add origin %github_url%
git branch -M main
git push -u origin main

echo.
echo ✅ Push ke GitHub selesai!
pause
goto end

:gitlab
cls
echo.
echo 📌 Setup GitLab Push
echo.
echo Langkah-langkah:
echo 1. Go ke https://gitlab.com/projects/new
echo 2. Create project 'ugm-intelligence-space'
echo 3. Copy HTTPS URL dari project
echo.
set /p gitlab_url="Masukkan GitLab project URL: "

git remote add origin %gitlab_url%
git branch -M main
git push -u origin main

echo.
echo ✅ Push ke GitLab selesai!
pause
goto end

:gitea
cls
echo.
echo 📌 Setup Gitea (Self-hosted) Push
echo.
set /p gitea_url="Masukkan Gitea repository URL: "
set /p gitea_user="Masukkan Gitea username: "
set /p gitea_pass="Masukkan Gitea token/password: "

git remote add origin %gitea_url%
git branch -M main
git push -u origin main

echo.
echo ✅ Push ke Gitea selesai!
pause
goto end

:cancel
cls
echo.
echo ❌ Push dibatalkan
echo.
pause
goto end

:invalid
cls
echo.
echo ❌ Pilihan tidak valid
echo.
pause
goto end

:end
echo.
echo 🎉 Selesai!
echo.
echo Perintah berikutnya untuk update:
echo git add -A
echo git commit -m "feat: deskripsi perubahan"
echo git push
echo.
