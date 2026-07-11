# Phase 9A: Page-Connected Menu API

## Purpose

Expose frontend header and footer menus from backend-managed Site Page fields.

The frontend should not hardcode header or footer navigation.

## Site Page Menu Fields

Added to site_page:

- field_show_in_header
- field_show_in_footer
- field_menu_title
- field_menu_weight

## Existing Page Defaults

- Home: header/footer, weight 0
- About: header/footer, weight 10
- Careers: header/footer, weight 20
- Contact: header/footer, weight 30

## API Endpoints

- GET /api/v1/menus
- GET /api/v1/menus/header
- GET /api/v1/menus/footer

## Frontend Usage

Frontend renders header and footer menus from API response.

Each menu item includes:

- title
- slug
- url
- weight
- apiPath

## Validation

Smoke tests verify:

- menu endpoints exist
- header includes Home, About, Careers, Contact
- footer includes Home, About, Careers, Contact
