# GrowBig Site Platform - Feature and Usage Guide

## 1. Purpose of the Platform

GrowBig is a Drupal-based backend platform for a decoupled website. The backend owns the site structure, content, menus, forms, analytics configuration, setup workflow, and admin tools. The frontend consumes backend APIs and renders the public website.

The current backend is production-ready for the implemented scope. It includes:

- Dynamic page APIs
- Reusable content APIs
- Menu APIs
- Form metadata and submission APIs
- Admin dashboard
- Role-based access
- Analytics configuration
- First-run setup workflow
- YAML-based setup import
- Portable setup CLI
- Final verification scripts

## 2. High-Level Architecture

### 2.1 Backend

Path:

```bash
site-platform/
```

Drupal backend responsibilities:

- Manage site pages
- Manage reusable content
- Manage forms
- Manage menus
- Manage analytics settings
- Manage setup workflow
- Provide APIs for frontend
- Provide admin dashboard and setup UI

### 2.2 Frontend

Path:

```bash
site-frontend/
```

Frontend responsibilities:

- Render API responses
- Build public page layouts
- Submit forms to backend APIs
- Consume menus and site settings

### 2.3 Custom Drupal Modules

Main custom modules:

```bash
site-platform/web/modules/custom/site_platform_api
site-platform/web/modules/custom/site_platform_admin
```

`site_platform_api` provides frontend/backend API endpoints.

`site_platform_admin` provides admin dashboard, setup workflow, setup CLI commands, and setup storage.

## 3. Environment Summary

Current development environment:

- Drupal: 11.4.0
- PHP: 8.4.22
- Drush: 13.7.4
- Database: MariaDB/MySQL through DDEV
- Webform: 6.3.0
- DDEV: supported

Development URLs:

```text
https://growbig-web.ddev.site
https://admin.growbig-web.ddev.site
https://api.growbig-web.ddev.site
https://ui.growbig-web.ddev.site
```

## 4. Main API Endpoints

### 4.1 Site API

```http
GET /api/v1/site
```

Purpose:

- Returns global site information
- Includes setup profile values
- Exposes frontend-safe setup data under `setupProfile`

Usage:

```bash
curl -sk https://growbig-web.ddev.site/api/v1/site | python3 -m json.tool
```

Important response section:

```json
{
  "setupProfile": {
    "mode": "single",
    "environment": "local",
    "siteKey": "growbig",
    "primaryDomain": "growbig-web.ddev.site",
    "frontendUrl": "https://growbig-web.ddev.site",
    "adminUrl": "https://admin.growbig-web.ddev.site",
    "apiUrl": "https://api.growbig-web.ddev.site",
    "companyName": "GrowBig",
    "primaryEmail": "admin@example.com",
    "country": "India",
    "branding": {
      "themeColor": "#0f62fe",
      "logo": "",
      "favicon": ""
    },
    "extraSites": []
  }
}
```

### 4.2 Page APIs

Page index:

```http
GET /api/v1/pages
```

Single dynamic page:

```http
GET /api/v1/pages/{page_key}
```

Available default pages:

```http
GET /api/v1/pages/home
GET /api/v1/pages/about
GET /api/v1/pages/careers
GET /api/v1/pages/contact
```

Usage:

```bash
curl -sk https://growbig-web.ddev.site/api/v1/pages | python3 -m json.tool
curl -sk https://growbig-web.ddev.site/api/v1/pages/home | python3 -m json.tool
```

Frontend usage pattern:

```text
Frontend route: /about
Backend call:   GET /api/v1/pages/about
Frontend render: sections from API response
```

### 4.3 Menu APIs

All menus:

```http
GET /api/v1/menus
```

Header menu:

```http
GET /api/v1/menus/header
```

Footer menu:

```http
GET /api/v1/menus/footer
```

Usage:

```bash
curl -sk https://growbig-web.ddev.site/api/v1/menus/header | python3 -m json.tool
curl -sk https://growbig-web.ddev.site/api/v1/menus/footer | python3 -m json.tool
```

Default menu pages:

- Home
- About
- Careers
- Contact

Menu values come from page fields:

- `field_show_in_header`
- `field_show_in_footer`
- `field_menu_title`
- `field_menu_weight`

### 4.4 Reusable Content List APIs

Reusable content sources:

```http
GET /api/v1/content/services
GET /api/v1/content/partners
GET /api/v1/content/team
GET /api/v1/content/jobs
```

Usage:

