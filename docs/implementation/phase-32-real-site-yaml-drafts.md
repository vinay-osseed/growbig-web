# Phase 32: Real Site YAML Drafts

## Goal

Add draft real-site YAML files for GrowBig and OSSeed so production content preparation can continue without stage deployment.

## Scope

Included:

- draft GrowBig YAML file
- draft OSSeed YAML file
- YAML preparation notes
- verification script for required keys and placeholder safety

Excluded:

- stage deployment
- production deployment
- importing the draft YAML into any shared environment
- real secrets
- final approved content

## Files

    setup/sites/growbig.site.yml
    setup/sites/osseed.site.yml
    docs/setup/real-site-yaml-drafts.md
    scripts/setup/verify-real-site-yaml-drafts.sh

## Main Script

    ./scripts/setup/verify-real-site-yaml-drafts.sh

## Expected Result

    Real site YAML draft verification passed.

## Meaning

When this passes, the branch contains safe draft YAML files that can be filled with approved real content later.

These drafts are not final launch content until all TODO values are replaced and the Webform/media/SEO/analytics decisions are approved.
