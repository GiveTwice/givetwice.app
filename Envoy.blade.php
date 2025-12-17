@setup
    $repository = 'git@github.com:GiveTwice/givetwice.app.git';
    $branch = $branch ?? 'main';

    $baseDir = '/home/givetwice/givetwice.app';
    $releasesDir = "{$baseDir}/releases";
    $persistentDir = "{$baseDir}/persistent";
    $currentDir = "{$baseDir}/current";
    $newReleaseName = date('Ymd-His');
    $newReleaseDir = "{$releasesDir}/{$newReleaseName}";

    function logMessage($message) {
        return "echo '\033[32m" . $message . "\033[0m';\n";
    }
@endsetup

@servers(['local' => '127.0.0.1', 'remote' => '-A givetwice@srv01.givetwice.app'])

@story('deploy')
    startDeployment
    cloneRepository
    linkEnvironment
    installComposerDependencies
    installYarnDependencies
    buildAssets
    updateSymlinks
    migrateDatabase
    optimizeInstallation
    blessNewRelease
    restartServices
    cleanOldReleases
    finishDeploy
@endstory

@story('deploy-code')
    deployOnlyCode
@endstory

@task('startDeployment', ['on' => 'local'])
    {{ logMessage("🏃 Starting deployment...") }}
    git checkout {{ $branch }}
    git pull origin {{ $branch }}
@endtask

@task('cloneRepository', ['on' => 'remote'])
    {{ logMessage("🌀 Cloning repository...") }}

    # Create directory structure if it doesn't exist
    [ -d {{ $releasesDir }} ] || mkdir -p {{ $releasesDir }};
    [ -d {{ $persistentDir }} ] || mkdir -p {{ $persistentDir }};
    [ -d {{ $persistentDir }}/storage ] || mkdir -p {{ $persistentDir }}/storage;
    [ -d {{ $persistentDir }}/storage/app ] || mkdir -p {{ $persistentDir }}/storage/app;
    [ -d {{ $persistentDir }}/storage/app/public ] || mkdir -p {{ $persistentDir }}/storage/app/public;
    [ -d {{ $persistentDir }}/storage/framework ] || mkdir -p {{ $persistentDir }}/storage/framework;
    [ -d {{ $persistentDir }}/storage/framework/cache ] || mkdir -p {{ $persistentDir }}/storage/framework/cache;
    [ -d {{ $persistentDir }}/storage/framework/cache/data ] || mkdir -p {{ $persistentDir }}/storage/framework/cache/data;
    [ -d {{ $persistentDir }}/storage/framework/sessions ] || mkdir -p {{ $persistentDir }}/storage/framework/sessions;
    [ -d {{ $persistentDir }}/storage/framework/views ] || mkdir -p {{ $persistentDir }}/storage/framework/views;
    [ -d {{ $persistentDir }}/storage/logs ] || mkdir -p {{ $persistentDir }}/storage/logs;

    cd {{ $releasesDir }};

    # Clone the repository
    git clone --depth 1 {{ $repository }} --branch {{ $branch }} {{ $newReleaseName }}

    # Configure sparse checkout to exclude storage and build directories
    cd {{ $newReleaseDir }}
    git config core.sparsecheckout true
    echo "*" > .git/info/sparse-checkout
    echo "!storage" >> .git/info/sparse-checkout
    echo "!public/build" >> .git/info/sparse-checkout
    git read-tree -mu HEAD

    # Mark release
    echo "{{ $newReleaseName }}" > public/release-name.txt
@endtask

@task('linkEnvironment', ['on' => 'remote'])
    {{ logMessage("🔐 Linking environment file...") }}

    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/.env .env;
@endtask

@task('installComposerDependencies', ['on' => 'remote'])
    {{ logMessage("🚚 Installing Composer dependencies...") }}

    cd {{ $newReleaseDir }};
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    # Install Octane FrankenPHP worker script
    php artisan octane:install --server=frankenphp --no-interaction
@endtask

@task('installYarnDependencies', ['on' => 'remote'])
    {{ logMessage("📦 Installing Yarn dependencies...") }}

    cd {{ $newReleaseDir }};
    yarn install --frozen-lockfile
@endtask

@task('buildAssets', ['on' => 'remote'])
    {{ logMessage("🌅 Building assets...") }}

    cd {{ $newReleaseDir }};
    yarn build
@endtask

@task('updateSymlinks', ['on' => 'remote'])
    {{ logMessage("🔗 Updating symlinks to persistent data...") }}

    # Remove the storage directory and replace with persistent data
    rm -rf {{ $newReleaseDir }}/storage;
    cd {{ $newReleaseDir }};
    ln -nfs {{ $persistentDir }}/storage storage;

    # Create public storage symlink if it doesn't exist
    cd {{ $newReleaseDir }};
    [ -L public/storage ] || ln -nfs {{ $persistentDir }}/storage/app/public public/storage;
@endtask

@task('migrateDatabase', ['on' => 'remote'])
    {{ logMessage("🗄️ Migrating database...") }}

    cd {{ $newReleaseDir }};
    php artisan migrate --force
@endtask

@task('optimizeInstallation', ['on' => 'remote'])
    {{ logMessage("✨ Optimizing installation...") }}

    cd {{ $newReleaseDir }};
    php artisan clear-compiled
@endtask

@task('blessNewRelease', ['on' => 'remote'])
    {{ logMessage("🙏 Blessing new release...") }}

    # Switch the symlink to the new release
    ln -nfs {{ $newReleaseDir }} {{ $currentDir }}

    cd {{ $newReleaseDir }}

    # Clear and rebuild caches
    php artisan config:clear
    php artisan view:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan event:clear

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
@endtask

@task('restartServices', ['on' => 'remote'])
    {{ logMessage("🔄 Restarting services...") }}

    cd {{ $currentDir }}

    # Restart Octane workers
    sudo /usr/bin/systemctl restart givetwice-octane.service

    # Restart Horizon and Reverb via supervisor
    sudo /usr/bin/supervisorctl restart all
@endtask

@task('cleanOldReleases', ['on' => 'remote'])
    {{ logMessage("🧹 Cleaning up old releases...") }}

    # Keep the 5 most recent releases, delete the rest
    cd {{ $releasesDir }}
    ls -dt {{ $releasesDir }}/* | tail -n +6 | xargs -d "\n" rm -rf;
@endtask

@task('finishDeploy', ['on' => 'local'])
    {{ logMessage("🚀 Application deployed!") }}
@endtask

@task('deployOnlyCode', ['on' => 'remote'])
    {{ logMessage("💻 Deploying code changes (quick deploy)...") }}

    cd {{ $currentDir }}
    git pull origin {{ $branch }}

    php artisan config:clear
    php artisan view:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan event:clear

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    php artisan octane:reload || true
    sudo /usr/bin/supervisorctl restart all
@endtask
