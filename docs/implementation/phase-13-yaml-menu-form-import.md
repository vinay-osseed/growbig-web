# Phase 13: YAML Menu and Form Import

## Goal

Extend YAML setup import to include menus and forms.

## Command

    site-platform:setup-import /path/to/setup.yml

## What This Phase Imports

This phase imports:

- Site Profile
- Site Pages
- Site Content Blocks
- Site Menus
- Site Menu Items
- Site Forms
- Site Form Fields

## Stable Identity

Site Menu:

    site.key + menu.key

Site Menu Item:

    site.key + menu.key + item.key

Site Form:

    site.key + form.key

Site Form Field:

    site.key + form.key + field.key

## Supported Menu Link Types

Menu items support:

- page
- path
- external
- nolink

## Supported Form Field Types

The setup runner stores field type values as stable strings.

Current demo examples use:

- text
- email
- textarea

## Current Limits

This phase does not import:

- page components
- media/images
- translations
- users/roles
- advanced form option validation
- nested menu tree output

Those can be added in later setup phases.
