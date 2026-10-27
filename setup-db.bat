@echo off
cd /d D:\LSP_P3\skenario\login
echo === Migrasi database ===
php artisan migrate --force
echo === Seeder akun admin ===
php artisan db:seed --force
echo === Selesai. Jalankan: php artisan serve ===
pause
