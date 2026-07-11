<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;
use Drupal\site_platform_admin\Service\SiteSetupStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Setup wizard form for first-run site configuration.
 */
final class SiteSetupWizardForm extends FormBase {

  /**
   * Constructs the setup wizard form.
   */
  public function __construct(
    private readonly SiteSetupStorage $setupStorage,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_admin.setup_storage'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'site_platform_admin_setup_wizard_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $values = $this->setupStorage->getValues();

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'site_platform_admin/site_setup';
    $form['#attributes']['class'][] = 'site-setup-wizard';

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-setup-wizard__intro']],
      'title' => [
        '#markup' => '<h2>Setup Wizard</h2>',
      ],
      'description' => [
        '#markup' => '<p>Configure the site identity, URLs, branding, forms, menus, roles, and analytics values used by this decoupled site platform.</p>',
      ],
    ];

    $form['setup'] = [
      '#type' => 'details',
      '#title' => $this->t('Setup Mode'),
      '#open' => TRUE,
    ];

    $form['setup']['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#options' => [
        'single' => $this->t('Single site'),
        'multi' => $this->t('Multi-site / multi-brand'),
      ],
      '#default_value' => (string) ($values['mode'] ?? 'single'),
      '#required' => TRUE,
    ];

    $form['setup']['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('Environment'),
      '#options' => [
        '' => $this->t('- Select -'),
        'local' => $this->t('Local'),
        'stage' => $this->t('Stage'),
        'production' => $this->t('Production'),
      ],
      '#default_value' => (string) ($values['environment'] ?? ''),
    ];

    $form['site'] = [
      '#type' => 'details',
      '#title' => $this->t('Site Identity and URLs'),
      '#open' => TRUE,
    ];

