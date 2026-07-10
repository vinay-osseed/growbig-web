# Phase 7E: Pages Index API

## Purpose

Add a lightweight index endpoint so the decoupled frontend can discover published dynamic pages.

## Endpoint

- GET /api/v1/pages

## Response

The endpoint returns:

- contractVersion
- count
- items

Each item includes:

- id
- uuid
- title
- slug
- pageType
- route.path
- route.apiPath
- api.self

## Why this matters

The frontend can discover available dynamic pages without hardcoding every route.

The primary page rendering endpoint remains:

- GET /api/v1/pages/{slug}

## Validation

Added smoke test coverage for:

- contractVersion
- home page exists
- about page exists
- route.path values
- route.apiPath values
