# Phase 06: Route Resolver API

## Goal

Add the first frontend route resolver endpoint.

## Endpoints

    GET /api/v1/routes
    GET /api/v1/routes/{path}

## Behavior

The endpoint resolves a frontend path for the active Site Profile.

Examples:

    /api/v1/routes?site=growbig
    /api/v1/routes/about?site=growbig
    /api/v1/routes?site=osseed

The front page uses:

    /

and can be requested with:

    /api/v1/routes?site=growbig

## Response Shape

Success:

    data.route
    data.page
    meta
    cache

Error:

    error

## Current Limits

This phase resolves only Site Page records by `field_page_path`.

It does not resolve:

- Drupal aliases
- menu links
- redirects
- external links
- components
- multilingual fallbacks

Those come later.