```bash
curl -sk https://growbig-web.ddev.site/api/v1/content/services | python3 -m json.tool
curl -sk https://growbig-web.ddev.site/api/v1/content/jobs | python3 -m json.tool
```

Supported list options:

```text
limit
featuredOnly
```

Example:

```bash
curl -sk "https://growbig-web.ddev.site/api/v1/content/services?limit=3" | python3 -m json.tool
```

### 4.5 Reusable Content Detail APIs

Detail endpoints:

```http
GET /api/v1/content/services/{key}
GET /api/v1/content/partners/{key}
GET /api/v1/content/team/{key}
GET /api/v1/content/jobs/{key}
```

Usage:

```bash
curl -sk https://growbig-web.ddev.site/api/v1/content/jobs/sample-job-key | python3 -m json.tool
```

Expected behavior:

- Valid source and key returns item data
- Missing item returns 404
- Invalid source returns 400

### 4.6 Form Metadata APIs

Contact form metadata:

```http
GET /api/v1/forms/contact-us
```

Job application form metadata:

```http
GET /api/v1/forms/job-application
```

Usage:

```bash
curl -sk https://growbig-web.ddev.site/api/v1/forms/contact-us | python3 -m json.tool
curl -sk https://growbig-web.ddev.site/api/v1/forms/job-application | python3 -m json.tool
```

### 4.7 Form Submit APIs

Contact form submit:

```http
POST /api/v1/forms/contact-us/submit
```

Job application submit:

```http
POST /api/v1/forms/job-application/submit
```

Example contact submit:

```bash
curl -sk -X POST https://growbig-web.ddev.site/api/v1/forms/contact-us/submit \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "subject": "Test Inquiry",
    "message": "This is a test message.",
    "consent": true
  }' | python3 -m json.tool
```

Example job application submit:

```bash
curl -sk -X POST https://growbig-web.ddev.site/api/v1/forms/job-application/submit \
  -H 'Content-Type: application/json' \
  -d '{
    "job_key": "sample-job",
    "job_title": "Sample Job",
    "name": "Test Applicant",
    "email": "applicant@example.com",
    "message": "I am interested in this role.",
    "consent": true
  }' | python3 -m json.tool
```

Note:

- `resume_upload` exists in metadata.
- API file upload support is not included in the current scope.

### 4.8 Analytics Config API

```http
GET /api/v1/analytics/config
```

Usage:

```bash
curl -sk https://growbig-web.ddev.site/api/v1/analytics/config | python3 -m json.tool
```

Expected disabled response example:

```json
{
  "enabled": false,
  "provider": "none",
  "measurementId": ""
}
```

Analytics can be configured from:

- Drupal admin settings
- Environment variables
- Setup YAML/runtime values

Environment variables have priority when configured.

### 4.9 Admin Dashboard API

```http
GET /api/v1/admin/dashboard
```

Purpose:

- Provides dashboard data for authenticated admin users
- Role-aware
- Not intended for anonymous plain browser/curl access

Verification uses script:

```bash
./scripts/check-dashboard-api.sh
```

## 5. Admin Features

### 5.1 Site Dashboard

Admin URL:

```text
/admin/site-dashboard
```

Purpose:

- View site platform summary
- View content metrics
- View forms/dashboard cards
- Role-aware admin experience
- Interactive dashboard UI

Verification:

```bash
./scripts/check-dashboard-ui.sh
```

### 5.2 Analytics Admin Settings

Admin URL:

```text
/admin/config/site-platform/analytics
```

Purpose:

- Configure analytics provider
- Enable/disable analytics
- Set Google Analytics measurement ID

Important rule:

- Private reporting credentials are not exposed through frontend JSON.

## 6. Setup Workflow Features

The setup workflow is the main first-run production setup system.

Admin URLs:

```text
/admin/site-setup
/admin/site-setup/wizard
/admin/site-setup/run
/admin/site-setup/complete
/admin/site-setup/unlock
/admin/site-setup/reset-status
```

### 6.1 Setup Overview

URL:

```text
/admin/site-setup
```

Purpose:

- Shows setup status
- Shows setup progress
- Links to wizard, run, complete, unlock, and reset actions

### 6.2 Setup Wizard

URL:

```text
/admin/site-setup/wizard
```

Purpose:

Collects setup values:

- Site name
- Site key
- Primary domain
- Frontend URL
- Admin URL
- API URL
- Company name
- Primary email
- Country
- Branding values
- Setup options
- Analytics settings

Values are stored in Drupal State API, not exported to config sync.

### 6.3 Setup Runner

URL:

