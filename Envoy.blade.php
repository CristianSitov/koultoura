@servers(['localhost' => '127.0.0.1'])

@task('deploy', ['on' => ['localhost']])
    /usr/bin/php8.0 /usr/bin/composer install -o -vv
    /usr/bin/php8.0 artisan migrate --force
    /usr/bin/php8.0 artisan optimize:clear
    /usr/bin/php8.0 artisan optimize
    /usr/bin/php8.0 artisan ziggy:generate
    npm install
    npm run build
    chown -R www-data:www-data /var/www/prinbanat.ngo
@endtask
