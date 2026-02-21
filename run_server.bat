@echo off
cd /d "%~dp0"
"C:\xampp\php\php.exe" artisan serve --port=8000 --host=127.0.0.1