    $form['site']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site name'),
      '#default_value' => (string) ($values['site']['name'] ?? ''),
      '#required' => TRUE,
    ];

    $form['site']['key'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Site key'),
      '#default_value' => (string) ($values['site']['key'] ?? ''),
      '#machine_name' => [
        'exists' => [$this, 'siteKeyExists'],
      ],
      '#required' => TRUE,
    ];

    $form['site']['primary_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Primary domain'),
      '#description' => $this->t('Example: growbig-web.ddev.site'),
      '#default_value' => (string) ($values['site']['primary_domain'] ?? ''),
      '#required' => TRUE,
    ];

    $form['site']['frontend_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Frontend URL'),
      '#default_value' => (string) ($values['site']['frontend_url'] ?? ''),
      '#required' => TRUE,
    ];

    $form['site']['admin_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Admin URL'),
      '#default_value' => (string) ($values['site']['admin_url'] ?? ''),
      '#required' => TRUE,
    ];

    $form['site']['api_url'] = [
      '#type' => 'url',
      '#title' => $this->t('API URL'),
      '#default_value' => (string) ($values['site']['api_url'] ?? ''),
      '#required' => TRUE,
    ];

    $form['contact'] = [
      '#type' => 'details',
      '#title' => $this->t('Company and Contact'),
      '#open' => TRUE,
    ];

    $form['contact']['company_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company name'),
      '#default_value' => (string) ($values['contact']['company_name'] ?? ''),
      '#required' => TRUE,
    ];

    $form['contact']['primary_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Primary email'),
      '#default_value' => (string) ($values['contact']['primary_email'] ?? ''),
      '#required' => TRUE,
    ];

    $form['contact']['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
      '#default_value' => (string) ($values['contact']['country'] ?? ''),
      '#required' => TRUE,
    ];

    $branding = is_array($values['branding'] ?? NULL) ? $values['branding'] : [];

    $form['branding'] = [
      '#type' => 'details',
      '#title' => $this->t('Branding, Logos, and Icons'),
      '#open' => TRUE,
      '#description' => $this->t('Upload frontend-safe brand assets used by the header, footer, browser title bar, mobile app icon, and social sharing previews.'),
    ];

    $form['branding']['theme_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Theme color'),
      '#default_value' => (string) ($branding['theme_color'] ?? '#0f62fe'),
    ];

    $form['branding']['site_title_bar'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Browser title bar text'),
      '#description' => $this->t('Used as the default title text for the browser tab and frontend metadata.'),
      '#default_value' => (string) ($branding['site_title_bar'] ?? ''),
    ];

    $form['branding']['header_logo'] = $this->buildManagedFileField(
      $this->t('Header logo'),
      $this->t('Main logo used in the website header. Recommended: SVG, PNG, WebP, or JPG.'),
      'svg png jpg jpeg webp',
      $this->defaultFileValue($branding, 'header_logo')
    );

    $form['branding']['header_logo_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header logo alt text'),
      '#default_value' => (string) ($branding['header_logo_alt'] ?? ''),
    ];

    $form['branding']['footer_logo'] = $this->buildManagedFileField(
      $this->t('Footer logo'),
      $this->t('Optional alternate logo used in the footer.'),
      'svg png jpg jpeg webp',
      $this->defaultFileValue($branding, 'footer_logo')
    );

    $form['branding']['footer_logo_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer logo alt text'),
      '#default_value' => (string) ($branding['footer_logo_alt'] ?? ''),
    ];

    $form['branding']['favicon_ico'] = $this->buildManagedFileField(
      $this->t('Title bar favicon / ICO'),
      $this->t('Browser tab icon. Recommended: ICO, PNG, or SVG.'),
      'ico png svg',
      $this->defaultFileValue($branding, 'favicon_ico')
    );

    $form['branding']['app_icon'] = $this->buildManagedFileField(
      $this->t('App / mobile icon'),
      $this->t('Icon used by mobile homescreen or PWA-style frontend integrations. Recommended: PNG 512x512.'),
      'png jpg jpeg webp',
      $this->defaultFileValue($branding, 'app_icon')
    );

    $form['branding']['social_image'] = $this->buildManagedFileField(
      $this->t('Default social sharing image'),
      $this->t('Default Open Graph/social preview image. Recommended: 1200x630 PNG/JPG/WebP.'),
      'png jpg jpeg webp',
      $this->defaultFileValue($branding, 'social_image')
    );

    $form['branding']['footer_copyright'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer copyright text'),
      '#description' => $this->t('Example: © 2026 GrowBig. All rights reserved.'),
      '#default_value' => (string) ($branding['footer_copyright'] ?? ''),
    ];

    $form['setup_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Setup Options'),
      '#open' => TRUE,
    ];

    foreach ([
      'create_default_pages' => $this->t('Create default pages'),
      'create_default_menus' => $this->t('Create default menus'),
      'create_default_forms' => $this->t('Create default forms'),
      'create_default_roles' => $this->t('Create default roles'),
      'create_demo_content' => $this->t('Create demo content'),
    ] as $key => $title) {
      $form['setup_options'][$key] = [
        '#type' => 'checkbox',
        '#title' => $title,
        '#default_value' => (bool) ($values['setup_options'][$key] ?? FALSE),
      ];
    }

    $form['analytics'] = [
      '#type' => 'details',
      '#title' => $this->t('Analytics'),
      '#open' => FALSE,
    ];

    $form['analytics']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Google Analytics'),
      '#default_value' => (bool) ($values['analytics']['enabled'] ?? FALSE),
    ];

    $form['analytics']['measurement_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Analytics measurement ID'),
      '#description' => $this->t('Example: G-XXXXXXXXXX'),
      '#default_value' => (string) ($values['analytics']['measurement_id'] ?? ''),
    ];

    $form['extra_sites'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional Sites / Brands'),
      '#open' => FALSE,
      '#description' => $this->t('Optional setup rows for additional sites or brands. These are validated and exposed as setup metadata. Real Domain records are not created unless a Domain module is added later.'),
    ];

    $extra_sites = is_array($values['extra_sites'] ?? NULL) ? $values['extra_sites'] : [];

    for ($index = 0; $index < 3; $index++) {
      $site = is_array($extra_sites[$index] ?? NULL) ? $extra_sites[$index] : [];
      $form['extra_sites'][$index] = [
        '#type' => 'details',
        '#title' => $this->t('Additional site @number', ['@number' => $index + 1]),
        '#open' => $index === 0 && $site !== [],
      ];

      $form['extra_sites'][$index]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#default_value' => (string) ($site['name'] ?? ''),
      ];

      $form['extra_sites'][$index]['key'] = [
        '#type' => 'machine_name',
        '#title' => $this->t('Key'),
        '#default_value' => (string) ($site['key'] ?? ''),
        '#machine_name' => [
          'exists' => [$this, 'siteKeyExists'],
        ],
      ];

      $form['extra_sites'][$index]['primary_domain'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Primary domain'),
        '#default_value' => (string) ($site['primary_domain'] ?? ''),
      ];

      $form['extra_sites'][$index]['frontend_url'] = [
        '#type' => 'url',
        '#title' => $this->t('Frontend URL'),
        '#default_value' => (string) ($site['frontend_url'] ?? ''),
      ];

      $form['extra_sites'][$index]['admin_url'] = [
        '#type' => 'url',
        '#title' => $this->t('Admin URL'),
        '#default_value' => (string) ($site['admin_url'] ?? ''),
      ];

      $form['extra_sites'][$index]['api_url'] = [
        '#type' => 'url',
        '#title' => $this->t('API URL'),
        '#default_value' => (string) ($site['api_url'] ?? ''),
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save setup values'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Machine-name callback for setup site keys.
   */
  public function siteKeyExists(string $value): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $primary_key = (string) $form_state->getValue(['site', 'key']);
    $primary_domain = (string) $form_state->getValue(['site', 'primary_domain']);

    $seen_keys = [];
    $seen_domains = [];

    if ($primary_key !== '') {
      $seen_keys[] = $primary_key;
    }

    if ($primary_domain !== '') {
      $seen_domains[] = $primary_domain;
    }

    $extra_sites = $form_state->getValue('extra_sites');
    if (!is_array($extra_sites)) {
      return;
    }

    foreach ($extra_sites as $index => $site) {
      if (!is_array($site)) {
        continue;
      }

      $has_any_value = trim(implode('', array_map('strval', $site))) !== '';
      if (!$has_any_value) {
        continue;
      }

      foreach (['name', 'key', 'primary_domain'] as $field) {
        if (trim((string) ($site[$field] ?? '')) === '') {
          $form_state->setErrorByName(
            'extra_sites][' . $index . '][' . $field,
            $this->t('Additional site @number requires @field.', [
              '@number' => $index + 1,
              '@field' => $field,
            ])
          );
        }
      }

      $key = (string) ($site['key'] ?? '');
      if ($key !== '') {
        if (in_array($key, $seen_keys, TRUE)) {
          $form_state->setErrorByName(
            'extra_sites][' . $index . '][key',
            $this->t('Additional site @number key must be unique.', [
              '@number' => $index + 1,
            ])
          );
        }

        $seen_keys[] = $key;
      }

      $domain = (string) ($site['primary_domain'] ?? '');
      if ($domain !== '') {
        if (in_array($domain, $seen_domains, TRUE)) {
          $form_state->setErrorByName(
            'extra_sites][' . $index . '][primary_domain',
            $this->t('Additional site @number domain must be unique.', [
              '@number' => $index + 1,
            ])
          );
        }

        $seen_domains[] = $domain;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $branding_values = $form_state->getValue('branding');
    $branding_values = is_array($branding_values) ? $branding_values : [];

    $header_logo = $this->getFirstFileId($branding_values['header_logo'] ?? []);
    $footer_logo = $this->getFirstFileId($branding_values['footer_logo'] ?? []);
    $favicon_ico = $this->getFirstFileId($branding_values['favicon_ico'] ?? []);
    $app_icon = $this->getFirstFileId($branding_values['app_icon'] ?? []);
    $social_image = $this->getFirstFileId($branding_values['social_image'] ?? []);

    $this->markFilesPermanent([
      $header_logo,
      $footer_logo,
      $favicon_ico,
      $app_icon,
      $social_image,
    ]);

    $extra_sites = [];
    $extra_values = $form_state->getValue('extra_sites');
    if (is_array($extra_values)) {
      foreach ($extra_values as $site) {
        if (!is_array($site)) {
          continue;
        }

        $has_any_value = trim(implode('', array_map('strval', $site))) !== '';
        if (!$has_any_value) {
          continue;
        }

        $extra_sites[] = [
          'name' => trim((string) ($site['name'] ?? '')),
          'key' => trim((string) ($site['key'] ?? '')),
          'primary_domain' => trim((string) ($site['primary_domain'] ?? '')),
          'frontend_url' => trim((string) ($site['frontend_url'] ?? '')),
          'admin_url' => trim((string) ($site['admin_url'] ?? '')),
          'api_url' => trim((string) ($site['api_url'] ?? '')),
        ];
      }
    }

    $values = [
      'mode' => (string) $form_state->getValue(['setup', 'mode']),
      'environment' => (string) $form_state->getValue(['setup', 'environment']),
      'site' => [
        'name' => trim((string) $form_state->getValue(['site', 'name'])),
        'key' => trim((string) $form_state->getValue(['site', 'key'])),
        'primary_domain' => trim((string) $form_state->getValue(['site', 'primary_domain'])),
        'frontend_url' => trim((string) $form_state->getValue(['site', 'frontend_url'])),
        'admin_url' => trim((string) $form_state->getValue(['site', 'admin_url'])),
        'api_url' => trim((string) $form_state->getValue(['site', 'api_url'])),
      ],
      'contact' => [
        'company_name' => trim((string) $form_state->getValue(['contact', 'company_name'])),
        'primary_email' => trim((string) $form_state->getValue(['contact', 'primary_email'])),
        'country' => trim((string) $form_state->getValue(['contact', 'country'])),
      ],
      'branding' => [
        'theme_color' => (string) ($branding_values['theme_color'] ?? '#0f62fe'),
        'site_title_bar' => trim((string) ($branding_values['site_title_bar'] ?? '')),
        'header_logo' => $header_logo,
        'header_logo_alt' => trim((string) ($branding_values['header_logo_alt'] ?? '')),
        'footer_logo' => $footer_logo,
        'footer_logo_alt' => trim((string) ($branding_values['footer_logo_alt'] ?? '')),
        'favicon_ico' => $favicon_ico,
        'app_icon' => $app_icon,
        'social_image' => $social_image,
        'footer_copyright' => trim((string) ($branding_values['footer_copyright'] ?? '')),
        'logo' => $header_logo,
        'favicon' => $favicon_ico,
      ],
      'setup_options' => [
        'create_default_pages' => (bool) $form_state->getValue(['setup_options', 'create_default_pages']),
        'create_default_menus' => (bool) $form_state->getValue(['setup_options', 'create_default_menus']),
        'create_default_forms' => (bool) $form_state->getValue(['setup_options', 'create_default_forms']),
        'create_default_roles' => (bool) $form_state->getValue(['setup_options', 'create_default_roles']),
        'create_demo_content' => (bool) $form_state->getValue(['setup_options', 'create_demo_content']),
      ],
      'analytics' => [
        'enabled' => (bool) $form_state->getValue(['analytics', 'enabled']),
        'measurement_id' => trim((string) $form_state->getValue(['analytics', 'measurement_id'])),
      ],
      'extra_sites' => $extra_sites,
    ];

    $this->setupStorage->saveValues($values);

    $this->messenger()->addStatus($this->t('Setup values saved. You can now prepare the setup run.'));
    $form_state->setRedirect('site_platform_admin.site_setup');
  }

  /**
   * Builds a managed file field.
   */
  private function buildManagedFileField(
    mixed $title,
    mixed $description,
    string $extensions,
    array $default_value,
  ): array {
    return [
      '#type' => 'managed_file',
      '#title' => $title,
      '#description' => $description,
      '#upload_location' => 'public://site-setup/',
      '#upload_validators' => [
        'file_validate_extensions' => [$extensions],
      ],
      '#default_value' => $default_value,
    ];
  }

  /**
   * Gets the default managed file value.
   */
  private function defaultFileValue(array $values, string $key): array {
    $value = $values[$key] ?? 0;
    if (is_array($value)) {
      $value = reset($value);
    }

    $fid = (int) $value;

    return $fid > 0 ? [$fid] : [];
  }

  /**
   * Gets first uploaded file ID.
   */
  private function getFirstFileId(mixed $value): int {
    if (is_array($value)) {
      $value = reset($value);
    }

    return (int) $value;
  }

  /**
   * Marks uploaded files as permanent.
   */
  private function markFilesPermanent(array $file_ids): void {
    $file_ids = array_filter(array_map('intval', $file_ids));
    if ($file_ids === []) {
      return;
    }

    $storage = $this->entityTypeManager->getStorage('file');

    foreach ($file_ids as $file_id) {
      $file = $storage->load($file_id);
      if ($file instanceof FileInterface) {
        $file->setPermanent();
        $file->save();
      }
    }
  }

}
