# Production Prep Checklist

This checklist captures the remaining work after the local/DDEV backend deadline checkpoint.

Stage deployment is intentionally skipped for now.

## Before Any Production Deployment

- Confirm target production branch and release tag strategy.
- Confirm production URL, admin URL, and API URL.
- Confirm rollback process and backup location.
- Confirm production database owner and access path.
- Confirm deployment window and responsible person.
- Confirm who approves content, forms, and permissions.

## Backend Readiness

- Review API contract documentation.
- Review final backend handoff documentation.
- Confirm production environment settings.
- Confirm config split policy.
- Confirm public/private API access policy.
- Confirm cache strategy and invalidation needs.

## Content Readiness

- Prepare real GrowBig YAML.
- Prepare real OSSeed YAML.
- Replace demo copy, demo media metadata, and placeholder URLs.
- Confirm menus, page slugs, route paths, and reusable content keys.
- Confirm SEO titles, descriptions, canonical paths, and indexing policy.

## Forms Readiness

- Confirm Webform IDs.
- Configure production email handlers.
- Configure spam protection.
- Confirm submission retention/export workflow.
- Confirm file upload rules if forms need uploads.

## Access Readiness

- Audit admin/editor roles.
- Confirm who can manage Site Platform content.
- Confirm who can view reports.
- Confirm who can administer Webforms.
- Confirm anonymous API access expectations.

## Media Readiness

- Confirm whether media files stay local, move to CDN, or use external URLs.
- Confirm file replacement workflow.
- Confirm image styles and PDF/file handling.
- Confirm cache headers for public assets.

## Final Go/No-Go Items

- Final client/editor QA.
- Final security review.
- Final backup and rollback confirmation.
- Final production deployment approval.
