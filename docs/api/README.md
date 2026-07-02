# GrowBig Site Platform API Docs

This directory contains OpenAPI documentation for the clean frontend API exposed by Drupal.

## Current endpoints

- GET /api/v1/site

## OpenAPI file

The main API documentation file is:

- docs/api/openapi.yml

## How to view

Open Swagger Editor:

https://editor.swagger.io/

Then import or paste the contents of:

docs/api/openapi.yml

## Current API purpose

The API is used by the frontend application to fetch Drupal-managed content and global site settings.

The current site API returns:

- Site identity
- Domain URLs
- Branding media
- SEO defaults
- Contact details
- Theme values
- Metadata about the active Site Profile

## Maintenance rule

Whenever a custom API endpoint is added or changed, update docs/api/openapi.yml in the same commit.

## Planned future endpoints

- GET /api/v1/navigation
- GET /api/v1/homepage
- GET /api/v1/pages/{path}
- GET /api/v1/services
- GET /api/v1/services/{slug}
- POST /api/v1/contact
