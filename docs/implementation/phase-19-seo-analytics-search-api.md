# Phase 19: SEO, Analytics, and Search API Foundation

## Goal

Add the next backend deadline layer without changing existing page, menu, content, media, or form API contracts.

This phase adds lightweight API endpoints for:

- SEO metadata
- analytics configuration
- site-scoped search

## New API Endpoints

    GET /api/v1/seo
    GET /api/v1/seo/page/{slug}
    GET /api/v1/analytics
    GET /api/v1/search?q=term

## SEO Scope

The SEO API returns site-aware metadata from Site Page fields:

- page title
- page slug/path
- SEO title
- SEO description

It is intentionally API-first and does not yet depend on the Metatag contrib module. Metatag integration can be added later if needed.

## Analytics Scope

The analytics API returns site-scoped analytics IDs from Site Profile fields:

- GA Measurement ID
- GTM Container ID

The frontend can decide whether to load scripts based on this response.

## Search Scope

The search API is a simple backend foundation. It searches site-scoped records across:

- Site Pages
- Site Content Blocks
- Site Media Assets

This is not a replacement for a full search engine. Later phases can replace or extend it with Drupal Search API, database indexing, or an external engine.

## Safety

This phase does not delete data.

It adds API controllers, routes, docs, and verification scripts only.
