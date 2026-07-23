# Phase 36C: Clickable Admin CRUD Workspace

## Goal

Make the safe Site Platform admin workspace directly usable by adding clickable create, view, edit, delete, manage, and results links.

## What This Does

This phase updates only runtime admin code and docs. It does not touch Drupal config.

The admin pages under `/admin/site-platform/*` now provide:

- section links from the overview page
- Add links for Site Profiles, Landing Pages, Content Blocks, Media Asset Metadata, and legacy wrappers
- View/Edit/Delete links for node-backed records
- Webform Manage/View/Results/Settings/Delete links
- links to native Drupal menu and file administration where appropriate

## What This Avoids

This phase intentionally avoids:

- `drush cim`
- `drush cex`
- `site-platform/config/sync` changes
- `config/install` changes
- active Drupal configuration mutation
- `Url` objects or `Link::fromTextAndUrl()` inside table rows

## Verification

Run:

```bash
./scripts/setup/verify-admin-workspace-crud-links.sh
```

Expected:

```text
Admin workspace CRUD links verification passed.
```
