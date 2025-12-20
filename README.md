#for install project
git clone https://github.com/nastagreciha-afk/test.task_management.git 
cd test.task_management
cp .env.example .env

#if you don`t have vendor
docker run --rm \
-v "$(pwd):/app" \
-w /app \
composer:2 \
sh -c "composer config audit.block-insecure false && composer install"

#after
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate

#if you have error file_put_contents(...sessions/...): Failed to open stream
rm -rf storage/bootstrap/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p bootstrap/cache

#after
sudo chown -R $USER:$USER storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

#for update Docker
./vendor/bin/sail down
./vendor/bin/sail up -d

