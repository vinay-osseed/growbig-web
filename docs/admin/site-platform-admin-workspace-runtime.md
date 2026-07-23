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

This version does not import or export Drupal config. It only updates module runtime code, routes, and menu links.

The listing tables now expose clickable CRUD actions through Drupal's native screens:

- node records get View, Edit, and Delete links
- each section has an Add link where the bundle supports creation
- Webforms get Manage, View, Results, Settings, and Delete links
- menu and media sections also link to the relevant Drupal native admin areas

The links are rendered as escaped HTML strings and not as `Url` objects in table rows, so Drupal does not receive unsafe `Url` values while rendering table attributes.

Real forms continue to use Drupal Webform. Legacy Site Form records remain only compatibility wrappers.
