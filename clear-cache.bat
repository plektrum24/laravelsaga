@echo off
echo ========================================
echo  SAGA POS - Clear Cache Script
echo ========================================
echo.

cd /d "%~dp0"

echo [1/4] Clearing route cache...
php artisan route:clear

echo.
echo [2/4] Clearing view cache...
php artisan view:clear

echo.
echo [3/4] Clearing config cache...
php artisan config:clear

echo.
echo [4/4] Optimizing application...
php artisan optimize:clear

echo.
echo ========================================
echo  Cache cleared successfully!
echo ========================================
echo.
echo You can now test the application:
echo   php artisan serve
echo.
pause
