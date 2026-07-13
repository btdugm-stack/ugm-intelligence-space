@echo off
REM ============================================
REM Git Deployment Setup for UGM Intelligence Space
REM Target: https://github.com/btdugm-stack/ugm-intelligence-space.git
REM ============================================

echo.
echo ========================================
echo UGM Intelligence Space - Git Setup
echo ========================================
echo.

REM Check if Git is installed
git --version
if errorlevel 1 (
    echo ERROR: Git is not installed or not in PATH
    pause
    exit /b 1
)

REM Change to project directory
cd /d "c:\laragon\www\ugm-intelligence-space-poc"
if errorlevel 1 (
    echo ERROR: Cannot change to project directory
    pause
    exit /b 1
)

echo.
echo Current directory: %cd%
echo.

REM Check if already a Git repository
if exist ".git" (
    echo Git repository already exists
    echo.
    echo Current Git status:
    git status
    echo.
) else (
    echo Initializing new Git repository...
    git init
    if errorlevel 1 (
        echo ERROR: Failed to initialize Git repository
        pause
        exit /b 1
    )
    echo Git repository initialized successfully
    echo.
)

REM Configure Git user (if not already configured globally)
echo Configuring Git user...
git config user.name "UGM Development Team" 2>nul
git config user.email "dev@ugm.ac.id" 2>nul
echo Git user configured
echo.

REM Check if remote origin exists
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo Adding remote origin...
    git remote add origin https://github.com/btdugm-stack/ugm-intelligence-space.git
    if errorlevel 1 (
        echo ERROR: Failed to add remote origin
        pause
        exit /b 1
    )
    echo Remote origin added: https://github.com/btdugm-stack/ugm-intelligence-space.git
) else (
    echo Remote origin already exists
    echo Current remote:
    git remote -v
)
echo.

REM Check for .gitignore
if not exist ".gitignore" (
    echo Creating .gitignore...
    (
        echo # Dependencies
        echo vendor/
        echo node_modules/
        echo
        echo # Environment
        echo .env
        echo .env.local
        echo .env.*.local
        echo
        echo # IDE
        echo .vscode/
        echo .idea/
        echo *.swp
        echo *.swo
        echo *~
        echo .DS_Store
        echo
        echo # Logs
        echo logs/*
        echo !logs/.gitkeep
        echo
        echo # Cache
        echo .cache/
        echo tmp/
        echo
        echo # Build
        echo dist/
        echo build/
    ) > .gitignore
    echo .gitignore created
    echo.
) else (
    echo .gitignore already exists
    echo.
)

REM Add all files to staging
echo.
echo Adding all files to Git staging area...
git add .
echo Files added to staging
echo.

REM Check what's staged
echo.
echo Staged files:
git diff --cached --name-status
echo.

REM Show Git status
echo.
echo Current Git status:
git status
echo.

REM Display next steps
echo.
echo ========================================
echo SETUP COMPLETE - NEXT STEPS
echo ========================================
echo.
echo 1. Review the staged files above
echo.
echo 2. Commit the changes:
echo    git commit -m "Initial commit: UGM Intelligence Space PoC"
echo.
echo 3. (Optional) Verify remote configuration:
echo    git remote -v
echo.
echo 4. Create and switch to main branch:
echo    git branch -M main
echo.
echo 5. Push to GitHub (first time):
echo    git push -u origin main
echo.
echo 6. For subsequent pushes:
echo    git push origin main
echo.
echo ========================================
echo.

pause
