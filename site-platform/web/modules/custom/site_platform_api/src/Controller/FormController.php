<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns Webform metadata and accepts Webform submissions.
 */
final class FormController extends ControllerBase {

  /**
   * Supported public form aliases.
   */
  private const FORM_MAP = [
    'contact-us' => 'contact_us',
    'job-application' => 'job_application',
  ];

  /**
   * Returns form metadata for frontend rendering.
   */
  public function form(string $form): JsonResponse {
    $webform = $this->loadMappedWebform($form);

    if (!$webform) {
      return $this->notFoundResponse();
    }

    return new JsonResponse([
      'id' => $form,
      'webformId' => $webform->id(),
      'title' => $webform->label(),
      'description' => $webform->get('description') ?? '',
      'fields' => $this->getPublicFields($webform->id()),
      'submit' => [
        'method' => 'POST',
        'apiPath' => '/api/v1/forms/' . $form . '/submit',
      ],
    ]);
  }

  /**
   * Submits a public Webform.
   */
  public function submit(string $form, Request $request): JsonResponse {
    $webform = $this->loadMappedWebform($form);

    if (!$webform) {
      return $this->notFoundResponse();
    }

    $payload = json_decode($request->getContent(), TRUE);

    if (!is_array($payload)) {
      return new JsonResponse([
        'error' => [
          'code' => 'invalid_json',
          'message' => 'Request body must be valid JSON.',
        ],
      ], 400);
    }

    $data = $payload['data'] ?? $payload;

    if (!is_array($data)) {
      return new JsonResponse([
        'error' => [
          'code' => 'invalid_payload',
          'message' => 'Request body must contain an object or a data object.',
        ],
      ], 400);
    }

    $errors = $this->validateRequiredFields($webform->id(), $data);

    if ($errors) {
      return new JsonResponse([
        'error' => [
          'code' => 'validation_failed',
          'message' => 'Required fields are missing.',
          'fields' => $errors,
        ],
      ], 422);
    }

    $submission = WebformSubmission::create([
      'webform_id' => $webform->id(),
      'data' => $this->filterSubmissionData($webform->id(), $data),
    ]);
    $submission->save();

    return new JsonResponse([
      'status' => 'success',
      'message' => 'Form submitted successfully.',
      'submissionId' => (int) $submission->id(),
    ], 201);
  }

  /**
   * Loads mapped Webform by public alias.
   */
  private function loadMappedWebform(string $form): ?Webform {
    $webform_id = self::FORM_MAP[$form] ?? NULL;

    if (!$webform_id) {
      return NULL;
    }

    $webform = Webform::load($webform_id);

    return $webform instanceof Webform ? $webform : NULL;
  }

