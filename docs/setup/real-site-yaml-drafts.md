# Real Site YAML Drafts

This folder contains draft site YAML files for the two first real Site Platform profiles:

    setup/sites/growbig.site.yml
    setup/sites/osseed.site.yml

## Important Safety Rule

These files are planning drafts. Do not import them into stage or production until all TODO values are replaced and approved.

## What To Fill Next

For each site, confirm and replace:

- frontend, API, and admin domains
- final page titles and route paths
- final menu labels and item order
- final Webform machine names
- real Webform handlers and recipient emails in Drupal UI
- real reusable content copy
- real media URLs or file references
- image alt text
- SEO title, description, canonical path, and robots value
- analytics IDs if analytics is enabled

## Webform Rule

Contact, inquiry, lead, job application, and other real forms should use Webform as the primary storage and handler system.

The Site Platform form entry is only the site-scoped API wrapper that points to the real Webform.

## Media Rule

The drafts use URL placeholders. Before launch, choose one approved media strategy:

- Drupal-managed public files
- CDN-backed URLs
- external asset URLs
- hybrid image/PDF strategy

## Analytics Rule

Analytics is disabled in the drafts. This avoids committing placeholder production IDs.

Turn analytics on only when the final approved GA/GTM values are available.

## Local Validation After Content Is Filled

After replacing TODO values locally, validate the import with:

    ddev drush site-platform:setup-import /var/www/html/setup/sites/growbig.site.yml
    ddev drush site-platform:setup-import /var/www/html/setup/sites/osseed.site.yml

Then run API smoke checks for each site key.
