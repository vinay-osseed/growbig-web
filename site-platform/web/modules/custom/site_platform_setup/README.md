# Site Platform Setup

## Purpose

Site Platform Setup provides Drush commands for the setup lifecycle.

## Commands

    ddev drush site-platform:setup-preview
    ddev drush site-platform:setup-run
    ddev drush site-platform:setup-status
    ddev drush site-platform:setup-complete
    ddev drush site-platform:setup-unlock
    ddev drush site-platform:setup-reset-status
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

## Completion Tracking

Mark setup complete:

    ddev drush site-platform:setup-complete

Unlock setup tracking without deleting data:

    ddev drush site-platform:setup-unlock

Reset setup tracking state without deleting data:

    ddev drush site-platform:setup-reset-status

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
- demo content replacement

## Webform-backed Forms

Forms imported from setup YAML are Webform-backed.

A form entry can include:

    forms:
      - key: contact
        storage: webform
        webformId: yamltest_contact
        title: Contact Form

The `site_form` and `site_form_field` node records remain as compatibility wrappers, but real form submissions should be stored as Webform submissions.


## Media Assets

Setup YAML can now import reusable site-scoped media asset records.

    media_assets:
      - key: hero-image
        title: Hero Image
        kind: image
        url: /assets/demo/hero.jpg
        alt: Hero image

The current phase stores URL/reference metadata. Full binary file import can be added later without changing the public API shape.