```text
/admin/site-setup/run
```

Purpose:

Runs safe setup preparation:

- Applies system site name/email
- Applies analytics config
- Ensures setup roles exist
- Ensures default pages exist
- Ensures default menus are set on pages
- Ensures default webforms exist
- Updates setup status
- Updates setup manifest

Safe behavior:

- Idempotent
- Reuses existing pages/forms/roles where possible
- Does not delete production content

### 6.4 Complete and Lock Setup

URL:

```text
/admin/site-setup/complete
```

Purpose:

- Checks required setup values
- Checks required roles
- Checks required pages
- Checks required webforms
- Marks setup completed
- Locks setup status

CLI equivalent:

```bash
./scripts/site-setup.sh complete
```

### 6.5 Unlock Setup

URL:

```text
/admin/site-setup/unlock
```

Purpose:

- Unlocks setup status
- Marks verification pending again
- Does not delete content

CLI equivalent:

```bash
./scripts/site-setup.sh unlock
```

### 6.6 Reset Setup Status

URL:

```text
/admin/site-setup/reset-status
```

Purpose:

- Resets setup runtime status only
- Does not delete pages, forms, roles, content, files, or submissions

CLI equivalent:

```bash
./scripts/site-setup.sh reset-status
```

## 7. Setup CLI Usage

Main helper:

```bash
./scripts/site-setup.sh
```

Commands:

```bash
./scripts/site-setup.sh status
./scripts/site-setup.sh preview
./scripts/site-setup.sh sites
./scripts/site-setup.sh import setup/site.yml
./scripts/site-setup.sh run
./scripts/site-setup.sh complete
./scripts/site-setup.sh unlock
./scripts/site-setup.sh reset-status
./scripts/site-setup.sh check
```

### 7.1 Native Drush Commands

```bash
ddev drush site-platform:setup-status
ddev drush site-platform:setup-preview
ddev drush site-platform:setup-sites
ddev drush site-platform:setup-import setup/site.yml
ddev drush site-platform:setup-run
ddev drush site-platform:setup-complete
ddev drush site-platform:setup-unlock
ddev drush site-platform:setup-reset-status
```

Aliases:

```bash
ddev drush sp-setup-status
ddev drush sp-setup-preview
ddev drush sp-setup-sites
ddev drush sp-setup-import setup/site.yml
ddev drush sp-setup-run
ddev drush sp-setup-complete
ddev drush sp-setup-unlock
ddev drush sp-setup-reset-status
```

### 7.2 Portable Production CLI

The helper supports production/stage without DDEV.

Set Drush binary explicitly:

```bash
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh status
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh preview
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh import setup/site.yml
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh run
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh complete
```

Resolution order:

1. `DRUSH_BIN` if provided
2. `ddev drush` if DDEV exists
3. `drush` fallback

## 8. Setup YAML Usage

Example file:

```bash
setup/site.example.yml
```

Private environment file:

```bash
setup/site.yml
```

Create private setup file:

```bash
cp setup/site.example.yml setup/site.yml
```

Edit it for production:

```yaml
mode: single
environment: production

site:
  name: GrowBig
  key: growbig
  primary_domain: example.com
  frontend_url: https://example.com
  admin_url: https://admin.example.com
  api_url: https://api.example.com

contact:
  company_name: GrowBig
  primary_email: admin@example.com
  country: India

branding:
  theme_color: '#0f62fe'
  logo: ''
  favicon: ''

setup_options:
  create_default_pages: true
  create_default_menus: true
  create_default_forms: true
  create_default_roles: true
  create_demo_content: false

analytics:
  enabled: false
  measurement_id: ''

extra_sites: []
```

Import setup values:

```bash
./scripts/site-setup.sh import setup/site.yml
```

Preview before running:

```bash
./scripts/site-setup.sh preview
```

Run setup:

```bash
./scripts/site-setup.sh run
```

Complete and lock:

```bash
./scripts/site-setup.sh complete
```

Important:

- Do not commit `setup/site.yml`.
- Do not run `drush cex` after importing environment-specific setup values unless intentional.

## 9. Private Setup File Protection

Committed file:

```bash
setup/site.example.yml
```

Ignored private files:

```bash
setup/site.yml
setup/*.local.yml
setup/*.stage.yml
setup/*.prod.yml
setup/*.production.yml
```

Purpose:

- Prevent accidental commit of production URLs
- Prevent accidental commit of analytics IDs
- Prevent accidental commit of private environment values

## 10. Multi-Site Setup Rows

Current status:

