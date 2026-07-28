# Drupal Decoupled Backend Template

## Final supported architecture

This repository is now treated as a reusable single-backend Drupal decoupled template.

Supported scope:

- One Drupal backend
- One frontend application
- One site configuration
- Drupal Webform for forms
- Drupal Media Library for media
- Paragraphs and Layout Paragraphs for page building
- Metatag, Redirect, and Simple XML Sitemap for SEO
- JSON/API endpoints for frontend usage
- Docker image based deployment
- Backup and restore scripts

Out of scope:

- Multisite
- Multiple site profiles
- Domain Access based tenant separation
- Multiple frontend tenants
- Required site query routing

Preferred API shape:

- /api/v2/bootstrap
- /api/v2/pages
- /api/v2/pages/home
- /api/v2/navigation
- /api/v2/forms
- /api/v2/forms/contact
- /api/v2/datasets
- /api/v2/careers

Temporary compatibility may still accept site query parameters, but the backend should not require them.

Production rule:

Avoid full config import unless config export is complete and reviewed.

Use targeted setup scripts instead of blind drush cim -y.
