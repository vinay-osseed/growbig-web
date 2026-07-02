# Local Setup

## Start DDEV

~~~bash
ddev start
~~~

## Install Composer dependencies

~~~bash
ddev composer install
~~~

## Install Drupal if database is empty

~~~bash
ddev drush site:install standard \
  --account-name=admin \
  --account-pass=admin \
  --site-name="Site Platform" \
  -y
~~~

## Import configuration

~~~bash
ddev drush cim -y
ddev drush cr
~~~

## Login link

~~~bash
ddev drush uli
~~~

## Status check

~~~bash
ddev drush status
ddev drush status-report
~~~
