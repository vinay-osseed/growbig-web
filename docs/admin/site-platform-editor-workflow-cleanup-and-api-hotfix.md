# Site Platform Editor Workflow Cleanup and API Hotfix

This package cleans up the editor workflow after moving the platform to a Drupal-native model.

## What it fixes

- Removes Legacy Wrapper sections from the Site Platform admin menu and dashboard.
- Deletes the old compatibility-only legacy content types from the active editor workflow.
- Removes those legacy bundles from the Create menu by deleting their active node type config.
- Fixes the `/api/v2/*` controller crash by adding a local JSON response helper.
- Fixes the empty Dataset type dropdown by setting allowed values on the active field storage.
- Makes Domain and Domain Alias work easier to find from the Start Here page.
- Adds a login redirect/welcome workflow so editors land on `/admin/site-platform/start` after login.
- Enables an on-demand Tour config for the Start Here page when Tour is available.

## Editor workflow after this cleanup

Editors should start from:

```text
/admin/site-platform/start
```

Then work in this order:

1. Sites and Domains
2. Media Library
3. Webforms
4. Landing Pages
5. Drupal Menus
6. Site Data Sets
7. API Guide

Legacy menu/form wrapper nodes are no longer part of the normal editor workflow.

## API expectation

The core frontend endpoints should work after this:

```text
/api/v2/bootstrap?site=growbig
/api/v2/pages?site=growbig
/api/v2/pages?site=growbig&include=full
/api/v2/navigation?site=growbig&menu=main
/api/v2/forms?site=growbig
/api/v2/datasets?site=growbig
```
