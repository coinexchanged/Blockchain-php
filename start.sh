#! /bin/sh
# 开机自启动 必须启动redis和es

php artisan config:cache

cd ./public/vendor/webmsgsender && php start.php start -d

php artisan schedule:run
php artisan queue:work
python3 ./python/main.py
php artisan websocket:client start