# Phase 10C: Role-Based Dashboard UI

## Purpose

Redesign the Drupal admin Site Dashboard to render backend-driven dashboard data.

## Updated Route

- /admin/site-dashboard

## Dashboard Sections

- intro
- overview counts
- role-aware quick action cards
- recent content updates

## Backend Data Source

The dashboard UI uses the same dashboard data provided by:

- GET /api/v1/admin/dashboard

## Verification

Added:

- scripts/check-dashboard-ui.sh

The full backend verification script now checks both dashboard API data and dashboard UI render output.
