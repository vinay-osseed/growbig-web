# Phase 36B: Safe Admin Workspace Hotfix

## Goal

Reintroduce the Site Platform admin workspace safely after reverting the broken Phase 36 commit.

## What This Does

This phase adds runtime-only admin workspace pages under `/admin/site-platform/*`:

- `/admin/site-platform/sites`
- `/admin/site-platform/pages`
- `/admin/site-platform/content-blocks`
- `/admin/site-platform/media-assets`
- `/admin/site-platform/forms`
- `/admin/site-platform/menus`
- `/admin/site-platform/legacy-wrappers`

## What This Avoids

This phase intentionally avoids the risky parts from the reverted Phase 36 commit:

- no `drush cim`
- no `drush cex`
- no `site-platform/config/sync` changes
- no `config/install` files
- no active Drupal configuration mutation
- no `Url` objects or link render arrays inside table rows

## Why

The reverted Phase 36 implementation broke rendering because Drupal received an unexpected `Drupal\Core\Url` value while rendering table output. This hotfix uses only plain string values in table rows.

## Verification

Run:

```bash
./scripts/setup/verify-admin-workspace-runtime-hotfix.sh
```

Expected:

```text
Admin workspace runtime hotfix verification passed.
```
