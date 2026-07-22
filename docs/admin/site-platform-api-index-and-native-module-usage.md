# Site Platform API Index and Native Module Usage

This update uses the Drupal-native stack already enabled on the project and improves the frontend API shape.

## Enabled contrib/core modules to use

The platform should use these modules as the primary backend feature providers:

- Domain, Domain Alias, Domain Configuration, Domain Content, and Domain Source for multi-site separation and site/domain resolution.
- Drupal Menu UI and menu link content for navigation.
- Webform, Webform UI, Webform Access, Webform Node, Webform Schema, and related Webform modules for forms.
- Media Library for images, files, logos, icons, and reusable assets.
- Metatag and Metatag Favicons for SEO and favicons.
- Redirect and Redirect Domain for URL/domain redirects.
- Pathauto for aliases.
- Simple XML Sitemap for sitemap output.
- REST, JSON:API, HTTP Basic Auth, and Decoupled Router as available Drupal API tools.

Custom Site Platform code should only normalize and package data for frontend use.

## New page index API

Frontend can now request all published pages for a site:

```text
/api/v2/pages?site=growbig
```

This returns a summary list:

- site key and label
- count
- page id, title, key, slug, path
- SEO summary
- section count
- changed timestamp
- detail API URL

For small sites or static builds, a full index can include sections:

```text
/api/v2/pages?site=growbig&include=full
```

For detail views, request one page:

```text
/api/v2/pages/home?site=growbig
```

## Other index APIs

The API also exposes indexes for forms and flexible datasets:

```text
/api/v2/forms?site=growbig
/api/v2/datasets?site=growbig
```

Then detail endpoints can be called only when needed:

```text
/api/v2/forms/contact?site=growbig
/api/v2/datasets/plans?site=growbig
```

## Frontend request pattern

Recommended frontend request shape:

1. App/layout boot:

```text
/api/v2/bootstrap?site=growbig
```

2. Page routing/index or static generation:

```text
/api/v2/pages?site=growbig
```

3. Current page detail:

```text
/api/v2/pages/home?site=growbig
```

4. Optional special data:

```text
/api/v2/datasets/plans?site=isp
/api/v2/forms/contact?site=growbig
```

This avoids many small requests while still keeping detail endpoints available.
