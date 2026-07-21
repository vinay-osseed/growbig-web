# Phase 33: Webform Production Prep Drafts

## Goal

Prepare Webform production-readiness drafts for GrowBig and OSSeed without creating, importing, or changing real Webforms in any environment.

## Scope

Included:

- GrowBig Webform production-prep draft
- OSSeed Webform production-prep draft
- Webform production implementation checklist
- verification script for required draft safety markers

Excluded:

- stage deployment
- production deployment
- Webform creation in Drupal
- email handler creation in Drupal
- recipient secrets or private addresses
- spam protection configuration changes

## Files

    setup/forms/growbig.webforms.yml
    setup/forms/osseed.webforms.yml
    docs/forms/webform-production-drafts.md
    scripts/setup/verify-webform-production-drafts.sh

## Main Script

    ./scripts/setup/verify-webform-production-drafts.sh

## Expected Result

    Webform production draft verification passed.

## Meaning

When this passes, the branch contains safe Webform production-prep drafts that can be filled with approved recipients, fields, confirmation copy, and spam settings later.
