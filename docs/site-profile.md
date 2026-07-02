# Site Profile

The Site Profile stores global frontend/backend settings for one website, domain, or workspace.

This is separate from the page/content model. It exists so editors can manage common site-wide information in one place before the full content model is finalized.

## Purpose

The Site Profile controls:

- site name
- logo
- favicon
- default social image
- frontend/UI domain
- Drupal admin/backend domain
- Drupal API domain
- contact information
- default SEO values
- social links
- footer/copyright text
- default theme/accent values

## Multi-site strategy

The platform supports one Site Profile per site/workspace/domain.

Examples:

~~~text
GrowBig LLP Production
GrowBig LLP Stage
Client A Website
Client B Website
~~~

Each Site Profile should have a unique key.

Example:

~~~text
growbig
client_a
client_b
~~~

## Recommended fields

~~~text
field_site_key
field_site_name
field_site_short_name
field_ui_domain
field_admin_domain
field_api_domain
field_logo
field_favicon
field_default_social_image
field_contact_email
field_contact_phone
field_contact_address
field_footer_copyright
field_default_meta_title
field_default_meta_description
field_theme_color
field_is_default
field_social_links
~~~

## Media fields

Media should be referenced through Drupal Media Library.

~~~text
field_logo                 -> Media: Image
field_favicon              -> Media: Image
field_default_social_image -> Media: Image
~~~

## Frontend API

The clean frontend API should expose the active Site Profile at:

~~~text
/api/v1/site
~~~

Example response:

~~~json
{
  "id": "growbig",
  "name": "GrowBig LLP",
  "shortName": "GrowBig",
  "domains": {
    "ui": "https://growbigllp.com",
    "admin": "https://admin.growbigllp.com",
    "api": "https://api.growbigllp.com"
  },
  "branding": {
    "logo": {},
    "favicon": {},
    "defaultImage": {}
  },
  "contact": {
    "email": "",
    "phone": "",
    "address": ""
  },
  "seo": {
    "title": "",
    "description": "",
    "image": {}
  },
  "social": [],
  "meta": {
    "isDefault": true
  }
}
~~~

## Selection logic

For single-site setup:

- return the Site Profile marked as default

For multi-site setup:

- first match by request/domain
- fallback to default Site Profile

## Notes

Do not hardcode this data in frontend.

Do not store editor-managed branding/media only in `settings.php`.

The backend should normalize Site Profile data before sending it to the frontend.
