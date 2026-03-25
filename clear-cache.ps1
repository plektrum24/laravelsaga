# SAGA POS - Clear Cache (PowerShell)
# Use this when PHP is not available

Write-Host "========================================"
Write-Host " SAGA POS - Manual Cache Clear"
Write-Host "========================================"
Write-Host ""

$projectRoot = "d:\Project App\laravelsaga"

# Clear Route Cache
Write-Host "[1/4] Clearing route cache..."
$routeCacheFiles = @(
    "$projectRoot\bootstrap\cache\routes-v7.php",
    "$projectRoot\bootstrap\cache\services.php",
    "$projectRoot\storage\framework\cache\laravel_explorer_routes.php"
)
foreach ($file in $routeCacheFiles) {
    if (Test-Path $file) {
        Remove-Item $file -Force
        Write-Host "  Deleted: $file"
    }
}

# Clear View Cache
Write-Host ""
Write-Host "[2/4] Clearing view cache..."
$viewFiles = Get-ChildItem "$projectRoot\storage\framework\views" -File -ErrorAction SilentlyContinue
foreach ($file in $viewFiles) {
    Remove-Item $file.FullName -Force
}
Write-Host "  Cleared $($viewFiles.Count) compiled view files"

# Clear Config Cache
Write-Host ""
Write-Host "[3/4] Clearing config cache..."
$configCacheFiles = @(
    "$projectRoot\bootstrap\cache\config-v7.php",
    "$projectRoot\bootstrap\cache\events-v7.php"
)
foreach ($file in $configCacheFiles) {
    if (Test-Path $file) {
        Remove-Item $file -Force
        Write-Host "  Deleted: $file"
    }
}

# Clear Application Cache
Write-Host ""
Write-Host "[4/4] Clearing application cache..."
$cacheFiles = Get-ChildItem "$projectRoot\storage\framework\cache\data" -File -ErrorAction SilentlyContinue
foreach ($file in $cacheFiles) {
    Remove-Item $file.FullName -Force
}
Write-Host "  Cleared $($cacheFiles.Count) cache files"

Write-Host ""
Write-Host "========================================"
Write-Host " Cache cleared successfully!"
Write-Host "========================================"
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Install PHP or add to PATH"
Write-Host "2. Run: php artisan serve"
Write-Host ""
Write-Host "Or use the compiled views directly (they will auto-regenerate)"
Write-Host ""
