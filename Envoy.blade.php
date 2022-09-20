@servers(['localhost' => '127.0.0.1'])

@task('deploy', ['on' => ['localhost']])
    /usr/bin/php8.0 /usr/bin/composer install -o -vv
    /usr/bin/php8.0 artisan migrate --force
    /usr/bin/php8.0 artisan cache:clear
    /usr/bin/php8.0 artisan view:clear
    /usr/bin/php8.0 artisan route:clear
    /usr/bin/php8.0 artisan config:clear
    /usr/bin/php8.0 artisan ziggy:generate
    npm install
    npm run build
    chown -R www-data:www-data /var/www/prinbanat.ngo
@endtask
