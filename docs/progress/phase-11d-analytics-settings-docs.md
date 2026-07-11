# Phase 11D: Analytics Settings Documentation

## Purpose

Document analytics configuration for environment values and Drupal admin fallback.

## Admin Settings Page

URL:

- /admin/config/site-platform/analytics

## Configuration Priority

Analytics config is resolved in this order:

1. Environment values
2. Drupal admin config
3. Disabled default

## Environment Values

- GOOGLE_ANALYTICS_ENABLED
- GOOGLE_ANALYTICS_MEASUREMENT_ID

Environment values are deployment-specific and take priority over Drupal config.

## Drupal Config

Config object:

- site_platform_api.analytics

Fields:

- enabled
- provider
- measurement_id

## Public API

Endpoint:

- GET /api/v1/analytics/config

Example disabled response:

    {
      "enabled": false,
      "provider": "none",
      "measurementId": ""
    }

Example enabled response:

    {
      "enabled": true,
      "provider": "google_analytics",
      "measurementId": "G-XXXXXXXXXX"
    }

## Security

The frontend receives only safe public tracking config.

Do not expose:

- Google service account JSON
- private keys
- API secrets
- Google Analytics Data API credentials

## Google Analytics Setup

1. Create or open a Google Analytics account.
2. Create a GA4 property.
3. Create a Web data stream.
4. Copy the Measurement ID, for example `G-XXXXXXXXXX`.
5. Add it in either:
   - environment variable `GOOGLE_ANALYTICS_MEASUREMENT_ID`
   - Drupal admin page `/admin/config/site-platform/analytics`
6. Enable analytics using:
   - `GOOGLE_ANALYTICS_ENABLED=1`
   - or the Drupal admin checkbox
7. Frontend reads `/api/v1/analytics/config`.
8. Frontend loads GA tracking only when `enabled` is true.

## Verification

Use:

- ./scripts/check-analytics-config-api.sh
- ./scripts/test-api.sh
- ./scripts/verify-backend-mvp.sh
