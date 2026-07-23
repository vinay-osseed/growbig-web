# Phase 04: Site Page Model

## Goal

Add the Site Page content model.

A Site Page represents one frontend route/page for one Site Profile.

## Site Page Responsibilities

A Site Page stores:

- owning Site Profile
- stable page key
- slug
- path
- template
- SEO title
- SEO description
- demo tracking fields
- sort weight

## Stable Identity

The stable page identity is:

    site key + language + page key

Example:

    growbig + en + home
    growbig + en + about
    osseed + en + home

Node IDs must never be used as public identity.

## Slug and Path

Slug is the stable URL key.

Examples:

    home
    about
    services
    careers
    contact

Path is the frontend path.

Examples:

    /
    /about
    /services
    /careers
    /contact

## Demo Content

Demo page records can be marked with:

- is demo
- demo source

This lets setup create safe demo pages that can be reset later.

## What This Phase Does Not Include

- No public page API
- No component/paragraph model
- No menu model
- No route resolver controller
- No site access filtering yet

Those come next.
