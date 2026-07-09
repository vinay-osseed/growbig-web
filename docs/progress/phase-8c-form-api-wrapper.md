# Phase 8C: Form API Wrapper

## Purpose

Expose selected Webforms through stable decoupled API endpoints.

## Forms

Supported public forms:

- contact-us maps to contact_us
- job-application maps to job_application

## Endpoints

- GET /api/v1/forms/contact-us
- POST /api/v1/forms/contact-us/submit
- GET /api/v1/forms/job-application
- POST /api/v1/forms/job-application/submit

## Contact Form

The Contact Us form includes optional budget_range.

Frontend can render budget_range only where needed.

## Job Application Form

Job applications are submitted to Webform.

Resume upload is present in metadata but marked as not API-supported yet.

File upload support should be handled separately.

## Validation

The API wrapper validates required public fields before creating a Webform submission.

## Notes

This API intentionally exposes only approved public forms.
