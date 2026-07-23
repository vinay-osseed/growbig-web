# Site Platform API Contract

## Main Rule

APIs must be stable and frontend-safe.

Frontend applications must not rely on Drupal field names.

## Single Resource Response

Example response shape:

    {
      "data": {},
      "meta": {
        "siteKey": "growbig",
        "language": "en",
        "resolvedBy": "api_domain",
        "generatedAt": "2026-07-15T00:00:00Z"
      },
      "cache": {
        "maxAge": 3600,
        "tags": [],
        "contexts": []
      }
    }

## List Response

Example list response:

    {
      "items": [],
      "pagination": {
        "limit": 20,
        "offset": 0,
        "total": 0
      },
      "meta": {
        "siteKey": "growbig",
        "language": "en"
      },
      "cache": {
        "maxAge": 3600,
        "tags": [],
        "contexts": []
      }
    }

## Error Response

Example error response:

    {
      "error": {
        "code": "page_not_found",
        "message": "Page not found.",
        "details": {
          "siteKey": "growbig",
          "slug": "about",
          "language": "en"
        }
      }
    }

## Page Response

Example page response:

    {
      "data": {
        "id": "growbig:en:home",
        "siteKey": "growbig",
        "language": "en",
        "slug": "home",
        "path": "/",
        "title": "Home",
        "seo": {},
        "menus": {},
        "components": []
      },
      "meta": {},
      "cache": {}
    }

## Component Response

Example component response:

    {
      "id": "component-key",
      "type": "Hero",
      "variant": "split",
      "props": {},
      "cache": {}
    }

## Menu Item Link Types

Menu items must support:

- page reference
- internal path
- external URL
- anchor
- nolink parent item

Example anchor menu item:

    {
      "label": "Services",
      "type": "anchor",
      "href": "#services"
    }
