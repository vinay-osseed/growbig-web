# Site Platform Core

## Purpose

Site Platform Core contains foundational contracts and services used by all Site Platform modules.

This module does not expose public APIs yet.

It provides:

- SiteContext value object
- SiteContextResolverInterface
- SiteContextResolver service

## Current Scope

The resolver now supports two layers:

1. Site Profile entity/domain lookup when the Site Profile model exists.
2. Environment-domain bootstrap fallback for early install/local use.

## Resolution Order

- local-only site query override
- Site Profile API domain
- Site Profile admin domain
- Site Profile frontend domain
- environment API/admin/UI/primary domains
- local bootstrap fallback outside production

## Production Rule

Production APIs must not silently fall back.

If no Site Profile or production domain matches, the resolver returns an unresolved context.
