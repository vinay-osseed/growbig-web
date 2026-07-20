# Phase 28: Editor UX Readiness Notes

## Goal

Document the remaining editor/admin UX work clearly so the deadline backend can be closed without pretending the Drupal editorial UI is final.

## Current State

The backend stores Site Platform records as Drupal content entities. This is correct for the API-first foundation, but the normal Drupal content list is not the final editor workflow.

## Remaining Editor UX Work

Later production hardening should add:

- custom admin listings for Site Profiles, Pages, Menus, Content Blocks, Media Assets, and Webform-backed forms
- better manage form display settings
- better manage display settings
- filters by Site Profile
- clear labels for legacy form wrapper records
- hiding or separating internal/demo/setup records from normal editorial content
- stronger permissions per role

## Deadline Decision

For this deadline, the admin dashboard and permission foundation are enough to review backend status. Final editor UX polish remains a post-deadline production-hardening item.
