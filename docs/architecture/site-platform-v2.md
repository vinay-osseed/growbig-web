# Site Platform v2 Architecture

## Purpose

Site Platform is a Drupal 11+ backend platform for API-driven websites.

It provides one reusable Drupal codebase that can support many isolated companies, sites, domains, APIs, and frontends.

## Core Principle

Drupal stores and manages content.

Site Platform APIs expose clean, framework-neutral frontend contracts.

Frontend developers should not depend on raw Drupal field names or Drupal entity structures.

## Multi-site Model

One Drupal codebase can support many isolated site profiles.

Example domains:

    admin.growbigllp.com
    api.growbigllp.com
    www.growbigllp.com

    admin.osseed.com
    api.osseed.com
    www.osseed.com

Each site can have isolated:

- domains
- branding
- pages
- menus
- components
- reusable content
- media
- forms
- analytics
- users
- permissions
- translations
- frontend API responses

## Site Isolation

Content is isolated by default.

Content can be shared only when it is explicitly assigned to multiple sites.

Public content must be:

- published
- active
- assigned to the resolved site
- available in the requested language
- allowed for public API output

## Domain Resolution

Every public API request must resolve a site context first.

Resolution order:

1. Exact API domain match.
2. Exact admin domain match.
3. Exact frontend/UI domain match.
4. Local-only site override.
5. Default site only for local/dev/bootstrap.

Production APIs must not silently fall back.

If no site is resolved, return this JSON shape:

    {
      "error": {
        "code": "site_not_resolved",
        "message": "No active site matched this request host."
      }
    }

## Public API Surface

Public frontend APIs:

- GET /api/v1/site
- GET /api/v1/routes/{path}
- GET /api/v1/pages
- GET /api/v1/pages/{slug}
- GET /api/v1/menus
- GET /api/v1/menus/{menu}
- GET /api/v1/content/{source}
- GET /api/v1/content/{source}/{key}
- GET /api/v1/forms/{form}
- POST /api/v1/forms/{form}/submit
- GET /api/v1/analytics/config
- GET /api/v1/search
- GET /api/v1/sitemap
- GET /api/v1/health

No general admin/internal API is required for v1.

Admin operations happen through Drupal admin UI.

## Setup Lifecycle

Setup must be idempotent.

It should create or update by stable keys, never by node IDs.

Stable keys include:

- site key
- page slug
- menu key
- menu item key
- component key
- reusable content key
- form ID
- content key

Setup must support:

- setup preview
- setup run
- setup completion
- demo content creation
- demo content reset
- site reset
- full reinstall during development

## Demo Content

Demo content is allowed in local and production, but it must be clearly marked and resettable.

Demo content fields:

- is_demo
- demo_source
- demo_group

Demo content must be removable without damaging real content.

## Multilingual

Multilingual support is required from the start.

API identity should include:

- site key
- language
- slug/path/key

API responses should expose:

- current language
- default language
- available translations
- fallback status

## Cache

Every API response must include correct cache metadata.

Cache behavior depends on:

- site
- host
- language
- route/page
- referenced content
- referenced media
- forms
- menus
- analytics config

## GrowBig Reference UI

GrowBig is one implementation of Site Platform.

The GrowBig mockup confirms the platform needs support for:

- sticky/header navigation
- dark hero sections
- hero image/device mockups
- hero slider
- primary and secondary buttons
- service cards
- partners/logo strip
- about/mission/vision blocks
- values cards
- team/leadership cards
- careers hero
- job filters
- open role cards
- careers CTA
- contact form
- contact info card
- social links
- footer menus

These must be modeled as backend components and normalized into frontend-safe API JSON.
