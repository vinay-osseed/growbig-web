# Phase 09B: Webform-backed Forms

## Goal

Correct the Phase 09 form architecture so real forms use Drupal Webform.

The public API contract remains stable:

    GET  /api/v1/forms/{form}
    POST /api/v1/forms/{form}/submit

But the preferred backend storage is now:

    webform
    webform_submission

The previous node-based `site_form_submission` model remains only as a backward-compatible fallback.

## Why this change was needed

The Phase 09 custom form model was useful for proving the API foundation quickly, but real forms such as contact forms and job application forms need Webform features:

- admin submission review
- email handlers
- exports
- validation
- conditional logic
- file uploads
- spam protection
- mature form UI

## Behavior after this phase

When a matching Webform exists for the resolved site and form key, the API uses Webform first.

For site `growbig` and form key `contact`, the default Webform ID is:

    growbig_contact

For site `yamltest` and form key `contact`, the default Webform ID is:

    yamltest_contact

YAML can explicitly provide a Webform ID:

    forms:
      - key: contact
        storage: webform
        webformId: yamltest_contact
        title: YAML Contact Form

## Submission storage

New Webform-backed submissions are saved as:

    webform_submission

They are not saved as:

    site_form_submission nodes

## Legacy fallback

If no matching Webform exists, the API can still fall back to legacy `site_form` and `site_form_field` node data. This is only to avoid breaking existing local/demo checks while the platform moves to Webform-first forms.

## Verification

Run:

    ./scripts/setup/verify-form-model-api.sh

The verification confirms:

- Webform is installed and enabled
- the API returns a Webform-backed schema
- valid API submissions create `webform_submission` records
- valid API submissions do not create new `site_form_submission` nodes
- invalid submissions still return `validation_failed`
- missing forms still return `form_not_found`
