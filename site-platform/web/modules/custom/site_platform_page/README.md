# Site Platform Page

## Purpose

Site Platform Page owns the page content model for API-driven frontend pages.

A Site Page belongs to one Site Profile and is identified by a stable page key, language, slug, and path.

## What This Module Adds

- Site Page content type
- Site Profile reference field
- stable page key field
- slug field
- path field
- template field
- SEO fields
- demo tracking fields
- weight field

## What This Module Does Not Add Yet

- No public page API
- No component/paragraph model
- No menu model
- No route resolver controller

Those come in later phases.

## Install

Run:

    ddev drush en site_platform_page -y
    ddev drush cr

## Verify

Run:

    ddev drush config:get node.type.site_page
    ddev drush config:get field.field.node.site_page.field_site_profile
    ddev drush config:get field.field.node.site_page.field_page_slug
