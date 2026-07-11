<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\site_platform_admin\Service\SiteSetupStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the safe setup status reset form.
 */
final class SiteSetupResetStatusForm extends FormBase {

  /**
   * Constructs a setup reset status form.
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
  public function getFormId(): string {
    return 'site_platform_admin_setup_reset_status';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'site_platform_admin/site_setup';
    $form['#attributes']['class'][] = 'site-setup-complete';

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup-complete__card',
        ],
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Reset Setup Status Only'),
      ],
      'description' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('This resets only the setup runtime status. It does not delete setup values, pages, menus, forms, roles, files, submissions, or content.'),
      ],
    ];

    $form['warning'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup__notice',
        ],
      ],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Use this when setup failed or needs to be restarted without removing existing data.'),
      ],
    ];

    $form['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand this only resets setup status and does not delete created data.'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset Status Only'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->setupStorage->resetStatus();
    $this->messenger()->addStatus($this->t('Setup status has been reset. Existing setup-created data was not deleted.'));
    $form_state->setRedirect('site_platform_admin.site_setup');
  }

}
