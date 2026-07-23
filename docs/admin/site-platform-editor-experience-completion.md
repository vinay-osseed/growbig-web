# Site Platform Editor Experience Completion

This package fixes the remaining backend editor experience problems after the backend runtime was technically working.

## What it changes

- Replaces temporary logo/favicon URL editing with Drupal Media Library selectors.
- Adds a structured multi-value Social links field instead of an unstructured textarea.
- Uses dropdown selection for Site Profile references on the main editor forms.
- Hides technical fields such as Page template, Weight, demo flags, and temporary media URL fields from primary editor forms.
- Adds a Frontend/API handoff panel on Landing Page create/edit/view screens so the editor and UI developer can quickly copy the useful endpoints.
- Adds a Media Library selector to Hero components and Media Asset Metadata.
- Keeps old URL/text fields in the data model as compatibility fallback, but removes them from the main editor workflow.

## What it avoids

It does not run a full config import or export:

```bash
ddev drush cim -y
ddev drush cex -y
```

It applies only targeted active config needed for the editor experience.

## Verify

Run:

```bash
./scripts/setup/finalize-site-platform-editor-experience.sh
```

Expected final line:

```text
Site Platform editor experience verification passed.
```
