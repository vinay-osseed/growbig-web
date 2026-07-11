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
 * Provides the setup completion and lock form.
 */
final class SiteSetupCompleteForm extends FormBase {

  /**
   * Constructs a setup complete form.
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
    return 'site_platform_admin_setup_complete';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'site_platform_admin/site_setup';

    $readiness = $this->setupRunner->getCompletionReadiness();

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
        '#value' => $this->t('Complete and Lock Setup'),
      ],
      'description' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('This verifies required setup items and locks the first-run setup workflow.'),
      ],
    ];

    if (!$readiness['ready']) {
      $run = Link::fromTextAndUrl(
        $this->t('Prepare Setup Run'),
        Url::fromRoute('site_platform_admin.site_setup_run')
      )->toRenderable();

      $run['#attributes']['class'][] = 'button';
      $run['#attributes']['class'][] = 'button--primary';

      $form['missing'] = [
        '#theme' => 'item_list',
        '#title' => $this->t('Setup is not ready yet. Missing items'),
        '#items' => $readiness['missing'],
      ];

      $form['run'] = $run;

      return $form;
    }

    $form['ready'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Ready to Complete'),
      '#items' => [
        $this->t('Required values verified'),
        $this->t('Roles verified'),
        $this->t('Default pages verified'),
        $this->t('Default forms verified'),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Complete and Lock Setup'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {
      $this->setupRunner->completeAndLock();
      $this->messenger()->addStatus($this->t('Setup completed and locked.'));
      $form_state->setRedirect('site_platform_admin.site_setup');
    }
    catch (\InvalidArgumentException $exception) {
      $this->messenger()->addError($exception->getMessage());
      $form_state->setRebuild(TRUE);
    }
  }

}
