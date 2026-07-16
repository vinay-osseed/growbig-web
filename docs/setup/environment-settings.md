# Site Platform Environment Settings

## Goal

The repository must keep one clean shared settings structure.

Do not commit secrets or machine-specific settings.

## Committed Files

These files are committed:

- `site-platform/web/sites/default/settings.php`
- `site-platform/web/sites/default/settings.platform.php`
- `site-platform/web/sites/default/settings.local.example.php`
- `site-platform/web/sites/default/settings.prod.example.php`
- `site-platform/web/sites/default/services.prod.yml`
- `site-platform/web/sites/default/.gitignore`
- `config/sync/.gitkeep`
- `config/splits/dev/README.md`
- `config/splits/prod/README.md`

## Ignored Files

These files are not committed:

- `site-platform/web/sites/default/settings.local.php`
- `site-platform/web/sites/default/settings.prod.php`
- `site-platform/web/sites/default/settings.ddev.php`
- `site-platform/web/sites/default/files/`

## Environments

Use `DRUPAL_ENV`.

Development:

    DRUPAL_ENV=dev

Production/live:

    DRUPAL_ENV=prod

## DDEV

DDEV provides the database connection through `settings.ddev.php`.

That file is generated locally and is ignored.

Team members should run:

    ddev start
    ddev composer install
    ddev drush site:install standard -y --account-name=admin --account-pass=admin --site-name="Site Platform"

After browser-based install, Drupal may append database settings into `settings.php`.
If that happens, restore the committed settings file:

    git restore site-platform/web/sites/default/settings.php

## Production

Production should use environment variables:

    DRUPAL_ENV=prod
    DRUPAL_HASH_SALT=...
    DRUPAL_DATABASE_NAME=...
    DRUPAL_DATABASE_USER=...
    DRUPAL_DATABASE_PASSWORD=...
    DRUPAL_DATABASE_HOST=db
    DRUPAL_DATABASE_PORT=3306
    DRUPAL_PRIMARY_DOMAIN=example.com
    DRUPAL_ADMIN_DOMAIN=admin.example.com
    DRUPAL_API_DOMAIN=api.example.com
    DRUPAL_UI_DOMAIN=www.example.com

Use `settings.prod.php` only for production-only overrides that cannot be handled by environment variables.

## Config Split

The base settings activate config splits by environment:

- `dev` split when `DRUPAL_ENV=dev`
- `prod` split when `DRUPAL_ENV=prod`

The actual config split entities should be created after Drupal is installed and `config_split` is enabled.
