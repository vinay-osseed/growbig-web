# Site Platform Menu Model

## Purpose

Menus must be editable and site-specific.

Menus can link to pages, paths, anchors, external URLs, or act as parent-only labels.

## Menu Types

Default menus:

- header
- footer
- quick links
- services
- social

## Link Types

### Page

Links to a Site Page.

Example:

    label: About
    page: about

### Internal Path

Links to any internal route.

Example:

    /services

### Anchor

Links to a section on the current page or target page.

Examples:

    #services
    /about#team

### External URL

Links to another site.

Example:

    https://example.com

### Nolink

Used as parent dropdown label.

Example:

    Company
      About
      Leadership
      Careers

## Page/Menu Relationship

Pages may suggest default menu items.

Menus remain real editable objects.

This avoids forcing every menu link to be a page.
