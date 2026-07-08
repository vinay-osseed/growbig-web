# Phase 7A: Dynamic Page API Contract

## Purpose

Clarify and harden the Page API as the main contract for truly dynamic decoupled frontend rendering.

## Scope completed

Updated Page API response with:

- contractVersion
- route.path
- route.apiPath
- api.self

Updated API smoke test:

- validates Page API contract version
- validates route mapping
- validates section types
- validates Home page section count
- validates About page section count

Added documentation:

- docs/api/page-contract.md

## Architecture decision

The primary frontend flow should be:

- frontend route
- /api/v1/pages/{slug}
- render returned sections dynamically

The reusable content endpoints remain helper APIs for listing and detail routes.

## Next phase

Phase 7B:

- Update OpenAPI docs for new Page API contract fields
- Add route/path examples for dynamic pages
