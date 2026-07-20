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
    GET /api/v1/forms/{form}
    POST /api/v1/forms/{form}/submit
    GET /api/v1/content
    GET /api/v1/content/{source}
    GET /api/v1/content/{source}/{key}

## Local Testing

Local testing can use the `site` query parameter.

Examples:

    https://site-platform.ddev.site/api/v1/site?site=growbig
    https://site-platform.ddev.site/api/v1/pages/home?site=growbig
    https://site-platform.ddev.site/api/v1/routes/about?site=growbig
    https://site-platform.ddev.site/api/v1/menus/main?site=growbig
    https://site-platform.ddev.site/api/v1/forms/contact?site=growbig
    https://site-platform.ddev.site/api/v1/content/global/footer_cta?site=growbig

The `site` query parameter is local/dev only. Production resolution must use domains.

## Response Shape

Successful responses use:

    data
    meta
    cache

Errors use:

    error

## Current Scope

This API returns Site Profile, Site Page, route, basic Site Menu, basic component, basic form, and reusable content data.

Advanced multilingual fallback comes later.

## Forms

Form endpoints are Webform-first.

    GET  /api/v1/forms/{form}
    POST /api/v1/forms/{form}/submit

When a site-scoped Webform exists, submissions are stored as `webform_submission` entities. The old `site_form_submission` node storage remains only as a legacy fallback.
