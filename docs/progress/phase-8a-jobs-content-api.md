# Phase 8A: Jobs Content API

## Purpose

Add reusable job openings to the backend platform.

Jobs are stored as Drupal content and exposed through the existing reusable content API pattern.

## Content Type

- job

## Main Fields

- field_job_key
- field_summary
- field_department
- field_location
- field_employment_type
- field_experience_level
- field_salary_range
- field_description
- field_responsibilities
- field_requirements
- field_display_order
- field_is_featured
- field_is_active
- field_closing_date

## API Endpoints

- GET /api/v1/content/jobs
- GET /api/v1/content/jobs/{key}

Example:

- GET /api/v1/content/jobs/frontend-developer

## Notes

Job applications are not stored as job nodes.

Applications will be handled separately through a Job Application Webform in the next phase.

## Validation

Added smoke test coverage for:

- jobs list endpoint
- frontend-developer detail endpoint
- jobs support in backend MVP verification script
