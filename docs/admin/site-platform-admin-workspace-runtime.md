# Site Platform Admin Workspace Runtime

This is the safe runtime version of the Site Platform admin workspace.

It groups the backend into sections while keeping the existing API and data model intact:

- Sites
- Landing Pages
- Content Blocks
- Media Assets
- Forms
- Menus
- Legacy Wrappers

This version does not import or export Drupal config. It only updates module runtime code, routes, and menu links. The listing tables deliberately output plain strings such as edit paths instead of link render arrays, so Drupal does not receive unsafe `Url` objects while rendering table attributes.

Real forms continue to use Drupal Webform. Legacy Site Form records remain only compatibility wrappers.
