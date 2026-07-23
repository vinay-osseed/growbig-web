# Site Platform Full Page Builder Completion

This completion pass turns the backend from a basic technical CMS into an editor-ready page builder for the GrowBig site pattern.

## What it supports

The Landing Page component selector now supports the common sections needed by the current GrowBig frontend and future landing pages:

- Hero / hero slider
- Section header
- Service / feature card grid
- Service / feature card item
- Partner / logo strip
- Partner / logo item
- Stats grid
- Stat item
- Image + text section
- CTA band
- Process / step list
- Step item
- Testimonial section
- Testimonial item
- FAQ section
- FAQ item
- Contact / office information section
- Webform embed section

## Editor order for a homepage

For the current GrowBig-style homepage, editors should start with this order:

1. Hero section
2. Section header for "Our Services"
3. Card grid for service cards
4. Section header for "Our Trusted Partners"
5. Partner/logo strip
6. CTA band or contact block
7. Footer/contact data from Site Profile and Content Blocks

## What this fixes

- Editors no longer need to guess which fields map to the frontend.
- Landing pages now have enough backend component types to describe the visible GrowBig homepage sections.
- The page form includes a Page Builder Guide explaining what to add first and how the frontend/API handoff works.
- Technical fields remain hidden from primary editor forms.
- Site Profile, Media Library, and structured social links from the earlier editor experience remain intact.

## What it avoids

This does not run a full Drupal config import/export:

```bash
ddev drush cim -y
ddev drush cex -y
```

It applies targeted active configuration through scripts and updates runtime admin code only.

## Final command

Run:

```bash
./scripts/setup/finalize-site-platform-full-page-builder.sh
```

Expected final line:

```text
Site Platform full page builder verification passed.
```
