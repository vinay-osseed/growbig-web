# Phase 8E: Final Jobs, Webforms, Careers, and Contact Docs

## Purpose

Document the completed top-level Careers, Jobs, Contact, and Webform API features.

## Completed Features

- Jobs content type
- Reusable jobs API
- Careers page
- Contact page
- Job Application Webform
- Contact Us Webform
- Webform metadata API
- Webform JSON submit API

## Main Endpoints

Pages:

- GET /api/v1/pages/careers
- GET /api/v1/pages/contact

Jobs:

- GET /api/v1/content/jobs
- GET /api/v1/content/jobs/{key}

Forms:

- GET /api/v1/forms/contact-us
- POST /api/v1/forms/contact-us/submit
- GET /api/v1/forms/job-application
- POST /api/v1/forms/job-application/submit

## Validation

The backend verification and API smoke tests cover:

- Careers page route
- Contact page route
- Careers jobs content list
- Jobs list and detail APIs
- Contact form metadata
- Job application form metadata
- Contact form submission
- Job application form submission
