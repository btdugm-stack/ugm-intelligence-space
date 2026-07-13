#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Git Deployment Script for UGM Intelligence Space
.DESCRIPTION
    Prepares project for Git deployment to GitHub
.PARAMETER Repository
    GitHub repository URL (default: https://github.com/btdugm-stack/ugm-intelligence-space.git)
.PARAMETER Message
    Commit message (default: "Initial commit: UGM Intelligence Space PoC")
.PARAMETER Push
    Whether to push to remote (default: $false for safety)
#>

param(
    [string]$Repository = "https://github.com/btdugm-stack/ugm-intelligence-space.git",
    [string]$Message = "Initial commit: UGM Intelligence Space PoC",
    [switch]$Push = $false
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

# Colors for output
$colors = @{
    Success = 'Green'
    Error = 'Red'
    Warning = 'Yellow'
    Info = 'Cyan'
}

function Write-ColorOutput($Message, $Color = 'White') {
    Write-Host $Message -ForegroundColor $Color
}

function Write-Section($Title) {
    Write-Host "`n" -NoNewline
    Write-Host "========================================" -ForegroundColor 'Cyan'
    Write-Host $Title -ForegroundColor 'Cyan'
    Write-Host "========================================" -ForegroundColor 'Cyan'
    Write-Host ""
}

# Main execution
try {
    Write-Section "UGM Intelligence Space - Git Deployment"

    # 1. Check Git installation
    Write-ColorOutput "1. Checking Git installation..." -Color $colors.Info
    $gitVersion = git --version
    if ($LASTEXITCODE -ne 0) {
        throw "Git is not installed or not in PATH"
    }
    Write-ColorOutput "   ✓ $gitVersion" -Color $colors.Success

    # 2. Navigate to project directory
    Write-ColorOutput "`n2. Navigating to project directory..." -Color $colors.Info
    $projectPath = "c:\laragon\www\ugm-intelligence-space-poc"
    if (-not (Test-Path $projectPath)) {
        throw "Project directory not found: $projectPath"
    }
    Set-Location $projectPath
    Write-ColorOutput "   ✓ Location: $(Get-Location)" -Color $colors.Success

    # 3. Initialize Git repository if needed
    Write-ColorOutput "`n3. Checking Git repository..." -Color $colors.Info
    if (-not (Test-Path ".git")) {
        Write-ColorOutput "   - Initializing new Git repository..." -Color $colors.Warning
        git init
        Write-ColorOutput "   ✓ Git repository initialized" -Color $colors.Success
    } else {
        Write-ColorOutput "   ✓ Git repository exists" -Color $colors.Success
    }

    # 4. Configure Git user
    Write-ColorOutput "`n4. Configuring Git user..." -Color $colors.Info
    git config user.name "UGM Development Team" 2>$null
    git config user.email "dev@ugm.ac.id" 2>$null
    Write-ColorOutput "   ✓ Git user configured" -Color $colors.Success

    # 5. Setup remote origin
    Write-ColorOutput "`n5. Setting up remote origin..." -Color $colors.Info
    $remoteUrl = git remote get-url origin 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-ColorOutput "   ✓ Remote already exists: $remoteUrl" -Color $colors.Success
        if ($remoteUrl -ne $Repository) {
            Write-ColorOutput "   - Updating remote URL..." -Color $colors.Warning
            git remote set-url origin $Repository
            Write-ColorOutput "   ✓ Remote URL updated" -Color $colors.Success
        }
    } else {
        Write-ColorOutput "   - Adding new remote..." -Color $colors.Warning
        git remote add origin $Repository
        Write-ColorOutput "   ✓ Remote added: $Repository" -Color $colors.Success
    }

    # 6. Check .gitignore
    Write-ColorOutput "`n6. Verifying .gitignore..." -Color $colors.Info
    if (Test-Path ".gitignore") {
        Write-ColorOutput "   ✓ .gitignore exists" -Color $colors.Success
    } else {
        Write-ColorOutput "   ⚠ .gitignore not found" -Color $colors.Warning
    }

    # 7. Stage all files
    Write-ColorOutput "`n7. Staging files..." -Color $colors.Info
    git add .
    Write-ColorOutput "   ✓ Files staged" -Color $colors.Success

    # 8. Show staged files
    Write-ColorOutput "`n8. Staged files summary:" -Color $colors.Info
    $stagedFiles = git diff --cached --name-status
    if ($stagedFiles) {
        $stagedFiles | ForEach-Object {
            Write-Host "   $_"
        }
        $fileCount = @($stagedFiles).Count
        Write-ColorOutput "   Total: $fileCount file(s)" -Color $colors.Success
    } else {
        Write-ColorOutput "   (No changes to stage)" -Color $colors.Warning
    }

    # 9. Show current status
    Write-ColorOutput "`n9. Current Git status:" -Color $colors.Info
    git status --short | ForEach-Object {
        Write-Host "   $_"
    }

    # 10. Create commit
    Write-ColorOutput "`n10. Creating commit..." -Color $colors.Info
    Write-ColorOutput "    Message: `"$Message`"" -Color $colors.Warning
    git commit -m $Message
    Write-ColorOutput "   ✓ Commit created" -Color $colors.Success

    # 11. Setup main branch
    Write-ColorOutput "`n11. Setting up main branch..." -Color $colors.Info
    $currentBranch = git rev-parse --abbrev-ref HEAD
    if ($currentBranch -ne "main") {
        git branch -M main
        Write-ColorOutput "   ✓ Branch renamed to 'main'" -Color $colors.Success
    } else {
        Write-ColorOutput "   ✓ Already on 'main' branch" -Color $colors.Success
    }

    # 12. Show final status
    Write-Section "Deployment Summary"
    Write-ColorOutput "Repository: $Repository" -Color $colors.Info
    Write-ColorOutput "Branch: main" -Color $colors.Info
    Write-ColorOutput "Commit: $Message" -Color $colors.Info

    # 13. Show next steps
    Write-Section "Next Steps"
    
    Write-ColorOutput "To push to GitHub, run:" -Color $colors.Warning
    Write-Host "  git push -u origin main"
    Write-Host ""

    if ($Push) {
        Write-ColorOutput "Pushing to repository..." -Color $colors.Warning
        git push -u origin main
        Write-ColorOutput "`n✓ Successfully pushed to GitHub!" -Color $colors.Success
    } else {
        Write-ColorOutput "Note: Use -Push switch to automatically push:" -Color $colors.Warning
        Write-Host "  .\setup-git.ps1 -Push"
        Write-Host ""
    }

    Write-ColorOutput "`n✓ Setup complete!" -Color $colors.Success

} catch {
    Write-ColorOutput "`n✗ Error: $_" -Color $colors.Error
    exit 1
}
