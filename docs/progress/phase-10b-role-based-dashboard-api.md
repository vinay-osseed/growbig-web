# Phase 10B: Role-Based Dashboard API

## Purpose

Start the backend-driven admin dashboard redesign.

The dashboard should show cards and data based on user role/access.

## Endpoint

- GET /api/v1/admin/dashboard

## Returned Data

- current user roles
- role-aware cards
- content counts
- form submission counts
- recent content updates

## Role-Aware Cards

Cards are generated for:

- site_developer
- content_admin
- hr_manager
- form_manager
- analytics_viewer

## Notes

This is the backend API foundation for dashboard redesign.

Frontend/dashboard UI should render cards from this API instead of hardcoding dashboard content.

## Verification Note

The dashboard API requires an authenticated admin user.

It should not be tested with anonymous curl.

Use:

- ./scripts/check-dashboard-api.sh
