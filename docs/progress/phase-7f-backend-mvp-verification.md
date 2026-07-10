# Phase 7F: Backend MVP Verification

## Purpose

Add a single verification script for the backend MVP.

This script confirms that the Drupal backend, custom code, API endpoints, and dynamic content contract are working together.

## Script

- scripts/verify-backend-mvp.sh

## What it validates

The script runs:

- Git working tree status
- Custom Drupal code check
- Drupal platform check
- API smoke tests

It also verifies required backend endpoints:

- GET /api/v1/site
- GET /api/v1/pages
- GET /api/v1/pages/home
- GET /api/v1/pages/about
- GET /api/v1/content/services
- GET /api/v1/content/partners
- GET /api/v1/content/team
- GET /api/v1/content/services/website-development

## Usage

Default local DDEV URL:

- ./scripts/verify-backend-mvp.sh

Custom base URL:

- BASE_URL=https://example.ddev.site ./scripts/verify-backend-mvp.sh

## Notes

This does not rebuild or destroy the local database.

It is a safe verification script for current local, dev, or stage environments.
