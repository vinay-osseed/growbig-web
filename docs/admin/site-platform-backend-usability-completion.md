# Site Platform Backend Usability Completion

This package finishes the current backend/admin usability pass without running a full Drupal config import/export.

## What it fixes

- Applies active edit-form displays for Site Platform content and Paragraph bundles so editors can actually create and edit pages, site profiles, content blocks, media metadata, menu wrappers, and form wrappers.
- Applies active view displays so View links show meaningful backend data instead of title-only records.
- Relabels technical bundles in active config so the admin UI uses editor-facing names.
- Adds admin workspace CSS so CRUD links are easier to scan and use.
- Removes local demo/test data that was marked as demo or belongs to the `yamltest` setup seed.
- Verifies the backend can render admin routes, CRUD links, add forms, active display config, Webform availability, and API routes after cleanup.

## What it avoids

This does not run:

```bash
ddev drush cim -y
ddev drush cex -y
```

It mutates only the specific active Drupal config needed for backend usability. It does not import the whole `config/sync` directory.

## Demo data cleanup

The cleanup removes only records that are explicitly demo/test platform records:

- nodes with `field_is_demo = 1`
- nodes with a non-empty `field_demo_source`
- nodes referencing a demo Site Profile
- the `yamltest` Site Profile
- Webforms whose ID or label contains `yaml` or `test`

A JSON backup summary is written under:

```text
var/site-platform-demo-cleanup-*.json
```

## Final command

Run:

```bash
./scripts/setup/finalize-backend-admin-usability.sh
```

Expected final line:

```text
Backend admin usability verification passed.
```
