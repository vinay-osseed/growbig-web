# Webform Production Drafts

This document prepares Webform production settings for the draft real site YAML files.

## Draft Files

    setup/forms/growbig.webforms.yml
    setup/forms/osseed.webforms.yml

These files are planning drafts only. Do not import them into stage or production until all TODO values are replaced and approved.

## Required Before Launch

For each Webform:

- final Webform machine name
- final form title and description
- final field list and validation rules
- final recipient email address or distribution list
- final from/reply-to behavior
- final confirmation message or redirect
- spam protection decision
- submission retention/export policy
- file upload policy
- permission review

## Webform Handler Rule

Email recipients and handler details must be configured in Drupal Webform UI or approved config. Do not store private recipient addresses or API secrets in public setup drafts.

## Current Drafts

The current drafts prepare:

- GrowBig contact Webform
- OSSeed contact Webform

More Webform drafts can be added later for lead, inquiry, newsletter, career, and job application forms.

## Validation

Run:

    ./scripts/setup/verify-webform-production-drafts.sh

Expected:

    Webform production draft verification passed.
