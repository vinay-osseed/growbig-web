# Site Platform Admin

Provides a custom Drupal admin dashboard for reusable site platform management.

## Dashboard

URL:

- /admin/site-dashboard

## Dashboard Sections

- Overview counts
- Role-aware quick action cards
- Recent content updates

## Backend Data

The dashboard uses backend dashboard data from:

- GET /api/v1/admin/dashboard

The API requires authenticated admin access.

## Roles

Supported role-aware dashboard behavior includes:

- Site Developer
- Content Admin
- HR Manager
- Form Manager
- Analytics Viewer

## Verification

Use:

- ./scripts/check-dashboard-api.sh
- ./scripts/check-dashboard-ui.sh
- ./scripts/check-admin-roles.sh
