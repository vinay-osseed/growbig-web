# Site Platform API

## Purpose

Site Platform API provides public JSON endpoints for decoupled frontends.

## Endpoints

    GET /api/v1/site
    GET /api/v1/pages
    GET /api/v1/pages/{slug}
    GET /api/v1/routes
    GET /api/v1/routes/{path}
    GET /api/v1/menus
    GET /api/v1/menus/{menu}

## Local Testing

Local testing can use the `site` query parameter.

Examples:

    https://site-platform.ddev.site/api/v1/site?site=growbig
    https://site-platform.ddev.site/api/v1/pages?site=growbig
    https://site-platform.ddev.site/api/v1/pages/home?site=growbig
    https://site-platform.ddev.site/api/v1/routes?site=growbig
    https://site-platform.ddev.site/api/v1/routes/about?site=growbig
    https://site-platform.ddev.site/api/v1/menus?site=growbig
    https://site-platform.ddev.site/api/v1/menus/main?site=growbig

The `site` query parameter is local/dev only. Production resolution must use domains.

## Response Shape

Successful responses use:

    data
    meta
    cache

Errors use:

    error

## Current Scope

This API returns Site Profile, Site Page, route, basic Site Menu, and basic component data.

Forms, reusable content, and advanced multilingual fallback come later.
