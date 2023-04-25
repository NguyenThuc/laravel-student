## Development

Install `Docker` and `make` then using this below commands

```
make init #For first time
make up #From 2nd time

make npm-watch #hot reload
```

And then access to `http://localhost:{WEB_PORT}`

## Link folders from eu-common

```
ln ../eu-common/src/app/Models/* ./app/Models
ln ../eu-common/src/app/Services/* ./app/Services
ln ../eu-common/src/database/migrations/* ./database/migrations
ln ../eu-common/src/database/seeders/* ./database/seeders
```

Note: Please only add Model, Service, migration to `eu-common`.
The steps to do it should be

1. Create a Model using `artisan` command on `eu-agency`: `php artisan make:model User --migration`
2. Move generated file named `app/Models/User.php` and `migrations/xxx_create_users.php` to `eu-common/src/app/Models/User.php`, `eu-common/src/databases/migrations/xxx_create_users.php`
3. Run `make link-common` again
4. You can add codes directly to the `eu-agency/app/Models/User.php`
5. You need to commit your changes on `eu-common`

## Gitflow

### New feature development

-   Create a new branch from `develop` with name prefix `feature/{feature_description}`
-   Create a pull request to `develop` branch
-   Assign a PR to reviewer

### Hotfix

-   Create a new branch from `main` with name prefix `hotfix/{hotfix_description}`
-   Create a pull request to `main` branch
-   Assign a PR to reviewer
### heroku
- Tạo file Procfile  : web: vendor/bin/heroku-php-apache2 public/
- Tạo kết nối database trong file env 
- Tạo các biến 
  heroku config:add APP_NAME=Laravel
  heroku config:add APP_ENV=production
  heroku config:add APP_KEY=[APP_KEY VALUE from .env]
  heroku config:add APP_DEBUG=true
  heroku config:add APP_URL=EXAMPLE.herokuapp.com

