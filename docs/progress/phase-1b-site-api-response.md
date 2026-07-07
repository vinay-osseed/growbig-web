# Phase 1B: Site API Response

## Purpose

Expose the extended Site Profile fields through the frontend-ready /api/v1/site endpoint.

## Scope completed

Updated the Site Profile API response to include:

- Tagline
- Description
- Website domain
- Expanded contact data
- Alternate phone
- City
- State
- Country
- Postal code
- Working hours
- Google map URL
- Map latitude
- Map longitude
- Social links
- Footer description
- Footer copyright
- Footer menu machine names

## API endpoint

- GET /api/v1/site

## Response sections

The endpoint now returns:

- id
- type
- name
- shortName
- tagline
- description
- domains
- branding
- contact
- seo
- social
- theme
- footer
- meta

## Testing completed

The following checks were completed:

- Custom Drupal code check passed.
- Drupal cache rebuild completed.
- /api/v1/site returned valid JSON.
- Social links are ordered by display order.
- Inactive social links are excluded.
- Map coordinates are returned as numbers.
- Empty coordinates return null.

## OpenAPI status

The OpenAPI schema was updated in Phase 1C to match the new /api/v1/site response structure.

## Next phase

Next planned backend phase:

- Phase 2: Site admin dashboard for content admins.
