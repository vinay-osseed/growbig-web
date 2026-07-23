# Phase 27: Runtime API Smoke Check

## Goal

Add one focused runtime smoke test for the Site Platform v2 backend API surface.

This phase confirms the current local/DDEV backend can respond through the public API routes after setup import.

## Main Script

    ./scripts/setup/verify-runtime-api-smoke.sh

## What It Checks

The smoke test verifies:

- site resolution
- pages API
- page detail API
- route resolver API
- menu API
- Webform-backed form schema API
- Webform-backed form submission API
- content API
- media API
- SEO API
- analytics API
- search API

## Safety

This script imports the sample setup YAML and submits one test form payload to the Webform-backed endpoint.

It does not delete records.
