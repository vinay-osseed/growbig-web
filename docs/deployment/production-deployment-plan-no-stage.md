# Production Deployment Plan Template Without Stage Execution

This document prepares production planning only. It does not run stage or production deployment.

## Deployment Is Not Included

Do not execute deployment from this phase.

Stage deployment is skipped for now.

## Information Needed Before Deployment

- target production branch
- deployment environment
- production URL and API URL
- database backup command/location
- rollback command/path
- config import/export policy
- secret management path
- deployment owner
- approval owner

## Pre-Deployment Checklist

- clean git status
- reviewed pull request
- approved production settings
- approved secrets and rotation list
- approved real site YAML/content
- approved roles/permissions
- approved Webform handlers
- approved media workflow
- backup verified
- rollback verified

## Deployment Outline For Later

1. Create/tag release candidate.
2. Take production backup.
3. Deploy code.
4. Install/update dependencies.
5. Run database updates if any.
6. Import or apply approved config strategy.
7. Clear cache.
8. Import approved site YAML if required.
9. Smoke test public APIs.
10. Verify forms, emails, admin access, and key pages.
11. Confirm go/no-go.

## Rollback Outline For Later

1. Restore previous code release.
2. Restore database/config from approved backup if needed.
3. Clear cache.
4. Verify admin and frontend/API availability.
