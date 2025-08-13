@echo off
echo Limpiando caches de Laravel...

php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize:clear

echo.
echo ✅ Limpieza completada.
pause
