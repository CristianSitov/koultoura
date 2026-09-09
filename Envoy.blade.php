@servers(['localhost' => '127.0.0.1'])

@task('deploy', ['on' => ['localhost']])
    @if ($branch)
        git fetch
        git reset --hard origin/{{ $branch }}
    @endif

    COMPOSER_ALLOW_SUPERUSER=1 /usr/bin/php8.2 /usr/bin/composer install -o -vv
    /usr/bin/php8.2 artisan migrate --force
    /usr/bin/php8.2 artisan optimize:clear
    /usr/bin/php8.2 artisan ziggy:generate
    npm install
    npm run build

    # The build leaves the previous one's files in place on purpose — see the
    # note in vite.config.mjs — so anyone mid-session keeps finding the chunks
    # their page names. This clears out what a week of deploys has orphaned.
    /usr/bin/php8.2 artisan build:prune --days=7

    chown -R www-data:www-data /var/www/whyculturematters.eu
@endtask
