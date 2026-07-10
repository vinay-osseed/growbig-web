# Phase 6B: Reusable Content Detail API Documentation

## Purpose

Document and validate reusable content detail API endpoints.

## Scope completed

Updated:

- docs/api/openapi.yml
- scripts/test-api.sh

Documented endpoint:

- GET /api/v1/content/{source}/{key}

Supported sources:

- services
- partners
- team

Added schema:

- ReusableContentDetail

The API smoke test now validates:

- /api/v1/content/services/website-development
- /api/v1/content/partners/aws
- /api/v1/content/team/founder-ceo

## Error handling

Documented:

- 400 invalid_source
- 404 not_found

## Next phase

Phase 7:

- Backend cleanup and hardening
- Investigate recurring AttributeRouteDiscovery warning
- Optional performance improvement for content detail lookup