  /**
   * Returns fields exposed to the decoupled frontend.
   */
  private function getPublicFields(string $webform_id): array {
    return match ($webform_id) {
      'contact_us' => [
        [
          'name' => 'name',
          'type' => 'textfield',
          'label' => 'Full Name',
          'required' => TRUE,
        ],
        [
          'name' => 'email',
          'type' => 'email',
          'label' => 'Email',
          'required' => TRUE,
        ],
        [
          'name' => 'phone',
          'type' => 'tel',
          'label' => 'Phone',
          'required' => FALSE,
        ],
        [
          'name' => 'company',
          'type' => 'textfield',
          'label' => 'Company',
          'required' => FALSE,
        ],
        [
          'name' => 'subject',
          'type' => 'textfield',
          'label' => 'Subject',
          'required' => TRUE,
        ],
        [
          'name' => 'service_interest',
          'type' => 'select',
          'label' => 'Service Interest',
          'required' => FALSE,
          'options' => [
            'website_development' => 'Website Development',
            'mobile_app_development' => 'Mobile App Development',
            'custom_software' => 'Custom Software',
            'cloud_solutions' => 'Cloud Solutions',
            'digital_marketing' => 'Digital Marketing',
            'branding' => 'Branding',
            'other' => 'Other',
          ],
        ],
        [
          'name' => 'preferred_contact_method',
          'type' => 'radios',
          'label' => 'Preferred Contact Method',
          'required' => FALSE,
          'options' => [
            'email' => 'Email',
            'phone' => 'Phone',
            'whatsapp' => 'WhatsApp',
          ],
          'defaultValue' => 'email',
        ],
        [
          'name' => 'budget_range',
          'type' => 'select',
          'label' => 'Budget Range',
          'required' => FALSE,
          'optional' => TRUE,
          'renderWhenNeeded' => TRUE,
          'options' => [
            'under_50000' => 'Under ₹50,000',
            '50000_100000' => '₹50,000 - ₹1,00,000',
            '100000_250000' => '₹1,00,000 - ₹2,50,000',
            '250000_500000' => '₹2,50,000 - ₹5,00,000',
            'above_500000' => 'Above ₹5,00,000',
          ],
        ],
        [
          'name' => 'message',
          'type' => 'textarea',
          'label' => 'Message',
          'required' => TRUE,
        ],
        [
          'name' => 'consent',
          'type' => 'checkbox',
          'label' => 'I consent to being contacted about this enquiry.',
          'required' => TRUE,
        ],
      ],
      'job_application' => [
        [
          'name' => 'job_key',
          'type' => 'hidden',
          'label' => 'Job Key',
          'required' => FALSE,
        ],
        [
          'name' => 'job_title',
          'type' => 'textfield',
          'label' => 'Job Title',
          'required' => TRUE,
        ],
        [
          'name' => 'name',
          'type' => 'textfield',
          'label' => 'Full Name',
          'required' => TRUE,
        ],
        [
          'name' => 'email',
          'type' => 'email',
          'label' => 'Email',
          'required' => TRUE,
        ],
        [
          'name' => 'phone',
          'type' => 'tel',
          'label' => 'Phone',
          'required' => TRUE,
        ],
        [
          'name' => 'current_location',
          'type' => 'textfield',
          'label' => 'Current Location',
          'required' => FALSE,
        ],
        [
          'name' => 'experience_years',
          'type' => 'number',
          'label' => 'Experience in Years',
          'required' => FALSE,
        ],
        [
          'name' => 'current_company',
          'type' => 'textfield',
          'label' => 'Current Company',
          'required' => FALSE,
        ],
        [
          'name' => 'resume_upload',
          'type' => 'managed_file',
          'label' => 'Resume',
          'required' => FALSE,
          'apiSupported' => FALSE,
          'note' => 'File upload support will be handled separately.',
        ],
        [
          'name' => 'portfolio_url',
          'type' => 'url',
          'label' => 'Portfolio URL',
          'required' => FALSE,
        ],
        [
          'name' => 'linkedin_url',
          'type' => 'url',
          'label' => 'LinkedIn URL',
          'required' => FALSE,
        ],
        [
          'name' => 'message',
          'type' => 'textarea',
          'label' => 'Message',
          'required' => FALSE,
        ],
        [
          'name' => 'consent',
          'type' => 'checkbox',
          'label' => 'I consent to being contacted about this job application.',
          'required' => TRUE,
        ],
      ],
      default => [],
    };
  }

  /**
   * Validates required fields.
   */
  private function validateRequiredFields(string $webform_id, array $data): array {
    $errors = [];

    foreach ($this->getPublicFields($webform_id) as $field) {
      if (empty($field['required'])) {
        continue;
      }

      $name = $field['name'];
      $value = $data[$name] ?? NULL;

      if ($value === NULL || $value === '' || $value === FALSE) {
        $errors[$name] = 'This field is required.';
      }
    }

    return $errors;
  }

  /**
   * Filters data to known public fields.
   */
  private function filterSubmissionData(string $webform_id, array $data): array {
    $allowed = array_column($this->getPublicFields($webform_id), 'name');
    $filtered = [];

    foreach ($allowed as $field_name) {
      if (array_key_exists($field_name, $data)) {
        $filtered[$field_name] = $data[$field_name];
      }
    }

    return $filtered;
  }

  /**
   * Returns not found response.
   */
  private function notFoundResponse(): JsonResponse {
    return new JsonResponse([
      'error' => [
        'code' => 'not_found',
        'message' => 'Form not found.',
      ],
    ], 404);
  }

}
