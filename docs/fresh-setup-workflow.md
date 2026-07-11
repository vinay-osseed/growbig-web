# Fresh Setup Workflow

## Purpose

This document defines the ideal first-run setup workflow after Drupal is installed.

The workflow must support:

- Production deployment
- Local/development setup
- Single-site setup
- Multi-site / multi-brand setup
- Pre-filled setup from config or environment values
- Manual setup through Drupal admin UI
- Safe retry when setup fails
- Safe reset when setup needs to be re-run
- Decoupled frontend rendering requirements

## Core Principle

The setup workflow must be usable by both developers and non-technical admins.

The setup should collect only the required information from the user, while automatically generating safe defaults for frontend-critical fields.

Required user input should stay small.

Frontend-critical data must always exist.

## Setup Entry Point

After Drupal install and config import, admin should be guided to:

- `/admin/site-setup`

The dashboard should show a warning if setup is incomplete:

- Site setup is incomplete.
- Start setup now.

After setup completes, the setup wizard should be locked unless an administrator or site developer unlocks it.

## Setup Modes

### 1. Pre-filled Setup

Used for developer, staging, and production deployments.

Values may come from:

1. Environment variables
2. `setup/site.yml`
3. Existing Drupal config
4. Safe defaults

The setup wizard should load these values and allow an admin to review before running setup.

### 2. Manual Setup

Used by non-technical admins.

Admin fills required fields in Drupal UI.

Optional fields can be skipped and added later.

### 3. Multi-site Setup

Used when one Drupal backend powers multiple sites, brands, domains, or frontends.

The wizard should allow:

- Single Site
- Multiple Sites / Brands

Each site should have its own identity, URLs, menus, pages, branding, and frontend config.

## Field Priority

Setup values should resolve in this order:

1. Environment values
2. `setup/site.yml`
3. Existing Drupal config
4. User-entered setup form values
5. Safe defaults

After the setup form is submitted, saved Drupal config becomes the source of truth.

Environment values may still override deployment-specific settings such as analytics IDs or frontend URLs.

## Required User Fields

These fields should be required from the person running setup.

### Global Required Fields

- Site Name
- Site Key
- Primary Domain
- Frontend URL
- Admin URL
- API URL
- Admin Email

### Contact Required Fields

- Company Name
- Primary Email
- Country

### Setup Required Choices

- Create Default Pages
- Create Default Menus
- Create Default Forms
- Create Default Roles
- Create Demo Content

For production, the default should be:

- Create Default Pages: enabled
- Create Default Menus: enabled
- Create Default Forms: enabled
- Create Default Roles: enabled
- Create Demo Content: disabled

For local/dev, demo content may be enabled by default.

## Required Fields Per Site

For each site in single-site or multi-site mode:

- Site Name
- Site Key
- Primary Domain
- Frontend URL
- API URL

## Optional Fields

These fields can be filled during setup or added later.

- Logo
- Favicon
- Default Image
- Theme Color
- Primary Brand Color
- Secondary Brand Color
- Phone
- Address
- Working Hours
- Map URL
- Social Links
- SEO Defaults
- Google Analytics Measurement ID
- Services
- Partners
- Team Members
- Jobs
- Extra Admin Users
- Backup Settings

## Decoupled Frontend Requirements

The frontend must never break because optional setup fields are missing.

The setup workflow must ensure the following frontend-critical values exist either through user input or defaults.

### Site API Requirements

Endpoint:

- `GET /api/v1/site`

Frontend-critical fields:

- Site Name
- Site Key
- Frontend URL
- API URL
- Logo URL or empty-safe fallback
- Favicon URL or empty-safe fallback
- Theme Color or default color
- Contact Email
- Social Links array, even if empty
- Analytics config API path

Required from user:

- Site Name
- Site Key
- Frontend URL
- API URL
- Contact Email

Can be auto-filled:

- Logo
- Favicon
- Theme Color
- Social Links

### Menu API Requirements

Endpoints:

- `GET /api/v1/menus/header`
- `GET /api/v1/menus/footer`

Frontend-critical fields:

- Menu item title
- URL/path
- Weight
- Active/enabled state

Setup must create default header/footer menus even if user does not add custom menu items.

Default menu:

- Home
- About
- Careers
- Contact

### Page API Requirements

Endpoints:

- `GET /api/v1/pages`
- `GET /api/v1/pages/home`
- `GET /api/v1/pages/about`
- `GET /api/v1/pages/careers`
- `GET /api/v1/pages/contact`

Frontend-critical fields:

- Page Key
- Page Title
- Page Type
- Summary
- Sections array, even if minimal
- SEO defaults
- Header/footer menu settings

Every default page should have at least one safe section.

Minimum sections:

- Home: Hero section + CTA section
- About: Hero section + content section
- Careers: Hero section + jobs list section
- Contact: Hero section + contact form section

If no image is provided, frontend should receive empty-safe image data and render a gradient or default placeholder.

### Content List API Requirements

Endpoints:

- `GET /api/v1/content/services`
- `GET /api/v1/content/partners`
- `GET /api/v1/content/team`
- `GET /api/v1/content/jobs`

Frontend-critical fields:

- Source
- Key/slug
- Title
- Summary
- Active state
- Display order
- Featured state
- Image/logo/photo URL or empty-safe fallback

Reusable content can be empty, but API response must be valid.

If demo content is disabled, frontend should still render empty states correctly.

### Form API Requirements

Endpoints:

