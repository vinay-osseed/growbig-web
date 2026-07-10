# Phase 10D: Dashboard and Admin Access Documentation

## Purpose

Document role-based admin access and the backend-driven dashboard.

## Admin Dashboard

URL:

- /admin/site-dashboard

The dashboard is role-aware and backend-driven.

It displays:

- overview counts
- quick action cards
- recent content updates

## Dashboard Data Source

The dashboard UI uses the same backend dashboard data exposed by:

- GET /api/v1/admin/dashboard

This endpoint requires an authenticated user with admin access.

Do not test it with anonymous curl.

Use:

- ./scripts/check-dashboard-api.sh
- ./scripts/check-dashboard-ui.sh

## Roles

### Site Developer

Developer/platform role for configuration and advanced admin access.

### Content Admin

Manages:

- pages
- page-connected menus
- services
- partners
- team members
- jobs
- media

### HR Manager

Manages:

- jobs
- job application submissions

### Form Manager

Manages:

- contact form submissions
- job application submissions

### Analytics Viewer

Reserved for analytics/reporting dashboard access.

## Verification

Use:

- ./scripts/check-admin-roles.sh
- ./scripts/check-dashboard-api.sh
- ./scripts/check-dashboard-ui.sh
- ./scripts/verify-backend-mvp.sh
