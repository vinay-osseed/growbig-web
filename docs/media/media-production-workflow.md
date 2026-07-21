# Media Production Workflow

The current backend supports reusable media asset metadata. Production still needs a final media binary strategy.

## Open Decision

Choose one media file strategy:

1. Drupal-managed public files
2. CDN-backed public URLs
3. external asset URLs managed outside Drupal
4. hybrid approach for images and PDFs

## Required Fields To Confirm

For each reusable media asset:

- key
- label/title
- asset type
- URL or file reference
- alt text for images
- summary/description
- whether it is demo or production content

## Production Checklist

- replace demo URLs with real URLs
- confirm image dimensions and responsive needs
- confirm PDF/file download behavior
- confirm cache headers/CDN behavior
- confirm replacement process for updated assets
- confirm ownership of source files

## API Validation

After real media data is imported, verify:

    /api/v1/media?site=<site_key>
    /api/v1/media/<media_key>?site=<site_key>
