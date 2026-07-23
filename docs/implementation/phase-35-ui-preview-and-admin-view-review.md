# Phase 35: Actual UI Preview and Admin View Review

## Goal

Document what the current Site Platform v2 UI actually is, how to review it locally, and what is still missing before calling the editor/admin experience complete.

This phase is intentionally not a frontend build and does not deploy anything.

## Current UI Reality

The current branch is backend/API-first.

Available now:

- Drupal admin dashboard route for Site Platform status
- Drupal default content admin screens for Site Platform content entities
- Drupal Webform UI for real form management
- public JSON API endpoints for frontend integration

Not final yet:

- polished public frontend pages
- custom editor listings for each Site Platform object type
- final site-builder/editor workflow
- final production permissions
- final production Webform handlers

## Files

    docs/ui/current-actual-ui-and-view-report.md
    docs/ui/local-ui-review-checklist.md
    scripts/setup/verify-ui-preview-readiness.sh

## Main Script

    ./scripts/setup/verify-ui-preview-readiness.sh

## Expected Result

    UI preview readiness verification passed.

## Meaning

When this passes, the branch has a clear handoff for what the current UI looks like, where to click locally, and what still needs to be built for editor-facing polish.
