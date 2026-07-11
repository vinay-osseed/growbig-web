<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\site_platform_admin\Service\SiteSetupRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the safe setup run preparation form.
 */
final class SiteSetupRunForm extends FormBase {

  /**
   * Constructs a setup run form.
   */
  public function __construct(
    private readonly SiteSetupRunner $setupRunner,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_admin.setup_runner')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'site_platform_admin_setup_run';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $missing = $this->setupRunner->getMissingRequiredValues();
    $preview = $this->setupRunner->getPreview();

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup-run__intro',
        ],
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Prepare Setup Run'),
      ],
      'description' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('This safe runner validates setup values and applies non-destructive config only. Content creation and reset actions will be added later.'),
      ],
    ];

    if ($missing !== []) {
      $wizard = Link::fromTextAndUrl(
        $this->t('Open Setup Wizard'),
        Url::fromRoute('site_platform_admin.site_setup_wizard')
      )->toRenderable();

      $wizard['#attributes']['class'][] = 'button';
      $wizard['#attributes']['class'][] = 'button--primary';

      $form['missing'] = [
        '#theme' => 'item_list',
        '#title' => $this->t('Missing Required Values'),
        '#items' => array_values($missing),
      ];

      $form['wizard'] = $wizard;

      return $form;
    }

    $form['preview'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Setup Preview'),
      '#items' => [
        $this->t('Mode: @value', ['@value' => $preview['mode']]),
        $this->t('Environment: @value', ['@value' => $preview['environment']]),
        $this->t('Site Name: @value', ['@value' => $preview['site_name']]),
        $this->t('Site Key: @value', ['@value' => $preview['site_key']]),
        $this->t('Primary Domain: @value', ['@value' => $preview['primary_domain']]),
        $this->t('Frontend URL: @value', ['@value' => $preview['frontend_url']]),
        $this->t('API URL: @value', ['@value' => $preview['api_url']]),
        $this->t('Create Default Roles: @value', [
          '@value' => $preview['create_default_roles'] ? $this->t('Yes') : $this->t('No'),
        ]),
        $this->t('Create Default Pages: @value', [
          '@value' => $preview['create_default_pages'] ? $this->t('Yes') : $this->t('No'),
        ]),
        $this->t('Create Default Menus: @value', [
          '@value' => $preview['create_default_menus'] ? $this->t('Yes') : $this->t('No'),
        ]),
        $this->t('Create Default Forms: @value', [
          '@value' => $preview['create_default_forms'] ? $this->t('Yes') : $this->t('No'),
        ]),
        $this->t('Create Demo Content: @value', [
          '@value' => $preview['create_demo_content'] ? $this->t('Yes') : $this->t('No'),
        ]),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Prepare Setup Run'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {
      $result = $this->setupRunner->run();

      $this->messenger()->addStatus($this->t('Setup run prepared for @site using setup ID @id.', [
        '@site' => $result['site_name'],
        '@id' => $result['setup_id'],
      ]));

      $form_state->setRedirect('site_platform_admin.site_setup');
    }
    catch (\InvalidArgumentException $exception) {
      $this->messenger()->addError($exception->getMessage());
      $form_state->setRebuild(TRUE);
    }
  }

}
