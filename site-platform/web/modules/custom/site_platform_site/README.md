# Site Platform Site

## Purpose

Site Platform Site owns the Site Profile content model.

A Site Profile represents one isolated website/company/brand inside the shared Site Platform codebase.

Examples:

- GrowBig Technologies LLP
- OSSeed Technologies LLP
- any future client/company/site

## What This Module Adds

- Site Profile content type
- site key field
- domain fields
- language fields
- branding fields
- contact fields
- analytics fields
- active/default flags

## What This Module Does Not Add Yet

- No public API controllers
- No entity-based resolver
- No setup runner
- No page/content assignment model

Those come in later phases.

## Install

Run:

    ddev drush en site_platform_site -y
    ddev drush cr

## Verify

Run:

    ddev drush config:get node.type.site_profile
    ddev drush config:get field.field.node.site_profile.field_site_key
