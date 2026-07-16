# Phase 02: Site Profile Model

## Goal

Add the Site Profile content model.

A Site Profile is the root entity for one isolated company/site/domain inside Site Platform.

## Site Profile Responsibilities

A Site Profile stores:

- site key
- active/default status
- domain mappings
- default language
- enabled languages
- timezone
- branding values
- contact values
- analytics values

## Domain Fields

The model supports:

- frontend domains
- API domains
- admin domains

Examples:

    www.growbigllp.com
    api.growbigllp.com
    admin.growbigllp.com

and:

    www.osseed.com
    api.osseed.com
    admin.osseed.com

## Stable Identity

Site key is the stable machine identity.

Example:

    growbig
    osseed

Node IDs must never be used as public identity.

## What This Phase Does Not Include

- No public APIs
- No setup runner
- No page model
- No menu model
- No content isolation fields
- No entity-based resolver

Those come next.
