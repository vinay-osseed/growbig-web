# Site Platform Drupal-Native Platform Rebuild

This package moves Site Platform toward a Drupal-native, multi-site, extensible backend.

## Direction

Use Drupal/contrib first:

- Domain / Domain Access for site separation.
- Drupal core Menu UI and menu link content for navigation.
- Webform for all forms.
- Media Library for images/files/logos/icons.
- Layout Paragraphs / Paragraphs for visual page sections.
- Pathauto for aliases.
- Metatag for SEO.
- Redirect for redirects.
- Simple XML Sitemap for sitemap output.
- Tour for editor onboarding.
- Custom code only for normalized APIs, editor guide pages, and lightweight glue.

## New flexible data holder

Adds a generic `Site Data Set` content type and `Site Data Item` paragraph item so each site can have its own structured data without new custom modules every time.

Examples:

- ISP plans
- coverage areas
- store locations
- pricing tiers
- benefits
- team members
- partner lists
- product plans
- feature tables

The API exposes this through:

```text
/api/v2/datasets/{key}?site=SITE_KEY
```

## Clean API shape

The frontend should use a small number of high-value API calls:

```text
/api/v2/bootstrap?site=SITE_KEY
/api/v2/pages/{slug}?site=SITE_KEY
/api/v2/navigation?site=SITE_KEY&menu=main
/api/v2/forms/{webform_id}?site=SITE_KEY
/api/v2/datasets/{key}?site=SITE_KEY
/api/v2/careers?site=SITE_KEY
```

The page API returns page data, SEO, sections, and media references together.

## Legacy direction

Legacy menu/form wrapper nodes should not be the editor workflow. They can remain as compatibility records only until the old APIs/importers are fully retired.

