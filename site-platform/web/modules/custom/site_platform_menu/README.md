# Site Platform Menu

## Purpose

Site Platform Menu owns editable, site-scoped menu models.

A menu belongs to one Site Profile.

Menu items can point to:

- Site Page
- internal path
- external URL
- no-link parent item
- optional anchor/hash

## Content Types

This module adds:

- Site Menu
- Site Menu Item

## Current Scope

This is the first menu model.

It supports flat item output with a parent key.

Nested menu tree output can be added later after the admin model is stable.

## Install

Run:

    ddev drush en site_platform_menu -y
    ddev drush cr
