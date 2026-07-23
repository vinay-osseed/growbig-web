# Phase 15: Site Reset Commands

## Goal

Add a safer site-specific reset command for setup-managed site data.

## Command

    site-platform:setup-reset-site SITE_KEY

## Dry Run

The command is dry-run by default.

Example:

    ddev drush site-platform:setup-reset-site yamltest

This prints the records that would be deleted, but does not delete anything.

## Execute

To delete one site and its setup-managed records:

    ddev drush site-platform:setup-reset-site yamltest --execute

## What It Deletes

For the selected Site Profile only, the reset command deletes:

- Site Form Submissions
- Site Form Fields
- Site Forms
- Site Menu Items
- Site Menus
- Site Content Blocks
- Site Pages
- Page component Paragraphs referenced by those pages
- Site Profile

## Safety Rules

The command resolves the target by `field_site_key`.

It does not delete other Site Profiles.

It is dry-run unless `--execute` is explicitly passed.

## Current Limits

This command resets setup-managed records only.

It does not yet reset:

- media/image files
- users and roles
- config
- translations
- non-setup contrib content

Those can be added in later cleanup phases.
