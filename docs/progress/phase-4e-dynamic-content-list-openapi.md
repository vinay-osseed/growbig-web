# Phase 4E: Dynamic Content List OpenAPI

## Purpose

Document dynamic Content List items in the OpenAPI specification.

## Scope completed

Updated:

- docs/api/openapi.yml

Expanded schema:

- ContentListSection

Added schemas:

- ServiceListItem
- PartnerListItem
- TeamMemberListItem

## Documented behavior

Content List sections now document:

- source
- limit
- featuredOnly
- items

Supported item sources:

- services
- partners
- team

## Notes

This phase only updates API documentation.

No runtime API behavior was changed.

## Next phase

Phase 5:

- Connect frontend UI to /api/v1/site
- Connect frontend UI to /api/v1/pages/home
- Connect frontend UI to /api/v1/pages/about
- Render dynamic contentList items from the API
