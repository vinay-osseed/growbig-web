# Phase 21: Admin and Permission Readiness

## Goal

Add a lightweight Site Platform admin entry point and permission foundation so backend records are easier to review without treating the normal Drupal content list as the final editor experience.

## New Module

    site_platform_admin

## New Admin Route

    /admin/site-platform

## New Permissions

    administer site platform
    manage site platform content
    view site platform reports

## What the Admin Page Shows

The first admin page is intentionally simple and safe. It shows:

- enabled Site Platform modules
- setup status summary
- managed entity counts
- current API endpoint groups

## Scope

This phase does not delete data.

It does not replace the final content editing UI. It gives a clear backend dashboard and permission foundation for the current deadline. Later admin UX phases can add custom listings, filters, forms, and editor workflows.
