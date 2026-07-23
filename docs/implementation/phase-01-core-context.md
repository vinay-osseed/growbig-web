# Phase 01: Site Platform Core Context

## Goal

Add the first real implementation foundation for Site Platform v2.

This phase adds a core module and a site context contract.

## Files

- site_platform_core.info.yml
- site_platform_core.services.yml
- SiteContext
- SiteContextResolverInterface
- SiteContextResolver
- README

## What This Phase Does Not Include

- No public API controllers
- No Site Profile content model
- No setup runner
- No menus
- No pages
- No components

## Why

Site resolution is the foundation.

Every future API must use the same resolved site context.
