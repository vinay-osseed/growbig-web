# Phase 5B: Reusable Content API Documentation

## Purpose

Document and validate the reusable content API endpoint.

## Scope completed

Updated:

- docs/api/openapi.yml
- scripts/test-api.sh

Documented endpoint:

- GET /api/v1/content/{source}

Supported sources:

- services
- partners
- team

Supported query parameters:

- limit
- featuredOnly

Added schema:

- ReusableContentList

The API smoke test now validates:

- /api/v1/content/services
- /api/v1/content/partners
- /api/v1/content/team
- /api/v1/content/services?limit=3

## Next phase

Phase 6:

- Add detail endpoints if frontend routing needs them
- Example: /api/v1/content/services/{key}
- Example: /api/v1/content/team/{key}
