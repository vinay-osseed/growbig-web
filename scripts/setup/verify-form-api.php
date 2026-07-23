<?php

declare(strict_types=1);

use Drupal\Component\Serialization\Yaml;
use Drupal\node\NodeInterface;
use Drupal\webform\Entity\Webform;

/**
 * Loads a Site Profile by key.
 */
function sp_form_load_site(string $site_key): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_profile')
    ->condition('field_site_key', $site_key)
    ->range(0, 1)
    ->execute();

  if (!$ids) {
    throw new RuntimeException("Missing Site Profile $site_key.");
  }

  $node = $storage->load(reset($ids));
  if (!$node instanceof NodeInterface) {
    throw new RuntimeException("Could not load Site Profile $site_key.");
  }

  return $node;
}

/**
 * Creates or updates a Webform.
 */
function sp_form_save_webform(string $site_key, array $values, array $fields): Webform {
  $webform_id = sp_form_machine_name($site_key . '_' . $values['key']);
  $webform = Webform::load($webform_id);
  if (!$webform instanceof Webform) {
    $webform = Webform::create([
      'id' => $webform_id,
    ]);
  }

  $webform->set('title', $values['label']);
  $webform->set('description', $values['description']);
  $webform->set('status', 'open');

  $settings = $webform->get('settings') ?? [];
  $settings = is_array($settings) ? $settings : [];
  $settings['confirmation_type'] = 'message';
  $settings['confirmation_message'] = $values['success_message'];
  $webform->set('settings', $settings);

  $elements = [];
  foreach ($fields as $field) {
    $element = [
      '#type' => sp_form_webform_type($field['type']),
      '#title' => $field['label'],
      '#required' => (bool) $field['required'],
      '#weight' => (int) $field['weight'],
    ];
    if (!empty($field['placeholder'])) {
      $element['#placeholder'] = $field['placeholder'];
    }
    if (!empty($field['help'])) {
      $element['#description'] = $field['help'];
    }
    if (!empty($field['options']) && is_array($field['options'])) {
      $element['#options'] = $field['options'];
    }
    $elements[$field['key']] = $element;
  }

  if (method_exists($webform, 'setElements')) {
    $webform->setElements($elements);
  }
  else {
    $webform->set('elements', Yaml::encode($elements));
  }

  $webform->save();

  return $webform;
}

/**
 * Creates or updates a legacy Site Form wrapper node.
 */
function sp_form_save_form(NodeInterface $site, array $values): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_form')
    ->condition('field_site_profile.target_id', $site->id())
    ->condition('field_form_key', $values['key'])
    ->range(0, 1)
    ->execute();

  $node = NULL;
  if ($ids) {
    $loaded = $storage->load(reset($ids));
    if ($loaded instanceof NodeInterface) {
      $node = $loaded;
    }
  }

  if (!$node instanceof NodeInterface) {
    $node = $storage->create([
      'type' => 'site_form',
      'title' => $values['title'],
      'status' => 1,
      'uid' => 1,
    ]);
  }

  $node->setTitle($values['title']);
  $node->set('field_site_profile', ['target_id' => $site->id()]);
  $node->set('field_form_key', $values['key']);
  $node->set('field_form_label', $values['label']);
  $node->set('field_form_description', $values['description']);
  $node->set('field_form_success_message', $values['success_message']);
  $node->set('field_form_is_active', TRUE);
  $node->set('field_is_demo', TRUE);
  $node->set('field_demo_source', 'phase_09_verify');
  $node->save();

  return $node;
}

/**
 * Creates or updates a legacy Site Form Field wrapper node.
 */
function sp_form_save_field(NodeInterface $site, NodeInterface $form, array $values): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_form_field')
    ->condition('field_site_form.target_id', $form->id())
    ->condition('field_form_field_key', $values['key'])
    ->range(0, 1)
    ->execute();

  $node = NULL;
  if ($ids) {
    $loaded = $storage->load(reset($ids));
    if ($loaded instanceof NodeInterface) {
      $node = $loaded;
    }
  }

  if (!$node instanceof NodeInterface) {
    $node = $storage->create([
      'type' => 'site_form_field',
      'title' => $values['label'],
      'status' => 1,
      'uid' => 1,
    ]);
  }

  $node->setTitle($values['label']);
  $node->set('field_site_profile', ['target_id' => $site->id()]);
  $node->set('field_site_form', ['target_id' => $form->id()]);
  $node->set('field_form_field_key', $values['key']);
  $node->set('field_form_field_type', $values['type']);
  $node->set('field_form_field_label', $values['label']);
  $node->set('field_form_field_required', $values['required']);
  $node->set('field_form_field_placeholder', $values['placeholder'] ?? '');
  $node->set('field_form_field_help', $values['help'] ?? '');
  $node->set('field_form_field_options', is_array($values['options'] ?? NULL) ? json_encode($values['options']) : ($values['options'] ?? ''));
  $node->set('field_form_field_weight', $values['weight']);
  $node->set('field_is_demo', TRUE);
  $node->set('field_demo_source', 'phase_09_verify');
  $node->save();

  return $node;
}

/**
 * Normalizes a config machine name.
 */
function sp_form_machine_name(string $value): string {
  $value = strtolower(trim($value));
  $value = preg_replace('/[^a-z0-9_]+/', '_', str_replace('-', '_', $value)) ?: $value;
  $value = trim($value, '_');
  return $value !== '' ? $value : 'form';
}

/**
 * Maps public field type to Webform element type.
 */
function sp_form_webform_type(string $type): string {
  return match ($type) {
    'text' => 'textfield',
    default => $type,
  };
}

$growbig = sp_form_load_site('growbig');
$osseed = sp_form_load_site('osseed');

$growbig_values = [
  'title' => 'GrowBig Contact Form',
  'key' => 'contact',
  'label' => 'Contact us',
  'description' => 'Send a message to GrowBig.',
  'success_message' => 'Thanks for contacting GrowBig.',
];
$growbig_fields = [
  ['key' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => TRUE, 'placeholder' => 'Your name', 'weight' => 0],
  ['key' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => TRUE, 'placeholder' => 'you@example.com', 'weight' => 10],
  ['key' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => TRUE, 'placeholder' => 'How can we help?', 'weight' => 20],
];

$osseed_values = [
  'title' => 'OSSeed Contact Form',
  'key' => 'contact',
  'label' => 'Contact OSSeed',
  'description' => 'Send a message to OSSeed.',
  'success_message' => 'Thanks for contacting OSSeed.',
];
$osseed_fields = [
  ['key' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => TRUE, 'placeholder' => 'you@example.com', 'weight' => 0],
  ['key' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => FALSE, 'placeholder' => 'Message', 'weight' => 10],
];

sp_form_save_webform('growbig', $growbig_values, $growbig_fields);
sp_form_save_webform('osseed', $osseed_values, $osseed_fields);

$growbig_contact = sp_form_save_form($growbig, $growbig_values);
$osseed_contact = sp_form_save_form($osseed, $osseed_values);

foreach ($growbig_fields as $field) {
  sp_form_save_field($growbig, $growbig_contact, $field);
}

foreach ($osseed_fields as $field) {
  sp_form_save_field($osseed, $osseed_contact, $field);
}

drupal_flush_all_caches();

echo "Webform-backed Form API demo data prepared.\n";
