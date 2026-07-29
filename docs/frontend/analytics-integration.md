# Frontend Analytics Integration

The Drupal backend exposes frontend analytics configuration through:

- `GET /api/v2/frontend/analytics`
- `GET /api/v2/frontend/bootstrap`

The frontend should read:

- `analytics.enabled`
- `analytics.gaMeasurementId`
- `analytics.gtmContainerId`

## Frontend behavior

If `analytics.enabled` is true and `analytics.gaMeasurementId` is set, the frontend should initialize Google Analytics using that ID.

If `analytics.gtmContainerId` is set, the frontend may initialize Google Tag Manager instead or alongside GA, depending on project requirements.

## Verification

After frontend integration:

- Open the site in browser.
- Confirm the analytics script is added to the page.
- Confirm page views fire on route changes.
- Confirm the real analytics account receives traffic.
- Confirm no analytics script loads when `analytics.enabled` is false.

## Current GrowBig seed value

The current GrowBig seed file includes:

- GA Measurement ID: `G-L2ML42EZZ7`

This value should be reviewed against the real Google Analytics account before production launch.
