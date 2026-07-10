# Phase 6A: Reusable Content Detail API

## Purpose

Expose individual reusable content items through frontend-ready API endpoints.

## Scope completed

Updated controller:

- Drupal\site_platform_api\Controller\ContentController

Added endpoint:

- GET /api/v1/content/{source}/{key}

Supported sources:

- services
- partners
- team

Current examples:

- /api/v1/content/services/website-development
- /api/v1/content/partners/aws
- /api/v1/content/team/founder-ceo

## Response includes

- source
- key
- item

## Error handling

Unsupported content sources return a 400 JSON response.

Missing content items return a 404 JSON response.

## Notes

The detail endpoint reuses the same normalized item structure used by the reusable content list API.

## Next phase

Phase 6B:

- Update OpenAPI docs for /api/v1/content/{source}/{key}
- Update API smoke test script to validate reusable content detail endpoints
