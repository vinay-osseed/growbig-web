# Phase 08: Component Model and Page API Output

## Goal

Add the first component model and include components in Site Page API responses.

## Component Foundation

This phase uses Paragraphs.

The first component types are:

- Site Hero
- Site Rich Text
- Site CTA

## Site Page Field

Site Page gets:

    field_page_components

This is an ordered entity reference revisions field to Paragraph components.

## API Output

Page API responses include:

    components

Example:

    {
      "id": "component-1",
      "type": "hero",
      "variant": "default",
      "adminLabel": "Home hero",
      "props": {}
    }

## Current Limits

This phase uses simple string/string_long fields.

It does not include:

- media/image fields
- frontend component schema validation
- reusable shared components
- admin previews
- nested components
- component permissions

Those come later.
