# Phase 4D: Content List API Documentation

## Purpose

Document and validate dynamic Content List items in the Page API.

## Scope completed

Updated:

- scripts/test-api.sh

The API smoke test now validates:

- /api/v1/site returns valid JSON
- /api/v1/pages/home returns valid JSON
- /api/v1/pages/about returns valid JSON
- /api/v1/pages/missing returns HTTP 404 JSON
- Home page Content List includes Partner items
- About page Content List includes Team Member items

## Dynamic Content List behavior

Content List sections now include:

- source
- limit
- featuredOnly
- items

Supported sources:

- services
- partners
- team

## Notes

The OpenAPI file already documents Content List sections. A future cleanup can expand it further with dedicated Service, Partner, and Team Member item schemas.

## Next phase

Phase 5:

- Add dedicated list endpoints if needed
- Add individual service/detail API if required by frontend routing
- Connect frontend UI to the new Page API and Content List data
