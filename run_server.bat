@echo off
cd /d "%~dp0"
php artisan serve --port=8000 --host=127.0.0.1
