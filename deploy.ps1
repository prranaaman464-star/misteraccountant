# Deploy script - GitHub se live production par deploy
# Repo: https://github.com/prranaaman464-star/misteraccountant.git
# Usage: .\deploy.ps1

param(
    [switch]$SkipBuild,
    [switch]$SkipMigration
)

# ============ CONFIG - Apna server path yahan set karo ============
$SSH_HOST = "91.108.107.218"
$SSH_PORT = "65002"
$SSH_USER = "u199585304"
# Server par project ka full path - hosting panel se check karo
# Hostinger/cPanel: /home/u199585304/domains/yourdomain.com/public_html
$REMOTE_PATH = "/home/u199585304/domains/misteraccountant.com/public_html"
# =================================================================

$SSH_TARGET = "${SSH_USER}@${SSH_HOST}"
$SSH_OPTS = "-p $SSH_PORT -o StrictHostKeyChecking=no"

Write-Host "`n=== MisterAccountant Deploy ===" -ForegroundColor Cyan
Write-Host "Target: $SSH_TARGET`n" -ForegroundColor Gray

# Step 1: Git push (optional - agar local changes hain to pehle push karo)
Write-Host "[1/6] Checking git status..." -ForegroundColor Yellow
$gitStatus = git status --short
if ($gitStatus) {
    Write-Host "Uncommitted changes found. Push to main first? (y/n): " -NoNewline
    $response = Read-Host
    if ($response -eq 'y') {
        git add -A
        git commit -m "Deploy: $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
        git push origin main
    } else {
        Write-Host "Skipping push. Make sure main is updated on remote." -ForegroundColor Yellow
    }
} else {
    git push origin main 2>$null
    if ($LASTEXITCODE -ne 0) { Write-Host "Git push skipped (nothing to push or no remote)" -ForegroundColor Gray }
}

# Step 2: Local build (live ki build replace karne ke liye)
if (-not $SkipBuild) {
    Write-Host "`n[2/6] Building frontend locally (npm run build)..." -ForegroundColor Yellow
    npm run build
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Build failed! Aborting deploy." -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "`n[2/6] Skipping local build (--SkipBuild)" -ForegroundColor Gray
}

# Step 3: SSH - Server par git pull, composer, etc.
Write-Host "`n[3/6] Connecting to server and pulling code..." -ForegroundColor Yellow
$remoteCommands = @"
cd $REMOTE_PATH && \
git fetch origin && \
git reset --hard origin/main && \
git pull origin main
"@

ssh $SSH_OPTS $SSH_TARGET $remoteCommands
if ($LASTEXITCODE -ne 0) {
    Write-Host "Git pull failed! Check REMOTE_PATH in script." -ForegroundColor Red
    exit 1
}

# Step 4: Composer install on server
Write-Host "`n[4/6] Running composer install on server..." -ForegroundColor Yellow
$composerCmd = "cd $REMOTE_PATH && composer install --no-dev --optimize-autoloader --no-interaction"
ssh $SSH_OPTS $SSH_TARGET $composerCmd

# Step 5: Upload local build to server (replace live build)
Write-Host "`n[5/6] Uploading local build to server..." -ForegroundColor Yellow
$buildPath = Join-Path $PSScriptRoot "public\build"
if (Test-Path $buildPath) {
    # Purani build hatao, phir nayi upload karo
    ssh $SSH_OPTS $SSH_TARGET "rm -rf ${REMOTE_PATH}/public/build"
    scp -P $SSH_PORT -r -o StrictHostKeyChecking=no "$buildPath" "${SSH_TARGET}:${REMOTE_PATH}/public/"
} else {
    Write-Host "Build folder not found. Running npm run build on server instead..." -ForegroundColor Yellow
    ssh $SSH_OPTS $SSH_TARGET "cd $REMOTE_PATH && npm ci && npm run build"
}

# Step 6: Laravel optimize & migrate
Write-Host "`n[6/6] Running Laravel optimizations..." -ForegroundColor Yellow
$artisanCmds = "cd $REMOTE_PATH && php artisan config:cache && php artisan route:cache && php artisan view:cache"
if (-not $SkipMigration) {
    $artisanCmds = "cd $REMOTE_PATH && php artisan migrate --force && " + $artisanCmds
}
ssh $SSH_OPTS $SSH_TARGET $artisanCmds

Write-Host "`n=== Deploy Complete ===" -ForegroundColor Green
Write-Host "Password enter karne par prompt aayega agar SSH key setup nahi hai." -ForegroundColor Gray
