# Deployment Checklist

## Required environment variable

Set `DRUPAL_ENV` per environment:

- `dev`
- `stage`
- `prod`

## Stage deployment

Use:

~~~bash
export DRUPAL_ENV=stage
./scripts/deploy-drupal.sh
~~~

## Production deployment

Use:

~~~bash
export DRUPAL_ENV=prod
./scripts/deploy-drupal.sh
~~~

## Deployment steps

The deployment script runs:

- Composer install
- Database updates
- Config import
- Cache rebuild
- Drush status check

## Manual fallback

~~~bash
composer install --working-dir=site-platform --no-dev --optimize-autoloader
cd site-platform
vendor/bin/drush updb -y
vendor/bin/drush cim -y
vendor/bin/drush cr
vendor/bin/drush status
~~~
