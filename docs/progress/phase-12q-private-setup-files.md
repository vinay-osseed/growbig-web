# Phase 12Q: Private Setup File Protection

## Completed Scope

This phase protects environment-specific setup import files from accidental commits.

## Added

- `.gitignore` entries for private setup files
- setup CLI documentation for private setup YAML files

## Committed Setup File

Only this setup YAML file should be committed:

- `setup/site.example.yml`

## Ignored Setup Files

These are ignored:

- `setup/site.yml`
- `setup/*.local.yml`
- `setup/*.stage.yml`
- `setup/*.prod.yml`
- `setup/*.production.yml`

## Safety

This helps prevent committing production URLs, private analytics IDs, or environment-specific setup values.

## Next Phase

Phase 12R should sync setup values into the Site API or improve setup wizard/run UI polish.
