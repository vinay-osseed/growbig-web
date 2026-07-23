# Phase 07: Menu Model and Menu API

## Goal

Add editable site-scoped menus and read-only menu API endpoints.

## Content Types

This phase adds:

- Site Menu
- Site Menu Item

## Endpoints

    GET /api/v1/menus
    GET /api/v1/menus/{menu}

## Menu Item Link Types

Menu items can use:

- page
- path
- external
- nolink

## Local Testing

Examples:

    /api/v1/menus?site=growbig
    /api/v1/menus/main?site=growbig
    /api/v1/menus/main?site=osseed

## Current Limits

This phase returns menu items as a flat ordered list with `parentKey`.

Nested tree output can be added later once the admin model is stable.

This phase does not include:

- role-based menu visibility
- multilingual fallback
- menu item icons
- advanced active-trail logic
