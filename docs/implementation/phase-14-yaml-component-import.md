# Phase 14: YAML Component Import

## Goal

Extend YAML setup import to include page components.

## Command

    site-platform:setup-import /path/to/setup.yml

## What This Phase Imports

This phase imports:

- Site Profile
- Site Pages
- Page Components
- Site Menus
- Site Menu Items
- Site Forms
- Site Form Fields
- Site Content Blocks

## Component YAML

Components are defined inside each page.

Example:

    pages:
      - key: home
        title: Home
        components:
          - type: hero
            key: home_hero
            variant: split
            adminLabel: Home hero
            title: Hero title
            summary: Hero summary
            buttonLabel: Get Started
            buttonPath: /contact

## Supported Component Types

This phase supports:

- hero
- rich_text
- cta

## Stable Identity

Page component:

    site.key + page.key + component.key

The actual Paragraph stores `field_component_key` using this stable value.

## Current Limits

This phase does not import:

- media/image entities
- nested components
- reusable shared components
- multilingual component translations
- frontend schema validation

Those can be added in later setup phases.
