# Phase 12P: Multi-site Setup Rows

## Completed Scope

This phase adds initial multi-site setup row support.

## Added

- stronger `extra_sites` YAML validation
- duplicate key/domain checks
- `site-platform:setup-sites`
- `sp-setup-sites`
- `./scripts/site-setup.sh sites`
- multi-site YAML documentation

## Behavior

This phase does not create multi-site/domain records.

It prepares setup values and inspection so multi-site creation can be implemented safely later.

## Safety

No content, pages, forms, roles, domains, or config are deleted.

The new command only reads setup runtime values.

## Next Phase

Phase 12Q should add site profile API sync from setup values or begin actual multi-site/domain creation planning.
