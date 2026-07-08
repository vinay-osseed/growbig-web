# Site Platform API

This module exposes normalized frontend-ready JSON responses under `/api/v1`.

## Endpoints

- `/api/v1/site`
- `/api/v1/pages/{slug}`
- `/api/v1/content/{source}`

## Current examples

- `/api/v1/site`
- `/api/v1/pages/home`
- `/api/v1/pages/about`
- `/api/v1/content/services`
- `/api/v1/content/partners`
- `/api/v1/content/team`

## Supported content sources

- services
- partners
- team

## Query parameters for content lists

- `limit`
- `featuredOnly`

Examples:

- `/api/v1/content/services?limit=3`
- `/api/v1/content/partners?featuredOnly=1`
- `/api/v1/content/team?limit=4&featuredOnly=1`

## Purpose

The API hides raw Drupal entity structures and returns clean JSON for the decoupled frontend application.