- `GET /api/v1/forms/contact-us`
- `POST /api/v1/forms/contact-us/submit`
- `GET /api/v1/forms/job-application`
- `POST /api/v1/forms/job-application/submit`

Frontend-critical fields:

- Form ID
- Form title
- Fields array
- Required flags
- Validation rules
- Submit endpoint
- Success message

Setup must ensure contact and job application forms exist if default forms are enabled.

### Analytics API Requirements

Endpoint:

- `GET /api/v1/analytics/config`

Frontend-critical fields:

- enabled
- provider
- measurementId

If analytics is not configured, API must return:

    {
      "enabled": false,
      "provider": "none",
      "measurementId": ""
    }

Frontend must not hardcode analytics IDs.

## Setup Wizard Steps

### Step 1: Setup Mode

Admin chooses:

- Single Site
- Multiple Sites / Brands

Admin chooses:

- Use Pre-filled Config
- Start Blank Setup

### Step 2: Site Identity

Required:

- Site Name
- Site Key
- Primary Domain
- Frontend URL
- Admin URL
- API URL

Optional:

- Tagline
- Description
- Logo
- Favicon
- Theme Color

### Step 3: Business / Contact Info

Required:

- Company Name
- Primary Email
- Country

Optional:

- Phone
- Address
- City
- State
- Postal Code
- Working Hours
- Map URL
- Social Links

### Step 4: Frontend Defaults

The setup should preview frontend defaults:

- Default pages
- Header menu
- Footer menu
- Default sections
- Empty-state behavior
- Default theme color
- Default SEO fallback

Admin should be able to continue without uploading assets.

### Step 5: Default Content

Admin chooses:

- Create Home Page
- Create About Page
- Create Careers Page
- Create Contact Page
- Create Header/Footer Menus
- Create Contact Form
- Create Job Application Form
- Create Roles
- Create Demo Content

### Step 6: Analytics

Optional:

- Enable Analytics
- Google Analytics Measurement ID

Show note:

- Environment values override Drupal setup values.

### Step 7: Review

Before running setup, show:

- Sites to initialize
- Pages to create
- Menus to create
- Forms to create
- Roles to create
- Analytics status
- Demo content status
- Frontend URLs
- API URLs

Button:

- Run Setup

### Step 8: Result

Show:

- Success steps
- Warnings
- Failed steps
- Retry failed steps
- Resume setup
- Reset setup

## Setup State

Setup should track progress in config/state.

Example config object:

- `site_platform_setup.status`

Fields:

- installed
- completed
- locked
- current_step
- setup_id
- mode
- environment
- started_at
- completed_at

Example step status:

- roles: completed
- site_profile: completed
- pages: completed
- menus: completed
- forms: failed
- analytics: pending

## Setup Manifest

Every setup run should create a manifest of items created by setup.

Example:

    setup_id: abc123
    nodes:
      - 1
      - 2
      - 3
    webforms:
      - contact_us
      - job_application
    roles:
      - content_admin
      - hr_manager
    config:
      - site_platform_api.analytics

The manifest allows safe reset without deleting user-created content.

## Failure Handling

If setup fails, user should not be stuck.

Available actions:

- Retry Failed Step
- Resume Setup
- Reset Setup Status Only
- Delete Setup-created Content
- Restore Pre-setup Backup

## Reset Levels

### Level 1: Reset Setup Status Only

Use when setup failed but created content is acceptable.

This clears setup status and allows wizard to run again.

It does not delete content.

### Level 2: Delete Setup-created Data Only

Use when setup created broken demo/default content.

Deletes only records from the setup manifest.

Does not delete user-created content.

### Level 3: Restore Pre-setup Backup

Use for production/stage if setup badly fails.

Before setup runs, the system should create or require:

- Database backup
- Config backup
- Files backup, optional

## Production Workflow

Ideal production setup flow:

1. Deploy code.
2. Run `composer install`.
3. Run database updates.
4. Import config.
5. Clear cache.
6. Create DB backup.
7. Open `/admin/site-setup`.
8. Review pre-filled setup values.
9. Run setup.
10. Verify APIs.
11. Lock setup wizard.
12. Open Site Dashboard.

## Developer Workflow

Developer should be able to run:

    ./scripts/setup-fresh-site.sh

Future options:

    ./scripts/setup-fresh-site.sh --config=setup/site.yml
    ./scripts/setup-fresh-site.sh --mode=prod
    ./scripts/setup-fresh-site.sh --resume
    ./scripts/setup-fresh-site.sh --reset-status
    ./scripts/setup-fresh-site.sh --delete-setup-data

## Non-technical Admin Workflow

1. Login as admin.
2. Go to `/admin/site-setup`.
3. Fill required fields.
4. Review frontend defaults.
5. Click Run Setup.
6. See setup results.
7. Go to Site Dashboard.
8. Add optional content later.

## Setup Lock

After successful setup:

- setup_locked: true

Only administrator or site developer can:

- unlock setup
- reset setup
- delete setup-created data
- re-run setup

## Planned Implementation Phases

- Phase 12A: Fresh Setup Workflow Documentation
- Phase 12B: Setup Config Schema + Setup Status
- Phase 12C: Setup Wizard Form UI
- Phase 12D: Setup Runner Service
- Phase 12E: Setup Reset / Resume Workflow
- Phase 12F: Master CLI Setup Script
- Phase 12G: Fresh Setup Verification Script
- Phase 12H: Security / Deployment / Backup Docs
