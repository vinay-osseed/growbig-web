<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\site_platform_admin\Service\SiteSetupStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the first-run site setup overview.
 */
final class SiteSetupController extends ControllerBase {

  /**
   * Constructs the site setup controller.
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
   * Builds the setup overview page.
   */
  public function overview(): array {
    $status = $this->setupStorage->getStatus();
    $values = $this->setupStorage->getValues();

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup',
        ],
      ],
      'intro' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-setup__intro',
          ],
        ],
        'title' => [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#value' => $this->t('Site Setup'),
        ],
        'description' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('This setup workflow will initialize required backend and frontend-safe defaults for this decoupled site platform.'),
        ],
      ],
      'actions' => $this->buildActions(),
      'summary' => [
        '#theme' => 'item_list',
        '#title' => $this->t('Current Setup Status'),
        '#items' => [
          $this->t('Mode: @value', [
            '@value' => $status['mode'] ?: 'single',
          ]),
          $this->t('Current step: @value', [
            '@value' => $status['current_step'] ?: 'not_started',
          ]),
          $this->t('Completed: @value', [
            '@value' => $this->formatBoolean((bool) $status['completed']),
          ]),
          $this->t('Locked: @value', [
            '@value' => $this->formatBoolean((bool) $status['locked']),
          ]),
          $this->t('Saved site name: @value', [
            '@value' => $values['site']['name'] ?: $this->t('Not set'),
          ]),
          $this->t('Setup ID: @value', [
            '@value' => $status['setup_id'] ?: $this->t('Not created yet'),
          ]),
        ],
      ],
      'steps' => $this->buildSteps($status['steps'] ?: []),
      'notice' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-setup__notice',
          ],
        ],
        'message' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('Setup values, status, and manifest are stored in Drupal state so local/stage/prod setup values do not accidentally export into shared config.'),
        ],
      ],
    ];
  }

  /**
   * Builds setup action links.
   */
  private function buildActions(): array {
    $wizard = Link::fromTextAndUrl(
      $this->t('Open Setup Wizard'),
      Url::fromRoute('site_platform_admin.site_setup_wizard')
    )->toRenderable();

    $wizard['#attributes']['class'][] = 'button';
    $wizard['#attributes']['class'][] = 'button--primary';

    $run = Link::fromTextAndUrl(
      $this->t('Prepare Setup Run'),
      Url::fromRoute('site_platform_admin.site_setup_run')
    )->toRenderable();

    $run['#attributes']['class'][] = 'button';

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup__actions',
        ],
      ],
      'wizard' => $wizard,
      'run' => $run,
    ];
  }

  /**
   * Builds setup step list.
   */
  private function buildSteps(array $steps): array {
    $items = [];

    foreach ($steps as $step => $status) {
      $items[] = $this->t('@step: @status', [
        '@step' => str_replace('_', ' ', (string) $step),
        '@status' => (string) $status,
      ]);
    }

    return [
      '#theme' => 'item_list',
      '#title' => $this->t('Setup Steps'),
      '#items' => $items,
      '#empty' => $this->t('No setup steps are configured yet.'),
    ];
  }

  /**
   * Formats boolean values for display.
   */
  private function formatBoolean(bool $value): string {
    return $value ? (string) $this->t('Yes') : (string) $this->t('No');
  }

}
