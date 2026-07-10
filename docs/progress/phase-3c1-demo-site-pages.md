# Phase 3C1: Demo Site Pages

## Purpose

Create re-runnable demo Site Page content based on the GrowBig frontend mockups.

## Scope completed

Created script:

- scripts/drupal/create-demo-site-pages.php

The script creates or updates:

- Home page
- About page

Home page sections:

- Hero Section
- Stats Section
- Services Card Grid Section
- Partners Content List Section

About page sections:

- Hero Section
- Mission & Vision Card Grid Section
- Core Values Card Grid Section
- Leadership Content List Section
- CTA Section

## Notes

This phase creates content in the database.

The generated content is not exported as Drupal config.

The script is idempotent and can be rerun on a fresh database after config import.

## Re-run instructions

Run:

- ddev drush scr /var/www/html/scripts/drupal/create-demo-site-pages.php
- ddev drush cr

## Next phase

Phase 3C2:

- Add page normalization service
- Add /api/v1/pages/{slug}
- Test Home and About API output
- Update OpenAPI docs
