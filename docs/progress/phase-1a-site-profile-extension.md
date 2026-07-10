# Phase 1A: Site Profile Extension

## Purpose

Extend the existing Site Profile foundation so the Drupal backend can support reusable multi-site and company settings for one or more frontend websites.

## Scope

This phase extends the existing Site Profile content type.

It does not change the frontend API response yet.

The API response update will be handled separately in Phase 1B.

## Added Site Profile Fields

- Tagline
- Description
- Alternate phone
- Website
- City
- State
- Country
- Postal code
- Working hours
- Google map link
- Map latitude
- Map longitude
- Footer description
- Main menu machine name
- Footer quick links menu machine name
- Footer services menu machine name
- Social links

## Added Paragraph Type

- Social Link

## Social Link Fields

- Platform name
- Profile URL
- Icon
- Display order
- Is active

## Scripts

Created:

- scripts/drupal/phase1a-extend-site-profile.php

Updated:

- scripts/drupal/create-default-site-profile.php

## Dummy Data

The default GrowBig Site Profile now includes mockup-based company details, contact details, footer details, map details, menu machine names, and social media links.

## Re-run Instructions

Run these commands from the repository root:

- ddev drush scr /var/www/html/scripts/drupal/phase1a-extend-site-profile.php
- ddev drush scr /var/www/html/scripts/drupal/create-default-site-profile.php
- ddev drush cr
- ddev drush cex -y

## Testing Checklist

After running the scripts, verify:

- Site Profile fields exist.
- Social Link paragraph type exists.
- Social links are attached to the default GrowBig Site Profile.
- Drupal config exports cleanly.
- Custom code check passes.
- Git status shows only expected changes.

## Next Phase

Phase 1B will handle:

- Updating /api/v1/site response.
- Exposing new company settings.
- Exposing social links.
- Updating OpenAPI documentation.
- Testing the API output.
