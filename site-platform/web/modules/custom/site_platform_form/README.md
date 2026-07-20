# Site Platform Form

Site Platform Form contains the legacy/simple form wrapper content types from Phase 09.

Real production forms must be backed by Drupal Webform.

Use Webform for:

- contact forms
- job application forms
- inquiry/lead forms
- forms with email handlers
- forms with conditional logic
- forms with file uploads
- forms that need exports and admin review

The public API may continue to expose stable `/api/v1/forms/{form}` and `/api/v1/forms/{form}/submit` endpoints, but Webform is now the primary storage for real form schemas and submissions.

The legacy `site_form_submission` node type remains only as a backward-compatible fallback. New Webform-backed submissions must be saved as `webform_submission` entities, not nodes.
