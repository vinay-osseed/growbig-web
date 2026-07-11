# Phase 11A: Analytics Integration Planning

## Purpose

Plan analytics integration for the decoupled site platform.

The frontend UI should not hardcode analytics settings.

The backend should own analytics configuration and expose only safe public tracking config to the frontend.

## Analytics Goals

- track frontend page views
- track service, job, career, and contact interest
- track contact form submissions
- track job application submissions
- show analytics/reporting summaries in the admin dashboard later

## Recommended Architecture

Backend owns analytics configuration.

Frontend reads analytics configuration from backend.

Frontend injects/runs the analytics tracking script.

Backend dashboard can later display analytics summaries.

## Google Analytics 4

Google Analytics 4 can be used for hosted analytics.

Required public value:

- GOOGLE_ANALYTICS_MEASUREMENT_ID

Optional enabled flag:

- GOOGLE_ANALYTICS_ENABLED

Example environment values:

    GOOGLE_ANALYTICS_ENABLED=1
    GOOGLE_ANALYTICS_MEASUREMENT_ID=G-XXXXXXXXXX

The Measurement ID is safe to expose to the frontend.

Do not expose private Google credentials, service account JSON, private keys, or reporting API credentials to the frontend.

## Drupal Config vs Environment

Recommended order:

1. Environment values for deployment-specific settings.
2. Drupal config fallback for site-level defaults.
3. Backend API exposes sanitized public config to frontend.

Example future API:

- GET /api/v1/analytics/config

Example response:

    {
      "enabled": true,
      "provider": "google_analytics",
      "measurementId": "G-XXXXXXXXXX"
    }

## Drupal Google Analytics Module

Drupal Google Analytics modules are useful for Drupal-rendered pages and admin/backend pages.

However, the public UI is decoupled and not rendered by Drupal.

So a Drupal module alone does not solve frontend analytics tracking.

For this project, frontend tracking should be driven by a backend analytics config API.

A Drupal module may still be added later if we also want tracking on Drupal admin/backend pages.

## Open-Source Analytics Options

Possible alternatives:

- Matomo
- Plausible
- Umami

These can be used with the same backend-driven configuration pattern.

## Google Analytics Setup Steps

1. Create or open a Google Analytics account.
2. Create a GA4 property for the website.
3. Create a Web data stream.
4. Copy the Measurement ID, for example `G-XXXXXXXXXX`.
5. Add it to environment configuration:

       GOOGLE_ANALYTICS_ENABLED=1
       GOOGLE_ANALYTICS_MEASUREMENT_ID=G-XXXXXXXXXX

6. Backend exposes the safe value through analytics config API.
7. Frontend loads GA tracking only when enabled.
8. Later, configure Google Analytics Data API only if backend dashboard reports need to pull GA metrics.

## Later Dashboard Reporting

For dashboard analytics reports, Google Analytics Data API may be needed.

That requires private server-side credentials.

Those credentials must stay in environment variables or a secrets manager, never in frontend JSON.

Possible future values:

- GOOGLE_ANALYTICS_PROPERTY_ID
- GOOGLE_ANALYTICS_CLIENT_EMAIL
- GOOGLE_ANALYTICS_PRIVATE_KEY

These should be used backend-side only.
