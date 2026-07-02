# Frontend API Strategy

The frontend should consume clean backend APIs from Drupal, not raw Drupal-shaped entity responses.

Drupal JSON:API remains enabled for debugging, internal testing, and fallback access, but the main frontend should use custom normalized endpoints.

## API goal

The API should be:

- clean
- predictable
- frontend-ready
- stable even if Drupal fields change internally
- complete enough for UI rendering
- simple for frontend developers to consume

## Main API namespace

~~~text
/api/v1
~~~

## Planned endpoints

~~~text
/api/v1/site
/api/v1/navigation/main
/api/v1/page/home
/api/v1/page/{slug}
/api/v1/pages
/api/v1/services
/api/v1/services/{slug}
/api/v1/industries
/api/v1/industries/{slug}
/api/v1/case-studies
/api/v1/case-studies/{slug}
/api/v1/testimonials
/api/v1/faqs
/api/v1/media/{uuid}
~~~

## Response shape

All content responses should follow this general shape:

~~~json
{
  "id": "uuid",
  "type": "page",
  "title": "Page title",
  "slug": "/page-url",
  "status": true,
  "language": "en",
  "created": "2026-07-02T00:00:00+00:00",
  "updated": "2026-07-02T00:00:00+00:00",
  "seo": {},
  "media": {},
  "components": [],
  "links": {},
  "meta": {}
}
~~~

## Media response shape

Media should always be returned in a frontend-ready format:

~~~json
{
  "id": "uuid",
  "type": "image",
  "title": "Image title",
  "alt": "Image alt text",
  "url": "https://admin.growbigllp.com/sites/default/files/image.jpg",
  "mime": "image/jpeg",
  "width": 1920,
  "height": 1080,
  "styles": {
    "thumbnail": "...",
    "card": "...",
    "hero_desktop": "...",
    "hero_mobile": "..."
  }
}
~~~

## Component response shape

Page builder/paragraph data should be returned as simple component objects:

~~~json
{
  "type": "hero",
  "id": "uuid",
  "title": "Hero title",
  "summary": "Hero summary",
  "image": {},
  "cta": {
    "label": "Contact us",
    "url": "/contact"
  }
}
~~~

## Frontend rules

Frontend should not need to understand Drupal internals such as:

- entity IDs
- field machine names
- relationships/included JSON:API nesting
- file entity references
- image style path generation
- paragraph internals

The Drupal backend should normalize these details before sending data to the frontend.

## Backend implementation

Create a custom module:

~~~text
site_platform_api
~~~

This module will provide:

- custom routes under `/api/v1`
- response normalizers
- media URL builders
- image style URL builders
- page/component transformers
- menu transformers
- SEO/meta transformers
