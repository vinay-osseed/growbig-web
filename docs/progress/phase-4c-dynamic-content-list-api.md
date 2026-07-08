# Phase 4C: Dynamic Content List API

## Purpose

Populate Page API Content List sections from reusable Drupal content.

## Scope completed

Added service:

- site_platform_api.content_list_normalizer

Added class:

- Drupal\site_platform_api\SitePlatformContentListNormalizer

Updated:

- Drupal\site_platform_api\SitePlatformPageNormalizer

Content List sections now include an items array populated from Drupal content.

## Supported sources

- services
- partners
- team

## Current demo usage

Home page:

- Partners Content List Section loads Partner content

About page:

- Leadership Content List Section loads Team Member content

## Notes

Service dynamic lists are supported by the normalizer but are not currently used by the demo Home page because the Services section is still represented as card items.

## Next phase

Phase 4D:

- Update OpenAPI docs for dynamic content list items
- Update API smoke test to validate contentList items
