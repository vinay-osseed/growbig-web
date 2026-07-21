# Phase 31: Production Prep Without Stage Deployment

## Goal

Prepare the remaining production-readiness work without performing a stage deployment.

This phase keeps the current backend checkpoint safe and reviewable while documenting the launch tasks that still need real values, environment decisions, or client confirmation.

## Scope

Included:

- production readiness checklist
- real site YAML/content preparation checklist
- secrets and permission review checklist
- Webform production hardening checklist
- media workflow checklist
- editor/admin UX cleanup plan
- production deployment plan template without executing deployment
- verification script for these documents

Excluded:

- stage deployment
- production deployment
- database changes
- config import/export
- live environment changes

## Main Script

    ./scripts/setup/verify-production-prep-readiness.sh

## Expected Result

    Production prep readiness verification passed.

## Meaning

When this passes, the branch contains the remaining production-prep plan and can be reviewed without touching stage or production.
