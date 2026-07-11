# Phase 11B: Analytics Config API

## Purpose

Expose safe public analytics configuration to the decoupled frontend.

Frontend should not hardcode analytics settings.

## Endpoint

- GET /api/v1/analytics/config

## Environment Values

- GOOGLE_ANALYTICS_ENABLED
- GOOGLE_ANALYTICS_MEASUREMENT_ID

## Response

Without environment values:

    {
      "enabled": false,
      "provider": "none",
      "measurementId": ""
    }

With Google Analytics enabled:

    {
      "enabled": true,
      "provider": "google_analytics",
      "measurementId": "G-XXXXXXXXXX"
    }

## Security

Only public analytics tracking config is exposed.

Do not expose:

- Google service account JSON
- private keys
- dashboard/reporting API credentials
- API secrets

## Verification

Use:

- ./scripts/check-analytics-config-api.sh
- ./scripts/test-api.sh
- ./scripts/verify-backend-mvp.sh
