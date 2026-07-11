<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides analytics settings form.
 */
final class AnalyticsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'site_platform_api_analytics_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'site_platform_api.analytics',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('site_platform_api.analytics');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Analytics'),
      '#default_value' => (bool) $config->get('enabled'),
      '#description' => $this->t('Environment values can still override this setting.'),
    ];

    $form['provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Analytics Provider'),
      '#options' => [
        'google_analytics' => $this->t('Google Analytics 4'),
      ],
      '#default_value' => $config->get('provider') ?: 'google_analytics',
    ];

    $form['measurement_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Analytics Measurement ID'),
      '#default_value' => $config->get('measurement_id') ?: '',
      '#placeholder' => 'G-XXXXXXXXXX',
      '#description' => $this->t('Only the public Measurement ID is exposed to the frontend. Do not enter private credentials here.'),
      '#maxlength' => 64,
    ];

    $form['env_note'] = [
      '#type' => 'item',
      '#title' => $this->t('Environment Override'),
      '#markup' => '<p>' . $this->t('If GOOGLE_ANALYTICS_ENABLED or GOOGLE_ANALYTICS_MEASUREMENT_ID is set in the environment, the API uses those values first.') . '</p>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $measurement_id = trim((string) $form_state->getValue('measurement_id'));

    if ($measurement_id !== '' && !preg_match('/^G-[A-Z0-9]+$/', $measurement_id)) {
      $form_state->setErrorByName('measurement_id', $this->t('Enter a valid GA4 Measurement ID, for example G-XXXXXXXXXX.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('site_platform_api.analytics')
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('provider', (string) $form_state->getValue('provider'))
      ->set('measurement_id', trim((string) $form_state->getValue('measurement_id')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
