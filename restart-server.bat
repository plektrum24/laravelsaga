@echo off
echo ========================================
echo  SAGA POS - Restart Server
echo ========================================
echo.

cd /d "%~dp0"

echo Stopping any running PHP processes...
taskkill /F /IM php.exe 2>nul

echo.
echo Clearing cache...
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan view:clear
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan optimize:clear

echo.
echo ========================================
echo  Starting Laravel Development Server
echo ========================================
echo.
echo Server will start at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.

C:\xampp\php\php.exe artisan serve
