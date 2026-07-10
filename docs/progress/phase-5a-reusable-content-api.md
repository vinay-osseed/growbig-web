# Phase 5A: Reusable Content API

## Purpose

Expose reusable backend content through dedicated frontend-ready API endpoints.

## Scope completed

Added controller:

- Drupal\site_platform_api\Controller\ContentController

Added endpoint:

- GET /api/v1/content/{source}

Supported sources:

- services
- partners
- team

Supported query parameters:

- limit
- featuredOnly

## Current examples

- /api/v1/content/services
- /api/v1/content/partners
- /api/v1/content/team
- /api/v1/content/services?limit=3
- /api/v1/content/team?featuredOnly=1

## Response includes

- source
- limit
- featuredOnly
- count
- items

## Error handling

Unsupported content sources return a 400 JSON response.

## Notes

This endpoint reuses the existing SitePlatformContentListNormalizer added for dynamic Page API content lists.

## Next phase

Phase 5B:

- Update OpenAPI docs for /api/v1/content/{source}
- Update API smoke test script to validate direct content endpoints
