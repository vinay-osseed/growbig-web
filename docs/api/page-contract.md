# Dynamic Page API Contract

## Purpose

The Page API is the main API contract for the decoupled frontend.

The frontend should not hardcode homepage, about page, partner cards, team cards, or service cards.

Instead, the frontend should request a page by slug and render the returned sections dynamically.

## Main endpoint

- GET /api/v1/pages/{slug}

Examples:

- /api/v1/pages/home
- /api/v1/pages/about

## Frontend rendering flow

Frontend route:

- /
- /about
- /services
- /contact

Maps to API:

- /api/v1/pages/home
- /api/v1/pages/about
- /api/v1/pages/services
- /api/v1/pages/contact

The frontend should render by section type.

## Page response contract

Each page response includes:

- contractVersion
- id
- uuid
- type
- title
- slug
- pageType
- route
- api
- summary
- sites
- seo
- sections
- meta

## Route object

The route object tells the frontend where this page belongs.

Example:

- route.path: /
- route.apiPath: /api/v1/pages/home

For a standard page:

- route.path: /about
- route.apiPath: /api/v1/pages/about

## API object

The api object provides self-reference links.

Example:

- api.self: /api/v1/pages/about

## Section rendering

The frontend should switch by section type.

Supported section types currently include:

- hero
- stats
- cardGrid
- card
- cta
- imageText
- contentList

## Dynamic content lists

Content List sections are the main dynamic content bridge.

A Drupal editor can configure:

- source
- limit
- featuredOnly

Supported sources:

- services
- partners
- team

The API populates:

- items

This means the frontend renders the section and its items without hardcoding the business content.

## Helper APIs

These endpoints are helper APIs, not the primary page-rendering contract:

- /api/v1/content/{source}
- /api/v1/content/{source}/{key}

Use them for listing pages or detail pages only when needed.

The main frontend page flow should use:

- /api/v1/pages/{slug}
