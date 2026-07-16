# Site Platform Setup

## Purpose

Site Platform Setup provides Drush commands for the setup lifecycle.

## Commands

    ddev drush site-platform:setup-preview
    ddev drush site-platform:setup-run
    ddev drush site-platform:setup-status
    ddev drush site-platform:setup-reset-demo
    ddev drush site-platform:setup-reset-site yamltest
    ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml

## YAML Import

The setup import command currently supports:

- Site Profile
- Site Pages
- Page Components
- Site Content Blocks
- Site Menus
- Site Menu Items
- Site Forms
- Site Form Fields

Example:

    ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml

Dry-run:

    ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml --dry-run

## Reset Safety

`site-platform:setup-reset-demo` is dry-run by default.

It only deletes demo records when called with:

    ddev drush site-platform:setup-reset-demo --execute

`site-platform:setup-reset-site` is also dry-run by default.

It only deletes one site's setup-managed records when called with:

    ddev drush site-platform:setup-reset-site yamltest --execute

## Later Phases

Later setup phases can add:

- media import
- full idempotent site setup
- setup completion tracking
- demo content replacement
