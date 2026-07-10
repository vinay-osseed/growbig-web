# Frontend Integration Guide

## Primary Page API

Frontend pages should use the Dynamic Page API as the primary data source.

## Routes

| Frontend Route | API Endpoint |
|---|---|
| / | GET /api/v1/pages/home |
| /about | GET /api/v1/pages/about |
| /careers | GET /api/v1/pages/careers |
| /contact | GET /api/v1/pages/contact |

## Page Index

Use this endpoint to discover available pages:

- GET /api/v1/pages

Current top-level pages:

- home
- about
- careers
- contact

## Section Rendering

Frontend should render sections based on:

- section.type

Supported section types include:

- hero
- cta
- stats
- cardGrid
- imageText
- contentList

## Careers Page

Careers page endpoint:

- GET /api/v1/pages/careers

The Careers page includes a contentList section with:

- source: jobs

The jobs list is also available directly from:

- GET /api/v1/content/jobs

Job detail endpoint:

- GET /api/v1/content/jobs/{key}

Example:

- GET /api/v1/content/jobs/frontend-developer

## Contact Page

Contact page endpoint:

- GET /api/v1/pages/contact

Contact form metadata:

- GET /api/v1/forms/contact-us

Contact form submission:

- POST /api/v1/forms/contact-us/submit

## Job Application Form

Job application form metadata:

- GET /api/v1/forms/job-application

Job application submission:

- POST /api/v1/forms/job-application/submit

When applying from a job detail page, frontend should prefill:

- job_key
- job_title

## Contact Form Budget Range

The contact form includes:

- budget_range

This field is optional.

Frontend can render it only where needed.

## Resume Upload

The job application metadata includes:

- resume_upload

Current API metadata marks this as:

- apiSupported: false

File upload support should be implemented separately.

## Header and Footer Menus

Frontend should not hardcode header or footer navigation.

Use backend menu APIs:

- GET /api/v1/menus
- GET /api/v1/menus/header
- GET /api/v1/menus/footer

Header and footer menus are driven by Site Page fields:

- Show In Header
- Show In Footer
- Menu Title
- Menu Weight

Each menu item includes:

- title
- slug
- url
- apiPath

## Analytics

Analytics settings should not be hardcoded in the frontend.

Future frontend analytics config should come from backend API.

Suggested endpoint:

- GET /api/v1/analytics/config

For Google Analytics 4, frontend should only receive the public Measurement ID.

Example response:

    {
      "enabled": true,
      "provider": "google_analytics",
      "measurementId": "G-XXXXXXXXXX"
    }

Do not expose private Google API credentials in frontend code.

## Analytics Config API

Use:

- GET /api/v1/analytics/config

The frontend should load analytics only when `enabled` is true.

For Google Analytics, use `measurementId`.

The frontend must not hardcode analytics IDs.
