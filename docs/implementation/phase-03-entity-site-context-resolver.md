# Phase 03: Entity Site Context Resolver

## Goal

Make SiteContextResolver prefer real Site Profile nodes over environment-only bootstrap values.

## What This Adds

- Site Profile host/domain lookup
- Site Profile site-key lookup
- local-only site query override
- siteProfileId on SiteContext
- entity source metadata

## Resolution Order

1. Local-only site key override.
2. Site Profile API domain.
3. Site Profile admin domain.
4. Site Profile frontend domain.
5. Environment bootstrap domains.
6. Local bootstrap fallback outside production.
7. Unresolved context in production when no match exists.

## Why

The platform must support many isolated companies and domains from one Drupal codebase.

Example:

    api.growbigllp.com
    admin.growbigllp.com
    www.growbigllp.com

and:

    api.osseed.com
    admin.osseed.com
    www.osseed.com

The resolver must find the correct Site Profile before any public API decides what content is visible.
