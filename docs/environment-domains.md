# Environment Domains

This project supports separate frontend, Drupal admin/backend, and Drupal API domains.

## Production

~~~text
growbigllp.com       -> Frontend UI
www.growbigllp.com   -> Frontend UI alias
admin.growbigllp.com -> Drupal admin/backend
api.growbigllp.com   -> Drupal API
~~~

Production environment variables:

~~~bash
export DRUPAL_ENV=prod
export DRUPAL_PRIMARY_DOMAIN=admin.growbigllp.com
export DRUPAL_ADMIN_DOMAIN=admin.growbigllp.com
export DRUPAL_API_DOMAIN=api.growbigllp.com
export DRUPAL_UI_DOMAIN=growbigllp.com
export DRUPAL_EXTRA_TRUSTED_HOSTS=www.growbigllp.com
~~~

## Stage

~~~text
stage.growbigllp.com       -> Frontend UI stage
stage-admin.growbigllp.com -> Drupal admin/backend stage
stage-api.growbigllp.com   -> Drupal API stage
~~~

Stage environment variables:

~~~bash
export DRUPAL_ENV=stage
export DRUPAL_PRIMARY_DOMAIN=stage-admin.growbigllp.com
export DRUPAL_ADMIN_DOMAIN=stage-admin.growbigllp.com
export DRUPAL_API_DOMAIN=stage-api.growbigllp.com
export DRUPAL_UI_DOMAIN=stage.growbigllp.com
~~~

## Dev server

~~~text
dev.growbigllp.com       -> Frontend UI dev
dev-admin.growbigllp.com -> Drupal admin/backend dev
dev-api.growbigllp.com   -> Drupal API dev
~~~

Dev environment variables:

~~~bash
export DRUPAL_ENV=dev
export DRUPAL_PRIMARY_DOMAIN=dev-admin.growbigllp.com
export DRUPAL_ADMIN_DOMAIN=dev-admin.growbigllp.com
export DRUPAL_API_DOMAIN=dev-api.growbigllp.com
export DRUPAL_UI_DOMAIN=dev.growbigllp.com
~~~

## Local DDEV

~~~text
growbig-web.ddev.site             -> Drupal default local
admin.growbig-web.ddev.site       -> Drupal admin/backend local
api.growbig-web.ddev.site         -> Drupal API local
ui.growbig-web.ddev.site          -> Future frontend local

dev-admin.growbig-web.ddev.site   -> Local dev backend simulation
dev-api.growbig-web.ddev.site     -> Local dev API simulation
dev-ui.growbig-web.ddev.site      -> Local dev UI simulation

stage-admin.growbig-web.ddev.site -> Local stage backend simulation
stage-api.growbig-web.ddev.site   -> Local stage API simulation
stage-ui.growbig-web.ddev.site    -> Local stage UI simulation

prod-admin.growbig-web.ddev.site  -> Local prod backend simulation
prod-api.growbig-web.ddev.site    -> Local prod API simulation
prod-ui.growbig-web.ddev.site     -> Local prod UI simulation
~~~

## SSL note

Use first-level subdomains such as:

~~~text
stage-admin.growbigllp.com
~~~

instead of nested subdomains such as:

~~~text
admin.stage.growbigllp.com
~~~

This is easier to cover with a normal wildcard certificate like:

~~~text
*.growbigllp.com
~~~
