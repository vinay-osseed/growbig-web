# Site Platform API

## Purpose

Site Platform API provides public JSON endpoints for decoupled frontends.

This phase adds the first read-only API endpoints only.

## Endpoints

    GET /api/v1/site
    GET /api/v1/pages
    GET /api/v1/pages/{slug}

## Local Testing

Local testing can use the `site` query parameter.

Examples:

    https://site-platform.ddev.site/api/v1/site?site=growbig
    https://site-platform.ddev.site/api/v1/pages?site=growbig
    https://site-platform.ddev.site/api/v1/pages/home?site=growbig

The `site` query parameter is local/dev only. Production resolution must use domains.

## Response Shape

Successful responses use:

    data
    meta
    cache

Errors use:

    error

## Current Scope

This API only returns Site Profile and Site Page data.

Components, menus, forms, route aliases, and reusable content come later.
