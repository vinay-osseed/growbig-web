<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides the first-run site setup overview.
 */
final class SiteSetupController extends ControllerBase {

  /**
   * Builds the setup overview page.
   */
  public function overview(): array {
    $status = $this->config('site_platform_admin.setup_status');

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
          '#value' => $this->t('Setup runner, retry, reset, and wizard forms will be added in the next implementation phases.'),
        ],
      ],
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
