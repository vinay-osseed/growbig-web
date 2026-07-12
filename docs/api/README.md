# GrowBig Site Platform Frontend API Docs

This directory documents the public frontend API only.

The API exists for frontend developers. It should describe only what the public UI needs to render the website.

## Domain model

- `<domain>` is the public frontend UI.
- `api.<domain>` is the API base URL used by the frontend.
- `admin.<domain>` is the Drupal admin/backend.

## Multi-site model

One Drupal backend can power multiple company sites.

Example:

- GrowBig Technologies LLP
  - own domains
  - own frontend pages
  - own reusable content
  - own menus
  - own analytics key

- OSSeed Technologies LLP
  - own domains
  - own frontend pages
  - own reusable content
  - own menus
  - own analytics key

## Frontend endpoints

- `GET /api/v1/site`
- `GET /api/v1/pages`
- `GET /api/v1/pages/{slug}`
- `GET /api/v1/menus`
- `GET /api/v1/menus/{menu}`
- `GET /api/v1/content/{source}`
- `GET /api/v1/content/{source}/{key}`
- `GET /api/v1/forms/{form}`
- `POST /api/v1/forms/{form}/submit`
- `GET /api/v1/analytics/config`

## Not included here

Admin/dashboard APIs are intentionally not documented in the public frontend API docs.

Drupal admin tools belong under `admin.<domain>`, not in frontend API docs.

## Security note

`siteKey` is a public site identifier, not a secret.

Browser-visible keys are not secure secrets. Public APIs must expose only published, frontend-safe, site-scoped data. Admin/private APIs must rely on Drupal authentication, permissions, server-side validation, and backend-only secrets.
