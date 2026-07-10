# Phase 7B: Page Contract OpenAPI

## Purpose

Document the dynamic Page API contract fields in OpenAPI.

## Scope completed

Updated:

- docs/api/openapi.yml

Documented Page API fields:

- contractVersion
- route.path
- route.apiPath
- api.self

Added schemas:

- PageRoute
- PageApiLinks

## Notes

This phase documents the Page API as the main decoupled frontend rendering contract.

The reusable content APIs remain helper endpoints for listing and detail routes.

## Next phase

Phase 7C:

- Backend cleanup and hardening
- Investigate recurring AttributeRouteDiscovery warning
- Optional optimization for reusable content detail lookup
