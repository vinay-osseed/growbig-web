<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides the first-run site setup overview.
 */
final class SiteSetupController extends ControllerBase {

  /**
   * Builds the setup overview page.
   */
  public function overview(): array {
    $status = $this->config('site_platform_admin.setup_status');
    $values = $this->config('site_platform_admin.setup_values');

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
            '@value' => $status->get('mode') ?: 'single',
          ]),
          $this->t('Current step: @value', [
            '@value' => $status->get('current_step') ?: 'not_started',
          ]),
          $this->t('Completed: @value', [
            '@value' => $this->formatBoolean((bool) $status->get('completed')),
          ]),
          $this->t('Locked: @value', [
            '@value' => $this->formatBoolean((bool) $status->get('locked')),
          ]),
          $this->t('Saved site name: @value', [
            '@value' => $values->get('site.name') ?: $this->t('Not set'),
          ]),
        ],
      ],
      'steps' => $this->buildSteps($status->get('steps') ?: []),
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
          '#value' => $this->t('Setup runner, retry, reset, and review forms will be added in the next implementation phases.'),
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

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-setup__actions',
        ],
      ],
      'wizard' => $wizard,
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
