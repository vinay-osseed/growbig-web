# Site Platform Core

## Purpose

Site Platform Core contains foundational contracts and services used by all Site Platform modules.

This module does not expose public APIs yet.

It provides:

- SiteContext value object
- SiteContextResolverInterface
- bootstrap SiteContextResolver service

## Current Scope

This is phase 1.

It resolves a basic site context from local environment/domain variables only.

Later phases will replace the bootstrap lookup with the real Site Profile entity/domain model.

## Install

From local repo:

    ddev drush site:install standard -y --account-name=admin --account-pass=admin --site-name="Site Platform"
    ddev drush en site_platform_core -y
    ddev drush cr

## Quick Test

Run:

    ddev drush ev 'print_r(\Drupal::service("site_platform_core.site_context_resolver")->resolve()->toArray());'

Expected result on local DDEV:

- resolved: true
- siteKey: default or value from DRUPAL_SITE_KEY
- resolvedBy: local_bootstrap or one of api_domain/admin_domain/ui_domain/primary_domain

## Production Rule

Production APIs must not silently fall back.

This first resolver already follows that rule:

- local/dev can use bootstrap fallback
- prod/production returns unresolved when host does not match
