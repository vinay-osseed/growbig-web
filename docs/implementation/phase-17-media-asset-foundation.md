# Phase 17: Media Asset Foundation

## Goal

Add a reusable, site-scoped media asset model and API without changing the frontend contract for existing pages, menus, content, or forms.

## New Module

    site_platform_media

## New Content Type

    Site Media Asset

Fields:

- Site Profile
- Media key
- Kind
- URL/reference
- Alt text
- Credit
- Weight
- Active flag
- Demo metadata

## New API Endpoints

    GET /api/v1/media
    GET /api/v1/media/{key}

## YAML Import

Setup YAML can now include:

    media_assets:
      - key: hero-image
        title: Hero Image
        kind: image
        url: /assets/demo/hero.jpg
        alt: Hero image

## Scope

This phase stores URL/reference metadata only.

Binary upload/import, image styles, remote downloads, CDN integration, and file replacement workflow can be added later without breaking the API shape.
