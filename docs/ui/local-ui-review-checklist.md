# Local UI Review Checklist

Use this checklist before claiming the actual UI/editor workflow is ready.

## Access

- Can log in locally as an administrator.
- Can open `/admin/site-platform`.
- Can open `/admin/content`.
- Can open `/admin/structure/webform`.

## Site Platform Admin Dashboard

- Dashboard loads without PHP fatal errors.
- Enabled module list is visible.
- Setup status rows are visible.
- Entity/content counts are visible.
- The dashboard language makes clear this is a backend/admin checkpoint.

## Default Drupal Content UI

- Site Platform content records are visible.
- Draft/demo/internal records are distinguishable enough for technical review.
- The default listing is understood as temporary, not final editor UX.

## Webform UI

- Webform admin screen loads.
- Contact Webform exists after local sample import if expected.
- Submissions can be reviewed in Webform UI.
- Email handlers are not treated as production-ready unless explicitly configured.

## API Output

- Site API returns current site metadata.
- Page API returns page data and components.
- Route API resolves frontend paths.
- Form API returns Webform-backed schema.
- Submit API creates Webform-backed submissions.
- Media, SEO, analytics, and search APIs respond.

## Final UI Gaps

Before production/editor handoff, confirm a plan for:

- custom admin listings
- site filters
- clearer editor labels
- Webform shortcuts
- reusable content/media management
- role-specific admin access
- public frontend rendering
