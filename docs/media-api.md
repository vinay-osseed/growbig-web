# Media API Strategy

Drupal manages all media through Media Library. The frontend consumes media through JSON:API.

## Source of truth

Editors upload and manage files in Drupal:

- Image
- Document
- Video
- Remote video
- Audio

Frontend applications should consume published media and should not manage files directly unless a dedicated upload API is added later.

## Field strategy

Content types and paragraph components should reference Media entities, not raw file fields.

Examples:

~~~text
field_hero_image   -> Media: Image
field_card_image   -> Media: Image
field_gallery      -> Media: Image, multiple
field_document     -> Media: Document
field_video        -> Media: Remote video or Video
~~~

## API usage

Frontend should request media relationships with JSON:API includes.

Example:

~~~text
/jsonapi/node/page/{uuid}?include=field_image,field_image.field_media_image
~~~

For lists:

~~~text
/jsonapi/node/article?include=field_image,field_image.field_media_image
~~~

## Frontend response needs

Frontend needs:

- media title/name
- image alt text
- original file URL
- styled image URLs
- mime type
- width/height where available

## Image styles

Recommended frontend image styles:

~~~text
hero_desktop
hero_mobile
card_thumbnail
listing_thumbnail
logo
~~~

## Future API normalization

If JSON:API output is too nested for frontend work, add a small custom module:

~~~text
site_platform_api
~~~

That module can expose clean media objects with original and styled URLs.
