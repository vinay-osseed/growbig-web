# Phase 8D: Careers and Contact Pages

## Purpose

Add top-level dynamic pages for Careers and Contact.

## Added Pages

- careers
- contact

## Careers Page

The Careers page includes:

- hero section
- jobs content list section

The jobs list is loaded from:

- GET /api/v1/content/jobs

## Contact Page

The Contact page includes:

- hero section
- CTA section pointing frontend toward the Contact Us form API

Contact form metadata is available from:

- GET /api/v1/forms/contact-us

## Validation

Added smoke test coverage for:

- GET /api/v1/pages/careers
- GET /api/v1/pages/contact
- pages index includes careers and contact
- careers page includes jobs content list
