# Phase 23: Quality and CI Readiness

## Goal

Add a fast quality layer for the Site Platform v2 backend without changing runtime behavior.

This phase adds:

- PHP syntax verification for custom modules and setup scripts
- shell syntax verification for setup scripts
- a GitHub Actions workflow for static checks
- a local verification script that can be run before pushing

## Local Script

    ./scripts/setup/verify-quality-ci-readiness.sh

## CI Workflow

    .github/workflows/site-platform-v2-backend-checks.yml

The workflow intentionally avoids database-dependent DDEV checks. Full runtime checks remain local through the existing release-readiness scripts.

## Safety

This phase does not delete data and does not change API behavior.