- Real Drupal Domain records are not created.
- No Domain module is currently enabled.
- `extra_sites` is used as validated setup metadata.

Inspect setup site rows:

```bash
./scripts/site-setup.sh sites
```

Example YAML:

```yaml
extra_sites:
  - name: Regional Site
    key: regional
    primary_domain: regional.example.com
    frontend_url: https://regional.example.com
    admin_url: https://admin.regional.example.com
    api_url: https://api.regional.example.com
```

Validation rules:

- `name` is required
- `key` is required
- `primary_domain` is required
- keys must be unique
- primary domains must be unique
- optional URL fields must be valid URLs

Production decision:

Do not create real domain records until the project selects one of these:

```text
Option A: Drupal Domain module
Option B: Custom site entity/table
Option C: Frontend-only routing metadata
```

Current recommendation:

Use Option C unless backend-side domain access/content separation becomes required.

## 11. Destructive Cleanup / Reset Policy

Current destructive cleanup status:

- Not implemented intentionally
- Not a production blocker

Reason:

- Setup creates real pages, forms, roles, and config.
- After setup, admins may edit those records.
- Webforms may receive real submissions.
- Automatic deletion could remove production data.

Safe commands already available:

```bash
./scripts/site-setup.sh unlock
./scripts/site-setup.sh reset-status
```

These do not delete:

- pages
- forms
- roles
- content
- files
- submissions

Future destructive cleanup must require:

1. Database and file backup
2. Dry-run list
3. Explicit confirmation
4. Protection for webform submissions
5. Protection for edited production content
6. Audit log of deleted records

## 12. Branding Support

Branding setup values:

```yaml
branding:
  theme_color: '#0f62fe'
  logo: ''
  favicon: ''
```

Exposed in Site API:

```json
{
  "setupProfile": {
    "branding": {
      "themeColor": "#0f62fe",
      "logo": "",
      "favicon": ""
    }
  }
}
```

Frontend can consume:

- `themeColor`
- `logo`
- `favicon`

Current status:

- Branding values are available through API.
- Deeper frontend/admin branding usage can be added later.

## 13. Roles Created by Setup

Setup can create or update these roles:

- Site Developer
- Content Admin
- HR Manager
- Form Manager
- Analytics Viewer

Role machine names:

```text
site_developer
content_admin
hr_manager
form_manager
analytics_viewer
```

Role permissions are baseline production-safe admin permissions for each role category.

Verification:

```bash
./scripts/check-admin-roles.sh
```

## 14. Default Pages Created by Setup

Default pages:

- Home
- About
- Careers
- Contact

Each page uses content type:

```text
site_page
```

Important fields:

- `field_page_key`
- `field_page_type`
- `field_summary`
- `field_show_in_header`
- `field_show_in_footer`
- `field_menu_title`
- `field_menu_weight`

The page API and menu API use these values.

## 15. Default Webforms Created by Setup

Default webforms:

```text
contact_us
job_application
```

Contact form includes:

- name
- email
- phone
- company
- subject
- message
- consent

Job application form includes:

- job key
- job title
- name
- email
- phone
- current location
- experience years
- current company
- resume upload
- portfolio URL
- LinkedIn URL
- message
- consent

## 16. Verification Scripts

### 16.1 Final setup workflow check

```bash
./scripts/check-final-setup-workflow.sh
```

Checks:

- setup CLI syntax
- setup status
- setup preview
- setup sites
- setup foundation
- Site API setup profile
- custom code standards

### 16.2 Backend MVP verification

```bash
./scripts/verify-backend-mvp.sh
```

Checks:

- Drupal platform
- API smoke tests
- analytics config API
- Site API setup profile
- dashboard API
- dashboard UI
- required backend endpoints

### 16.3 Code standards

```bash
./scripts/check-code.sh
```

Checks custom Drupal code quality.

### 16.4 Site setup check

```bash
./scripts/check-site-setup.sh
```

Checks setup routes, services, commands, and setup foundation.

### 16.5 Site API setup profile check

```bash
./scripts/check-site-api-setup-profile.sh
```

Checks that `GET /api/v1/site` includes expected setup profile values.

## 17. Full Production Verification Command

Run this before deployment:

```bash
git pull --ff-only origin dev
./scripts/check-final-setup-workflow.sh
./scripts/verify-backend-mvp.sh
git status
```

Expected result:

```text
Final setup workflow verified.
Backend MVP verification passed.
nothing to commit, working tree clean
```

## 18. Recommended Production Setup Flow

