# Phase 4B: Demo Business Content

## Purpose

Create reusable demo content for the business content types added in Phase 4A.

## Scope completed

Created script:

- scripts/drupal/create-demo-business-content.php

The script creates or updates:

- Services
- Partners
- Team Members

Demo content created:

- 6 services
- 4 partners
- 3 team members

Updated Site Dashboard cards:

- Services changed to Ready
- Partners changed to Ready
- Leadership changed to Ready

## Notes

This phase creates content in the database.

The generated content is not exported as Drupal config.

The script is idempotent and can be rerun on a fresh database after config import.

## Re-run instructions

Run:

- ddev drush scr /var/www/html/scripts/drupal/create-demo-business-content.php
- ddev drush cr

## Next phase

Phase 4C:

- Populate Content List sections from real Service, Partner, and Team Member content
- Update Page API output for dynamic content lists
- Update OpenAPI docs
