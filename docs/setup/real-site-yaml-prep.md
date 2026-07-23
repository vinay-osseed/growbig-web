# Real Site YAML Preparation

The sample YAML is only for local smoke testing. Production launch needs real YAML files for each site profile.

## Target Files

Recommended structure:

    setup/sites/growbig.site.yml
    setup/sites/osseed.site.yml

These files should be created only after final content and URLs are confirmed.

## Required Sections

Each real site YAML should define:

- site profile key, name, and domains
- pages and paths
- menus and menu items
- Webform-backed form references
- reusable content blocks
- reusable media asset metadata
- SEO metadata
- analytics configuration
- search-visible content/media settings

## Content Rules

- Do not keep demo keys or demo copy unless intentionally approved.
- Keep route paths stable before frontend integration.
- Keep content keys machine-friendly and stable.
- Use Webform-backed forms for contact, inquiry, lead, job application, and similar forms.
- Avoid environment-specific secrets in YAML.
- Keep analytics IDs blank until real production values are approved.

## Validation Steps

After real YAML exists, validate locally with:

    ddev drush site-platform:setup-import /var/www/html/setup/sites/growbig.site.yml
    ddev drush site-platform:setup-import /var/www/html/setup/sites/osseed.site.yml

Then test core endpoints with the relevant site key.

## Open Decisions

- Final GrowBig domain values.
- Final OSSeed domain values.
- Final analytics IDs.
- Final form recipients.
- Final media file locations.
