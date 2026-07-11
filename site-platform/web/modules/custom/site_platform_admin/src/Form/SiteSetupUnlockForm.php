<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\site_platform_admin\Service\SiteSetupStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the setup unlock form.
 */
final class SiteSetupUnlockForm extends FormBase {

  /**
   * Constructs a setup unlock form.
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
    return 'site_platform_admin_setup_unlock';
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
        '#value' => $this->t('Unlock Setup'),
      ],
      'description' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Unlocking allows the setup wizard and setup runner to be used again. It does not delete pages, roles, forms, content, files, or submissions.'),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Unlock Setup'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->setupStorage->unlockSetup();
    $this->messenger()->addStatus($this->t('Setup has been unlocked.'));
    $form_state->setRedirect('site_platform_admin.site_setup');
  }

}
