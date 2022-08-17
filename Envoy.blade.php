@servers(['localhost' => '127.0.0.1'])

@task('deploy', ['on' => ['localhost']])
    git fetch
    git reset --hard origin/main
    php artisan migrate --force
    npm run build
@endtask
