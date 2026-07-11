<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\site_platform_admin\Service\SiteSetupStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the first-run site setup wizard form.
 */
final class SiteSetupWizardForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'site_platform_admin_setup_wizard';
  }

  /**
   * Constructs the setup wizard form.
   */
  public function __construct(
    private readonly SiteSetupStorage $setupStorage,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_admin.setup_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->setupStorage->getValues();

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup-wizard__intro',
        ],
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Fresh Site Setup Wizard'),
      ],
      'description' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Enter the minimum required values. Optional branding, analytics, and content can be completed now or later.'),
      ],
    ];

    $form['setup_mode'] = [
      '#type' => 'details',
      '#title' => $this->t('Setup Mode'),
      '#open' => TRUE,
    ];

    $form['setup_mode']['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Site Setup Mode'),
      '#options' => [
        'single' => $this->t('Single Site'),
        'multiple' => $this->t('Multiple Sites / Brands'),
      ],
      '#default_value' => $config['mode'] ?: 'single',
      '#required' => TRUE,
    ];

    $form['setup_mode']['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('Environment'),
      '#options' => [
        '' => $this->t('- Select -'),
        'local' => $this->t('Local'),
        'dev' => $this->t('Development'),
        'stage' => $this->t('Stage'),
        'prod' => $this->t('Production'),
      ],
      '#default_value' => $config['environment'] ?: '',
      '#description' => $this->t('Production defaults keep demo content disabled.'),
    ];

    $form['site'] = [
      '#type' => 'details',
      '#title' => $this->t('Site Identity'),
      '#open' => TRUE,
    ];

    $form['site']['site_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site Name'),
      '#default_value' => $config['site']['name'] ?: '',
      '#required' => TRUE,
      '#maxlength' => 128,
    ];

    $form['site']['site_key'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Site Key'),
      '#default_value' => $config['site']['key'] ?: '',
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this, 'siteKeyExists'],
        'source' => [
          'site',
          'site_name',
        ],
      ],
      '#description' => $this->t('Use lowercase letters, numbers, and underscores only. This key is used by frontend and setup scripts.'),
    ];

    $form['site']['primary_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Primary Domain'),
      '#default_value' => $config['site']['primary_domain'] ?: '',
      '#placeholder' => 'example.com',
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['site']['frontend_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Frontend URL'),
      '#default_value' => $config['site']['frontend_url'] ?: '',
      '#placeholder' => 'https://www.example.com',
      '#required' => TRUE,
    ];

    $form['site']['admin_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Admin URL'),
      '#default_value' => $config['site']['admin_url'] ?: '',
      '#placeholder' => 'https://admin.example.com',
      '#required' => TRUE,
    ];

    $form['site']['api_url'] = [
      '#type' => 'url',
      '#title' => $this->t('API URL'),
      '#default_value' => $config['site']['api_url'] ?: '',
      '#placeholder' => 'https://api.example.com',
      '#required' => TRUE,
    ];

    $form['contact'] = [
      '#type' => 'details',
      '#title' => $this->t('Business / Contact Information'),
      '#open' => TRUE,
    ];

    $form['contact']['company_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
      '#default_value' => $config['contact']['company_name'] ?: '',
      '#required' => TRUE,
      '#maxlength' => 128,
    ];

    $form['contact']['primary_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Primary Email'),
      '#default_value' => $config['contact']['primary_email'] ?: '',
      '#required' => TRUE,
    ];

    $form['contact']['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
      '#default_value' => $config['contact']['country'] ?: '',
      '#required' => TRUE,
      '#maxlength' => 128,
    ];

    $form['branding'] = [
      '#type' => 'details',
      '#title' => $this->t('Frontend-safe Defaults'),
      '#open' => FALSE,
      '#description' => $this->t('These values help the decoupled frontend render cleanly. They can be changed later.'),
    ];

    $form['branding']['theme_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Theme Color'),
      '#default_value' => $config['branding']['theme_color'] ?: '#0f62fe',
      '#placeholder' => '#0f62fe',
      '#maxlength' => 16,
    ];

    $form['branding']['logo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Logo URL'),
      '#default_value' => $config['branding']['logo'] ?: '',
      '#description' => $this->t('Optional. If empty, frontend should render text branding or a default placeholder.'),
      '#maxlength' => 512,
    ];

    $form['branding']['favicon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Favicon URL'),
      '#default_value' => $config['branding']['favicon'] ?: '',
      '#description' => $this->t('Optional. If empty, frontend should use its fallback favicon.'),
      '#maxlength' => 512,
    ];

    $form['setup_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Setup Options'),
      '#open' => TRUE,
    ];

    $form['setup_options']['create_default_pages'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create Default Pages'),
      '#default_value' => $config['setup_options']['create_default_pages'] ?? TRUE,
    ];

    $form['setup_options']['create_default_menus'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create Default Menus'),
      '#default_value' => $config['setup_options']['create_default_menus'] ?? TRUE,
    ];

    $form['setup_options']['create_default_forms'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create Default Forms'),
      '#default_value' => $config['setup_options']['create_default_forms'] ?? TRUE,
    ];

    $form['setup_options']['create_default_roles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create Default Roles'),
      '#default_value' => $config['setup_options']['create_default_roles'] ?? TRUE,
    ];

    $form['setup_options']['create_demo_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create Demo Content'),
      '#default_value' => $config['setup_options']['create_demo_content'] ?? FALSE,
      '#description' => $this->t('Keep this disabled for production unless demo content is intentionally needed.'),
    ];

    $form['analytics'] = [
      '#type' => 'details',
      '#title' => $this->t('Analytics'),
      '#open' => FALSE,
    ];

    $form['analytics']['analytics_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Analytics'),
      '#default_value' => $config['analytics']['enabled'] ?? FALSE,
      '#description' => $this->t('Environment values may override analytics setup values.'),
    ];

    $form['analytics']['analytics_measurement_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Analytics Measurement ID'),
      '#default_value' => $config['analytics']['measurement_id'] ?: '',
      '#placeholder' => 'G-XXXXXXXXXX',
      '#maxlength' => 64,
    ];

    $form['multi_site'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional Sites / Brands'),
      '#open' => FALSE,
      '#description' => $this->t('Multi-site setup runner will be added in a later phase. For now, save the setup mode and primary site values.'),
    ];

    $form['multi_site']['extra_sites_note'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Additional site rows and import support will be added after the setup runner foundation is complete.'),
    ];

    $form['next_steps'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup-wizard__next',
        ],
      ],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Saving this form stores setup values only. Setup runner, review, retry, and reset actions will be added next.'),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Machine-name callback.
   */
  public function siteKeyExists(string $value): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $site_key = (string) $form_state->getValue('site_key');
    $primary_domain = trim((string) $form_state->getValue('primary_domain'));
    $measurement_id = trim((string) $form_state->getValue('analytics_measurement_id'));

    if (!preg_match('/^[a-z0-9_]+$/', $site_key)) {
      $form_state->setErrorByName('site_key', $this->t('Site Key can contain only lowercase letters, numbers, and underscores.'));
    }

    if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $primary_domain)) {
      $form_state->setErrorByName('primary_domain', $this->t('Enter a valid domain, for example example.com.'));
    }

    foreach (['frontend_url', 'admin_url', 'api_url'] as $field_name) {
      $url = (string) $form_state->getValue($field_name);
      if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $form_state->setErrorByName($field_name, $this->t('Enter a valid absolute URL.'));
      }
    }

    $email = (string) $form_state->getValue('primary_email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('primary_email', $this->t('Enter a valid email address.'));
    }

    if ($measurement_id !== '' && !preg_match('/^G-[A-Z0-9]+$/', $measurement_id)) {
      $form_state->setErrorByName('analytics_measurement_id', $this->t('Enter a valid GA4 Measurement ID, for example G-XXXXXXXXXX.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->setupStorage->saveValues([
      'mode' => (string) $form_state->getValue('mode'),
      'environment' => (string) $form_state->getValue('environment'),
      'site' => [
        'name' => trim((string) $form_state->getValue('site_name')),
        'key' => trim((string) $form_state->getValue('site_key')),
        'primary_domain' => trim((string) $form_state->getValue('primary_domain')),
        'frontend_url' => trim((string) $form_state->getValue('frontend_url')),
        'admin_url' => trim((string) $form_state->getValue('admin_url')),
        'api_url' => trim((string) $form_state->getValue('api_url')),
      ],
      'contact' => [
        'company_name' => trim((string) $form_state->getValue('company_name')),
        'primary_email' => trim((string) $form_state->getValue('primary_email')),
        'country' => trim((string) $form_state->getValue('country')),
      ],
      'branding' => [
        'theme_color' => trim((string) $form_state->getValue('theme_color')) ?: '#0f62fe',
        'logo' => trim((string) $form_state->getValue('logo')),
        'favicon' => trim((string) $form_state->getValue('favicon')),
      ],
      'setup_options' => [
        'create_default_pages' => (bool) $form_state->getValue('create_default_pages'),
        'create_default_menus' => (bool) $form_state->getValue('create_default_menus'),
        'create_default_forms' => (bool) $form_state->getValue('create_default_forms'),
        'create_default_roles' => (bool) $form_state->getValue('create_default_roles'),
        'create_demo_content' => (bool) $form_state->getValue('create_demo_content'),
      ],
      'analytics' => [
        'enabled' => (bool) $form_state->getValue('analytics_enabled'),
        'measurement_id' => trim((string) $form_state->getValue('analytics_measurement_id')),
      ],
      'extra_sites' => [],
    ]);

    $status = $this->setupStorage->getStatus();
    $status['current_step'] = 'setup_form_saved';
    $this->setupStorage->saveStatus($status);

    $this->messenger()->addStatus($this->t('Setup values saved. The setup runner will be added in the next phase.'));

    parent::submitForm($form, $form_state);
  }

}
