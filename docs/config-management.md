# Configuration Management

Drupal configuration is exported to:

~~~text
site-platform/config/sync
~~~

Environment-specific configuration uses Config Split:

~~~text
site-platform/config/splits/dev
site-platform/config/splits/stage
site-platform/config/splits/prod
~~~

## Environment selection

The active split is controlled by `DRUPAL_ENV`.

Valid values:

- `dev`
- `stage`
- `prod`

Local DDEV uses:

~~~text
DRUPAL_ENV=dev
~~~

Stage should use:

~~~text
DRUPAL_ENV=stage
~~~

Production should use:

~~~text
DRUPAL_ENV=prod
~~~

## Export config after local changes

~~~bash
ddev drush cex -y
git status
git add -A
git commit -m "Export Drupal configuration"
git push origin dev
~~~

## Import config after pulling changes

~~~bash
git pull
ddev composer install
ddev drush updb -y
ddev drush cim -y
ddev drush cr
~~~

## Dev-only configuration

The dev split contains development/admin helper configuration such as:

- `dblog`
- `field_ui`
- `views_ui`

These should stay out of shared stage/prod configuration unless explicitly needed.
