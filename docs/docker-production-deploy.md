# Docker Production Deployment

This project should not use DDEV in production.

Use DDEV only for local development.

Production uses Docker Compose with these services:

- site-platform: Drupal backend, admin, and API
- site-frontend: public frontend UI
- db: MariaDB database with persistent volume
- caddy: HTTPS reverse proxy

Domain mapping:

- admin.growbigllp.com points to site-platform
- api.growbigllp.com points to site-platform
- dev.growbigllp.com points to site-frontend for testing
- growbigllp.com points to site-frontend after final live switch

Database:

The database is not stored in the app image.

Use a persistent MariaDB volume or a managed database.

Persistent data:

- MariaDB data volume
- Drupal public files volume
- Drupal private files volume

First server setup:

1. Copy .env.prod.example to .env.prod.
2. Update DockerHub namespace, passwords, hash salt, ACME email, and API URL.
3. Point DNS records to the production server.
4. Build and push images from the build machine.
5. Pull and run images on the production server.

Build and push:

  ./scripts/docker-build-push.sh .env.prod

Deploy on server:

  ./scripts/prod-deploy.sh .env.prod

First Drupal install on an empty database may need a one-time install before normal deploy commands.

After deploy, verify:

  https://api.growbigllp.com/api/v1/site
  https://api.growbigllp.com/api/v1/analytics/config
  https://admin.growbigllp.com/user/login
  https://dev.growbigllp.com

Backups:

Back up the MariaDB database and both Drupal file volumes.

Do not rely on Docker images as backups because images contain code only, not live content or uploaded files.
