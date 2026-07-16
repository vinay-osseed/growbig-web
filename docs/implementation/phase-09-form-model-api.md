# Phase 09: Form Model and Form API

## Goal

Add a basic site-scoped form model and the first form API endpoints.

## Content Types

This phase adds:

- Site Form
- Site Form Field
- Site Form Submission

## Endpoints

    GET /api/v1/forms/{form}
    POST /api/v1/forms/{form}/submit

## Local Testing

Examples:

    /api/v1/forms/contact?site=growbig
    /api/v1/forms/contact/submit?site=growbig

## Submission Behavior

Submissions are stored as Site Form Submission nodes.

The submitted payload is stored as JSON in:

    field_submission_payload

## Current Limits

This phase supports a simple platform-owned form model.

It does not include:

- Webform integration
- email notifications
- spam protection
- file uploads
- CRM integrations
- field options validation
- advanced conditional logic

Those come later.