### Step 1: Pull latest code

```bash
git pull --ff-only origin dev
```

### Step 2: Create private setup YAML

```bash
cp setup/site.example.yml setup/site.yml
```

### Step 3: Edit production values

Update:

- site name
- site key
- primary domain
- frontend URL
- admin URL
- API URL
- company name
- primary email
- country
- analytics settings
- branding values

### Step 4: Import setup YAML

DDEV/local:

```bash
./scripts/site-setup.sh import setup/site.yml
```

Production/stage:

```bash
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh import setup/site.yml
```

### Step 5: Preview setup

```bash
./scripts/site-setup.sh preview
```

Production/stage:

```bash
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh preview
```

### Step 6: Run setup

```bash
./scripts/site-setup.sh run
```

Production/stage:

```bash
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh run
```

### Step 7: Verify setup

```bash
./scripts/check-final-setup-workflow.sh
./scripts/verify-backend-mvp.sh
```

### Step 8: Complete and lock

```bash
./scripts/site-setup.sh complete
```

Production/stage:

```bash
DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh complete
```

### Step 9: Confirm status

```bash
./scripts/site-setup.sh status
```

## 19. Deployment Safety Rules

Do:

- Use private `setup/site.yml` for environment-specific values
- Run preview before run
- Run final verification before complete/lock
- Keep setup values in Drupal State API
- Keep `setup/site.example.yml` as the committed template

Do not:

- Commit production `setup/site.yml`
- Run `drush cex` after importing private production values unless intentional
- Implement destructive cleanup without backup-aware design
- Create fake domain records without a selected multisite architecture

## 20. Current Production-Ready Scope

Ready now:

- Backend APIs
- Page system
- Menu system
- Reusable content APIs
- Jobs API
- Forms APIs
- Dashboard
- Analytics config
- Setup UI
- Setup CLI
- Setup YAML import
- Setup validation
- Site API setup profile
- Production handoff docs
- Final verification scripts

Not part of current production scope:

- Real Drupal Domain record creation
- Destructive cleanup/delete reset
- API file upload for resume field
- Full frontend branding application beyond available API values

## 21. Quick Demo Order

Use this order to demonstrate the platform:

1. Open `/admin/site-dashboard`
2. Open `/admin/site-setup`
3. Show setup wizard at `/admin/site-setup/wizard`
4. Show setup preview:

        ./scripts/site-setup.sh preview

5. Show setup sites:

        ./scripts/site-setup.sh sites

6. Show Site API:

        curl -sk https://growbig-web.ddev.site/api/v1/site | python3 -m json.tool

7. Show pages API:

        curl -sk https://growbig-web.ddev.site/api/v1/pages | python3 -m json.tool

8. Show one dynamic page:

        curl -sk https://growbig-web.ddev.site/api/v1/pages/home | python3 -m json.tool

9. Show menu API:

        curl -sk https://growbig-web.ddev.site/api/v1/menus/header | python3 -m json.tool

10. Show content API:

        curl -sk https://growbig-web.ddev.site/api/v1/content/services | python3 -m json.tool

11. Show form metadata:

        curl -sk https://growbig-web.ddev.site/api/v1/forms/contact-us | python3 -m json.tool

12. Show analytics config:

        curl -sk https://growbig-web.ddev.site/api/v1/analytics/config | python3 -m json.tool

13. Run final verification:

        ./scripts/check-final-setup-workflow.sh

14. Run backend MVP verification:

        ./scripts/verify-backend-mvp.sh

## 22. Final Summary

The GrowBig backend platform now provides a complete Drupal-powered backend for a decoupled site. It includes APIs, admin tools, setup automation, production-safe setup import, verification scripts, and clear deployment rules.

The setup workflow is production-ready for the current architecture. Risky future items, such as real domain record creation and destructive cleanup, are intentionally deferred until the project chooses the correct architecture and backup policy.

## Current Admin Setup Capability

The backend setup flow is usable by non-technical admins for the current single-site setup.

A developer still performs deployment and first installation. After that, the admin can use:

- Site Setup
- Setup Wizard
- Prepare Run
- Complete and Lock
- Site Dashboard

## Current Site Scope

This installation currently manages one primary site.

Additional Sites / Brands rows are future-ready metadata only. They do not create active Drupal Domain records yet.

## Menu Usage

Frontend menus are managed through page visibility fields and exposed through APIs:

- `/api/v1/menus/header`
- `/api/v1/menus/footer`

The Drupal node edit Menu settings panel is not the frontend menu source.
