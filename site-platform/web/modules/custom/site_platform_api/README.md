# Site Platform API

Provides clean frontend API endpoints for Site Platform.

## Purpose

This module exposes normalized frontend-ready JSON responses under `/api/v1`.

The frontend should use these endpoints instead of consuming raw Drupal JSON:API responses directly.

## Current endpoints

~~~text
/api/v1/site
~~~

## Notes

The first version returns basic site/domain data from Drupal config and environment variables.

Later this endpoint will read from the editable Site Profile content type.
