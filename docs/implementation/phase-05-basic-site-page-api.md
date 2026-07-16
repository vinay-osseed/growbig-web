# Phase 05: Basic Site Page API

## Goal

Add the first public read-only JSON API endpoints.

## Endpoints

    GET /api/v1/site
    GET /api/v1/pages
    GET /api/v1/pages/{slug}

## Local Testing

For local development, use the site query override:

    /api/v1/site?site=growbig
    /api/v1/pages?site=growbig
    /api/v1/pages/home?site=growbig

The site query override is disabled in production by the resolver.

## Response Shape

Success:

    data
    meta
    cache

Error:

    error

## Current Limits

This phase only returns Site Profile and Site Page data.

It does not include:

- component fields
- route alias resolving
- menus
- forms
- reusable content
- multilingual lookup fallback
- full Drupal cache metadata headers

Those come later.
