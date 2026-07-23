# Site Platform Final Operator Checklist

Use this checklist after Phase 36C and Phase 37 are applied.

## Admin Workspace

Open:

```text
/admin/site-platform
```

Confirm the page shows:

- Recommended workflow
- Workspace sections
- Setup status
- Backend capability status
- Managed record counts
- Frontend API groups

## CRUD Sections

Open each section and confirm the table includes clickable actions:

```text
/admin/site-platform/sites
/admin/site-platform/pages
/admin/site-platform/content-blocks
/admin/site-platform/media-assets
/admin/site-platform/forms
/admin/site-platform/menus
/admin/site-platform/legacy-wrappers
```

Expected actions:

- Site/Profile/Page/Content/Media records have View, Edit, and Delete links.
- Sections have Add links where creation is supported.
- Webforms have Manage, View, Results, Settings, and Delete links.
- Menus link to Drupal core menu administration.
- Media section links to Drupal file administration.

## API Smoke URLs

The final verifier checks these public API URLs against the local DDEV site:

```text
/api/v1/site?site=yamltest
/api/v1/pages?site=yamltest
/api/v1/menus?site=yamltest
/api/v1/forms/contact?site=yamltest
/api/v1/content?site=yamltest
/api/v1/media?site=yamltest
/api/v1/seo?site=yamltest
/api/v1/analytics?site=yamltest
/api/v1/search?site=yamltest&q=brochure
```

## Config Safety

Do not run config import/export for this closeout unless you intentionally decide to reconcile the entire active Drupal config later.

Do not run:

```bash
ddev drush cim -y
ddev drush cex -y
```

## Current Closeout Scope

This closes the current backend/admin checkpoint:

- API-first backend foundation
- Webform-backed forms
- admin workspace with clickable CRUD paths
- safe runtime-only admin changes
- final verification script

Longer-term production hardening can continue separately without blocking this checkpoint.
