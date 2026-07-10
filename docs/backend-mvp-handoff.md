# Backend MVP Handoff

## Project

GrowBig Web Backend Platform

## Status

Backend MVP is complete for the current dynamic decoupled site architecture.

The backend provides:

- reusable site profile API
- dynamic page API
- page index API
- reusable content list APIs
- reusable content detail APIs
- smoke tests
- backend verification script
- OpenAPI documentation

## Local Environment

DDEV URLs:

- Main: https://growbig-web.ddev.site
- Admin: https://admin.growbig-web.ddev.site
- API: https://api.growbig-web.ddev.site
- UI: https://ui.growbig-web.ddev.site

Platform:

- Drupal 11.4.0
- PHP 8.4
- MariaDB
- Drush 13
- Default theme: Olivero
- Admin theme: Gin

## Custom Modules

### site_platform_api

Provides public frontend API endpoints.

Path:

- site-platform/web/modules/custom/site_platform_api

### site_platform_admin

Provides backend admin dashboard helpers.

Path:

- site-platform/web/modules/custom/site_platform_admin

Dashboard:

- /admin/site-dashboard

## Primary Dynamic Page Flow

The preferred frontend rendering flow is:

- Frontend route: /about
- API call: GET /api/v1/pages/about
- Backend returns normalized page data with sections and reusable content list items
- Frontend renders by section type

The frontend should primarily use:

- GET /api/v1/pages/{slug}

## API Endpoints

### Site Profile

- GET /api/v1/site

Returns company/site settings, branding, contact details, SEO defaults, social links, theme data, and footer data.

### Pages Index

- GET /api/v1/pages

Returns available published dynamic pages.

Used for page discovery.

### Page Detail

- GET /api/v1/pages/{slug}

Examples:

- GET /api/v1/pages/home
- GET /api/v1/pages/about

Returns one normalized dynamic page with:

- contractVersion
- route
- api links
- SEO
- sections
- reusable content list items where configured

### Reusable Content Lists

- GET /api/v1/content/services
- GET /api/v1/content/partners
- GET /api/v1/content/team

Supported query parameters:

- limit
- featuredOnly

Examples:

- GET /api/v1/content/services?limit=3
- GET /api/v1/content/services?featuredOnly=true

### Reusable Content Detail

- GET /api/v1/content/{source}/{key}

Examples:

- GET /api/v1/content/services/website-development
- GET /api/v1/content/partners/aws
- GET /api/v1/content/team/founder-ceo

Supported sources:

- services
- partners
- team

## Content Types

### Site Page

Machine name:

- site_page

Purpose:

- dynamic frontend pages

Important fields:

- field_page_key
- field_page_type
- field_summary
- field_sites
- field_sections
- SEO fields

### Service

Machine name:

- service

Important fields:

- field_service_key
- field_summary
- field_display_order
- field_is_featured
- field_is_active
- field_icon
- field_accent_color
- field_image
- field_link_url

### Partner

Machine name:

- partner

Important fields:

- field_partner_key
- field_summary
- field_display_order
- field_is_featured
- field_is_active
- field_logo
- field_website

### Team Member

Machine name:

- team_member

Important fields:

- field_member_key
- field_role
- field_summary
- field_bio
- field_display_order
- field_is_featured
- field_is_active
- field_photo
- field_email
- field_linkedin_url

## Paragraph Section Types

Current page sections include:

- hero_section
- cta_section
- stats_section
- stats_item
- card_grid_section
- card_item
- image_text_section
- content_list_section

The `content_list_section` can dynamically include reusable content items from:

- services
- partners
- team

## API Contract

Current contract version:

- 1.0

Page responses include:

- contractVersion
- route.path
- route.apiPath
- api.self

The frontend should use `section.type` to decide which component to render.

## Tests and Verification

Run API smoke tests:

- ./scripts/test-api.sh

Run custom code check:

- ./scripts/check-code.sh

Run Drupal platform check:

- ./scripts/check-drupal.sh

Run full backend MVP verification:

- ./scripts/verify-backend-mvp.sh

Use a custom base URL:

- BASE_URL=https://example.ddev.site ./scripts/verify-backend-mvp.sh

## OpenAPI Documentation

OpenAPI file:

- docs/api/openapi.yml

## Progress Docs

Relevant progress documents:

- docs/progress/phase-7a-dynamic-page-contract.md
- docs/progress/phase-7b-page-contract-openapi.md
- docs/progress/phase-7e-pages-index-api.md
- docs/progress/phase-7f-backend-mvp-verification.md

## Known Notes

JSON:API Extras was removed because it was unused for this backend and caused a Drupal 11 route discovery warning.

The backend currently uses custom `/api/v1/*` endpoints instead of JSON:API for frontend rendering.

## Next Possible Work

Future backend improvements can include:

- more page types
- more reusable content sources
- detail pages for services/team/partners on the frontend
- environment-specific deployment docs
- frontend integration examples

## Phase 8 Additions

The backend now also includes top-level Careers, Jobs, Contact, and Webform support.

### Careers

Frontend route:

- /careers

Primary API:

- GET /api/v1/pages/careers

The Careers page includes a dynamic jobs content list.

### Jobs

Reusable content APIs:

- GET /api/v1/content/jobs
- GET /api/v1/content/jobs/{key}

Example:

- GET /api/v1/content/jobs/frontend-developer

### Contact

Frontend route:

- /contact

Primary API:

- GET /api/v1/pages/contact

Contact form APIs:

- GET /api/v1/forms/contact-us
- POST /api/v1/forms/contact-us/submit

The contact form includes optional budget_range.

### Job Application

Job application form APIs:

- GET /api/v1/forms/job-application
- POST /api/v1/forms/job-application/submit

Resume upload is listed in metadata but is not API-supported yet.

## Phase 9 Menu API

The backend now exposes header and footer menus from Site Page menu fields.

### Menu Fields

Site Page includes:

- Show In Header
- Show In Footer
- Menu Title
- Menu Weight

### Menu Endpoints

- GET /api/v1/menus
- GET /api/v1/menus/header
- GET /api/v1/menus/footer

### Frontend Usage

Frontend should not hardcode header or footer navigation.

Header and footer should be rendered from the Menu API.

Each menu item includes:

- title
- slug
- url
- weight
- apiPath
