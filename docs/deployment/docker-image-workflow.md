# Docker image workflow

This restores the production-ready Docker image build flow for the Drupal Site Platform backend.

## Branch behavior

- Push to `dev` builds and pushes:
  - `dev`
  - `dev-<commit-sha>`

- Push to `prod` builds and pushes:
  - `prod`
  - `latest`
  - `prod-<commit-sha>`

Image name:

```text
ghcr.io/<owner>/<repo>/site-platform
```

For this repository, the expected image path is:

```text
ghcr.io/vinay-osseed/growbig-web/site-platform
```

## Deployment meaning

Use `dev` to test the newest backend image.

Use `prod` or `latest` for production deployment after the `prod` branch is updated.

## Production safety

The image build does not run database updates or config import.

Production deployment should:

1. take a backup
2. pull the image
3. restart containers
4. run `drush updb -y`
5. run only the required targeted Site Platform scripts
6. rebuild cache

Do not run `drush cim -y` unless config sync has been separately reviewed and approved.
