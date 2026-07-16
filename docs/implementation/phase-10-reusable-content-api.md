# Phase 10: Reusable Content API

## Goal

Add reusable site-scoped content blocks and expose them through read-only API endpoints.

## Content Type

This phase adds:

- Site Content Block

## Endpoints

    GET /api/v1/content
    GET /api/v1/content/{source}
    GET /api/v1/content/{source}/{key}

## Local Testing

Examples:

    /api/v1/content?site=growbig
    /api/v1/content/global?site=growbig
    /api/v1/content/global/footer_cta?site=growbig

## Stable Identity

Reusable content is addressed by:

    site key + source + content key

Examples:

    growbig + global + footer_cta
    growbig + global + announcement
    osseed + global + footer_cta

## Current Limits

This phase supports simple title, summary, body, source, key, variant, and weight fields.

It does not include:

- media/image fields
- scheduled publishing
- shared cross-site content
- content block permissions
- reusable component references

Those come later.
