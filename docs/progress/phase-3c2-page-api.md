# Phase 3C2: Page API

## Purpose

Expose reusable Site Page content through a frontend-ready custom API endpoint.

## Scope completed

Added service:

- site_platform_api.page_normalizer

Added class:

- Drupal\site_platform_api\SitePlatformPageNormalizer

Added controller:

- Drupal\site_platform_api\Controller\PageController

Added endpoint:

- GET /api/v1/pages/{slug}

Tested examples:

- /api/v1/pages/home
- /api/v1/pages/about

## Response includes

- Page identity
- Page key / slug
- Page type
- Summary
- Site references
- SEO fields
- Structured sections
- Section-specific data
- Nested stats/card paragraphs
- Media normalized with image styles where available

## Error handling

Missing page keys return a 404 JSON response.

## Notes

This phase does not yet include dynamic content resolution for Content List sections.

For now, Content List sections expose the source, limit, and featuredOnly settings. Later phases will populate services, partners, team, and jobs.

## Next phase

Phase 3C3:

- Update OpenAPI docs for /api/v1/pages/{slug}
- Add page API examples
- Optionally add API test script
