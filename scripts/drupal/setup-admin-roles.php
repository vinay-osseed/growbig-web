<?php

declare(strict_types=1);

use Drupal\user\Entity\Role;

$available_permissions = array_keys(\Drupal::service('user.permissions')->getPermissions());

function valid_permissions(array $permissions, array $available_permissions): array {
  $valid = [];
  $missing = [];

  foreach ($permissions as $permission) {
    if (in_array($permission, $available_permissions, TRUE)) {
      $valid[] = $permission;
    }
    else {
      $missing[] = $permission;
    }
  }

  if ($missing) {
    print "Skipped unavailable permissions:\n";
    foreach ($missing as $permission) {
      print "- {$permission}\n";
    }
  }

  return array_values(array_unique($valid));
}

function ensure_role(string $id, string $label, array $permissions, array $available_permissions): void {
  $role = Role::load($id);

  if (!$role) {
    $role = Role::create([
      'id' => $id,
      'label' => $label,
    ]);
    print "Created role: {$id}\n";
  }
  else {
    $role->set('label', $label);
    print "Updated role: {$id}\n";
  }

  foreach (valid_permissions($permissions, $available_permissions) as $permission) {
    $role->grantPermission($permission);
  }

  $role->save();

  print "Saved role: {$id}\n";
}

$common_admin = [
  'access administration pages',
  'access content overview',
  'view the administration theme',
];

$content_permissions = [
  ...$common_admin,
  'administer nodes',
  'create site_page content',
  'edit any site_page content',
  'delete any site_page content',
  'create service content',
  'edit any service content',
  'delete any service content',
  'create partner content',
  'edit any partner content',
  'delete any partner content',
  'create team_member content',
  'edit any team_member content',
  'delete any team_member content',
  'create job content',
  'edit any job content',
  'delete any job content',
  'administer media',
  'access media overview',
  'create media',
  'edit any media',
  'delete any media',
];

$hr_permissions = [
  ...$common_admin,
  'access content overview',
  'create job content',
  'edit any job content',
  'delete any job content',
  'access webform overview',
  'view any webform submission',
  'edit any webform submission',
  'delete any webform submission',
];

$form_permissions = [
  ...$common_admin,
  'access webform overview',
  'view any webform submission',
  'edit any webform submission',
  'delete any webform submission',
];

$analytics_permissions = [
  ...$common_admin,
  'access site reports',
];

$developer_permissions = [
  ...$common_admin,
  'administer site configuration',
  'administer permissions',
  'administer users',
  'administer account settings',
  'administer content types',
  'administer nodes',
  'administer menu',
  'administer media',
  'administer webform',
  'access site reports',
  'access webform overview',
  'view any webform submission',
  'edit any webform submission',
  'delete any webform submission',
];

ensure_role('site_developer', 'Site Developer', $developer_permissions, $available_permissions);
ensure_role('content_admin', 'Content Admin', $content_permissions, $available_permissions);
ensure_role('hr_manager', 'HR Manager', $hr_permissions, $available_permissions);
ensure_role('form_manager', 'Form Manager', $form_permissions, $available_permissions);
ensure_role('analytics_viewer', 'Analytics Viewer', $analytics_permissions, $available_permissions);

print "Admin roles setup complete.\n";
