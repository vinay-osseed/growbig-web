# Webform Production Hardening

Webform is the primary real form system for Site Platform forms.

## Required Production Tasks

For each real form:

- confirm Webform machine name
- confirm fields and required validation
- configure email handlers
- configure recipient addresses
- configure confirmation message/page
- configure spam protection
- confirm submission access permissions
- confirm export/retention workflow
- confirm whether uploads are allowed

## Forms Expected To Use Webform

Use Webform for:

- contact forms
- inquiry/lead forms
- job application forms
- newsletter or signup forms
- any advanced forms requiring validation, handlers, or exports

## Do Not Treat As Final

The legacy/custom Site Form wrapper is not the final submission storage system when a matching Webform exists. It is only a site-scoped API wrapper/fallback.

## Testing Checklist

For each form:

- GET form schema endpoint returns storage `webform`
- POST submit endpoint creates Webform submission
- required validation works
- email handler sends to the approved address
- spam protection is enabled and does not block valid submissions
