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
      '#attached' => [
        'library' => [
          'site_platform_admin/site_setup',
        ],
      ],
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup',
        ],
      ],
      'hero' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-setup__hero',
          ],
        ],
        'content' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'site-setup__hero-content',
            ],
          ],
          'title' => [
            '#type' => 'html_tag',
            '#tag' => 'h2',
            '#value' => $this->t('Setup Control Center'),
          ],
          'description' => [
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => $this->t('Prepare required backend data and frontend-safe defaults for this decoupled site platform.'),
          ],
        ],
        'badge' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => [
              'site-setup__badge',
            ],
          ],
          '#value' => $this->getStatusBadgeText($status),
        ],
      ],
      'actions' => $this->buildActions(),
      'status_cards' => $this->buildStatusCards($status, $values),
      'progress' => $this->buildProgress($status['steps'] ?: []),
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
          '#value' => $this->t('Runtime setup values are stored per environment, so local, stage, and production setup values are not exported through shared config.'),
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
      $this->t('Prepare Run'),
      Url::fromRoute('site_platform_admin.site_setup_run')
    )->toRenderable();
    $run['#attributes']['class'][] = 'button';

    $complete = Link::fromTextAndUrl(
      $this->t('Complete and Lock'),
      Url::fromRoute('site_platform_admin.site_setup_complete')
    )->toRenderable();
    $complete['#attributes']['class'][] = 'button';

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup__actions',
        ],
      ],
      'wizard' => $wizard,
      'run' => $run,
      'complete' => $complete,
    ];
  }

  /**
   * Builds compact status cards.
   */
  private function buildStatusCards(array $status, array $values): array {
    $cards = [
      [
        'label' => $this->t('Mode'),
        'value' => $status['mode'] ?: 'single',
      ],
      [
        'label' => $this->t('Step'),
        'value' => $status['current_step'] ?: 'not_started',
      ],
      [
        'label' => $this->t('Site'),
        'value' => $values['site']['name'] ?: $this->t('Not set'),
      ],
      [
        'label' => $this->t('Setup ID'),
        'value' => $status['setup_id'] ?: $this->t('Pending'),
      ],
      [
        'label' => $this->t('Completed'),
        'value' => $this->formatBoolean((bool) $status['completed']),
      ],
      [
        'label' => $this->t('Locked'),
        'value' => $this->formatBoolean((bool) $status['locked']),
      ],
    ];

    $items = [];

    foreach ($cards as $card) {
      $items[] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-setup__status-card',
          ],
        ],
        'value' => [
          '#type' => 'html_tag',
          '#tag' => 'strong',
          '#value' => $card['value'],
        ],
        'label' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $card['label'],
        ],
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup__status-grid',
        ],
      ],
      'items' => $items,
    ];
  }

  /**
   * Builds compact setup progress pills.
   */
  private function buildProgress(array $steps): array {
    $items = [];

    foreach ($steps as $step => $status) {
      $items[] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-setup__step',
            'site-setup__step--' . str_replace('_', '-', (string) $status),
          ],
        ],
        'name' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => [
              'site-setup__step-name',
            ],
          ],
          '#value' => $this->formatStepName((string) $step),
        ],
        'status' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => [
              'site-setup__step-status',
            ],
          ],
          '#value' => (string) $status,
        ],
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup__progress-card',
        ],
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('Progress'),
      ],
      'items' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-setup__steps',
          ],
        ],
        'items' => $items,
      ],
    ];
  }

  /**
   * Gets status badge text.
   */
  private function getStatusBadgeText(array $status): string {
    if (!empty($status['locked'])) {
      return (string) $this->t('Locked');
    }

    if (!empty($status['completed'])) {
      return (string) $this->t('Completed');
    }

    return (string) $this->t('In Progress');
  }

  /**
   * Formats setup step machine name.
   */
  private function formatStepName(string $step): string {
    return ucwords(str_replace('_', ' ', $step));
  }

  /**
   * Formats boolean values for display.
   */
  private function formatBoolean(bool $value): string {
    return $value ? (string) $this->t('Yes') : (string) $this->t('No');
  }

}
