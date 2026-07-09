# Phase 8B: Webforms

## Purpose

Add top-level Webforms for job applications and contact enquiries.

## Added Webforms

- job_application
- contact_us

## Job Application Webform

Machine name:

- job_application

Purpose:

- Store candidate submissions for open positions.

Important fields:

- job_key
- job_title
- name
- email
- phone
- current_location
- experience_years
- current_company
- resume_upload
- portfolio_url
- linkedin_url
- message
- consent

## Contact Us Webform

Machine name:

- contact_us

Purpose:

- Store general contact and project enquiries.

Important fields:

- name
- email
- phone
- company
- subject
- service_interest
- preferred_contact_method
- budget_range
- message
- consent

## Budget Range

The budget_range field is optional.

Frontend can render it only where needed.

## Notes

Job openings are stored as Job content nodes.

Job applications are stored as Webform submissions.

The default Webform example contact form is not used.
