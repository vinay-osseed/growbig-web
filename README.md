# Site Platform

Reusable Drupal backend platform for content-driven websites.

The current focus is the Drupal backend only. Frontend implementation will be added later when the framework and requirements are finalized.

## Local development

~~~bash
ddev start
ddev composer install
ddev drush status
~~~

## Documentation

- `docs/backend-architecture.md`
- `docs/content-model.md`
- `docs/config-management.md`
- `docs/deployment-flow.md`
- `docs/deployment-checklist.md`
- `docs/local-setup.md`
- `docs/recipe-usage.md`

## Branch flow

~~~text
feature/* -> dev -> stage -> master
~~~

## Config flow

~~~bash
ddev drush cex -y
git add -A
git commit -m "Export Drupal configuration"
git push origin dev
~~~
