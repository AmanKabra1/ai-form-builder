# AI Form Builder - Complete Setup & Start Script
# Run once: powershell -ExecutionPolicy Bypass -File start.ps1

$projectPath = "C:\Users\Lenovo\Desktop\Personal\BackendProject\form-builder"
Set-Location $projectPath

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "AI Form Builder - Setup & Start" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if composer dependencies exist
if (-not (Test-Path "$projectPath\vendor")) {
    Write-Host "[1/6] Installing Composer dependencies..." -ForegroundColor Yellow
    & "C:\php82\composer.bat" install --no-interaction
}

# Check if npm dependencies exist
if (-not (Test-Path "$projectPath\node_modules")) {
    Write-Host "[2/6] Installing NPM dependencies..." -ForegroundColor Yellow
    npm install
}

# Check if .env exists
if (-not (Test-Path "$projectPath\.env")) {
    Write-Host "[3/6] Setting up .env file..." -ForegroundColor Yellow
    Copy-Item "$projectPath\.env.example" "$projectPath\.env"
    php artisan key:generate
} else {
    Write-Host "[3/6] .env file already exists" -ForegroundColor Green
}

# Check if database exists, if not migrate and seed
if (-not (Test-Path "$projectPath\database\database.sqlite")) {
    Write-Host "[4/6] Running migrations..." -ForegroundColor Yellow
    php artisan migrate

    Write-Host "[5/6] Seeding demo data..." -ForegroundColor Yellow
    php artisan db:seed
}

Write-Host "[6/6] Starting services..." -ForegroundColor Yellow
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "[DONE] Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Start three services in parallel
$job1 = Start-Job -ScriptBlock {
    Set-Location "C:\Users\Lenovo\Desktop\Personal\BackendProject\form-builder"
    Write-Host "Starting Laravel server..." -ForegroundColor Cyan
    php artisan serve
}

$job2 = Start-Job -ScriptBlock {
    Set-Location "C:\Users\Lenovo\Desktop\Personal\BackendProject\form-builder"
    Write-Host "Starting asset builder..." -ForegroundColor Cyan
    npm run dev
}

$job3 = Start-Job -ScriptBlock {
    Set-Location "C:\Users\Lenovo\Desktop\Personal\BackendProject\form-builder"
    Write-Host "Starting queue worker..." -ForegroundColor Cyan
    php artisan queue:work
}

Write-Host ""
Write-Host "[OK] All services started!" -ForegroundColor Green
Write-Host ""
Write-Host "[INFO] Application URL: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "[INFO] Login Credentials:" -ForegroundColor Cyan
Write-Host "       Email: demo@example.com" -ForegroundColor White
Write-Host "       Password: password" -ForegroundColor White
Write-Host ""
Write-Host "[NOTE] To stop all services, press Ctrl+C" -ForegroundColor Yellow
Write-Host ""

# Keep running until user stops
Wait-Job -Job $job1, $job2, $job3
