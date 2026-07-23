# Site Platform v2 API Contract

## Purpose

Site Platform v2 exposes a stable, site-scoped JSON API for a decoupled frontend.

Site resolution is handled by domain or local `?site=` query during development.

## Site

    GET /api/v1/site

Returns resolved Site Profile data.

## Pages

    GET /api/v1/pages
    GET /api/v1/pages/{slug}

Returns Site Page records and page detail data, including page components when present.

## Routes

    GET /api/v1/routes
    GET /api/v1/routes/{path}

Returns path-to-page route resolution data.

## Menus

    GET /api/v1/menus
    GET /api/v1/menus/{menu}

Returns site-scoped menu data.

## Forms

    GET  /api/v1/forms/{form}
    POST /api/v1/forms/{form}/submit

Forms are Webform-first. When a matching Webform exists, submissions are saved as `webform_submission` entities.

Legacy `site_form_submission` node storage remains only as fallback compatibility.

## Content Blocks

    GET /api/v1/content
    GET /api/v1/content/{source}
    GET /api/v1/content/{source}/{key}

Returns reusable site-scoped content block data.

## Media

    GET /api/v1/media
    GET /api/v1/media/{key}

Returns reusable site-scoped media asset metadata and URL/reference data.

Current scope stores media metadata only. Binary ingestion can be added later.

## SEO

    GET /api/v1/seo
    GET /api/v1/seo/page/{slug}

Returns site/page SEO metadata from Site Page fields.

## Analytics

    GET /api/v1/analytics

Returns GA/GTM IDs from Site Profile fields. The frontend controls whether and how scripts are loaded.

## Search

    GET /api/v1/search?q=term

Returns simple site-scoped search results across pages, content blocks, and media assets.

This is a foundation search endpoint, not a full search engine replacement.

## Error Shape

Errors use a JSON object with an `error` key containing:

- `code`
- `message`
- `details`

## Cache Shape

Successful API responses include a `cache` object with:

- `maxAge`
- `tags`
- `contexts`
