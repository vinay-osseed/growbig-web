<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\site_platform_core\Context\SiteContext;
use Drupal\site_platform_core\Context\SiteContextResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form API controller.
 */
final class FormApiController extends ControllerBase {

  /**
   * Constructs the controller.
   */
  public function __construct(
    private readonly SiteContextResolverInterface $siteContextResolver,
    private readonly EntityTypeManagerInterface $sitePlatformEntityTypeManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_core.site_context_resolver'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Returns a form schema by key.
   */
  public function form(Request $request, string $form): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $form_key = $this->normalizeKey($form);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'form' => $form_key],
        404,
      );
    }

    $form_node = $this->loadFormByKey((int) $context->getSiteProfileId(), $form_key);

    if (!$form_node instanceof NodeInterface) {
      return $this->errorResponse(
        'form_not_found',
        'Form not found.',
        [
          'siteKey' => $context->getSiteKey(),
          'form' => $form_key,
          'language' => $context->getLanguageId(),
        ],
        404,
      );
    }

    return $this->successResponse($this->normalizeForm($form_node, TRUE), $context, [
      'node:' . $form_node->id(),
      'node:' . $context->getSiteProfileId(),
      'node_list:site_form_field',
    ]);
  }

  /**
   * Stores one form submission.
   */
  public function submit(Request $request, string $form): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $form_key = $this->normalizeKey($form);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'form' => $form_key],
        404,
      );
    }

    $form_node = $this->loadFormByKey((int) $context->getSiteProfileId(), $form_key);

    if (!$form_node instanceof NodeInterface) {
      return $this->errorResponse(
        'form_not_found',
        'Form not found.',
        [
          'siteKey' => $context->getSiteKey(),
          'form' => $form_key,
          'language' => $context->getLanguageId(),
        ],
        404,
      );
    }

    $payload = json_decode((string) $request->getContent(), TRUE);
    if (!is_array($payload)) {
      return $this->errorResponse(
        'invalid_json',
        'Request body must be valid JSON.',
        ['form' => $form_key],
        400,
      );
    }

    $errors = $this->validateSubmission($form_node, $payload);
    if ($errors !== []) {
      return $this->errorResponse(
        'validation_failed',
        'Submission validation failed.',
        ['form' => $form_key, 'errors' => $errors],
        422,
      );
    }

    $submission = $this->saveSubmission((int) $context->getSiteProfileId(), $form_node, $payload);

    return new JsonResponse([
      'data' => [
        'submissionId' => 'submission-' . $submission->id(),
        'form' => $form_key,
        'status' => $this->fieldValue($submission, 'field_submission_status') ?: 'received',
        'message' => $this->fieldValue($form_node, 'field_form_success_message') ?: 'Thank you.',
      ],
      'meta' => [
        'siteKey' => $context->getSiteKey(),
        'language' => $context->getLanguageId(),
        'resolvedBy' => $context->getResolvedBy(),
        'generatedAt' => gmdate('c'),
      ],
      'cache' => [
        'maxAge' => 0,
        'tags' => ['node:' . $form_node->id()],
        'contexts' => ['url.site', 'headers:host'],
      ],
    ], 201);
  }

  /**
   * Loads one form by key.
   */
  private function loadFormByKey(int $siteProfileId, string $formKey): ?NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_form')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->condition('field_form_key', $formKey)
      ->range(0, 1);

    if ($this->fieldExists('node.site_form.field_form_is_active')) {
      $query->condition('field_form_is_active', 1);
    }

    $ids = $query->execute();
    if (!$ids) {
      return NULL;
    }

    $form = $storage->load(reset($ids));

    return $form instanceof NodeInterface ? $form : NULL;
  }

  /**
   * Loads fields for a form.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Form field nodes.
   */
  private function loadFields(NodeInterface $form): array {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_form_field')
      ->condition('status', 1)
      ->condition('field_site_form.target_id', (int) $form->id())
      ->sort('field_form_field_weight.value', 'ASC')
      ->sort('title', 'ASC')
      ->execute();

    if (!$ids) {
      return [];
    }

    return array_values(array_filter(
      $storage->loadMultiple($ids),
      static fn($entity): bool => $entity instanceof NodeInterface,
    ));
  }

  /**
   * Normalizes a form.
   *
   * @return array<string, mixed>
   *   Normalized form data.
   */
  private function normalizeForm(NodeInterface $form, bool $includeFields): array {
    $data = [
      'id' => 'form-' . $form->id(),
      'type' => 'SiteForm',
      'key' => $this->fieldValue($form, 'field_form_key'),
      'label' => $this->fieldValue($form, 'field_form_label') ?: $form->label(),
      'description' => $this->fieldValue($form, 'field_form_description'),
      'successMessage' => $this->fieldValue($form, 'field_form_success_message') ?: 'Thank you.',
    ];

    if ($includeFields) {
      $data['fields'] = array_map(function (NodeInterface $field): array {
        return $this->normalizeFormField($field);
      }, $this->loadFields($form));
    }

    return $data;
  }

  /**
   * Normalizes one form field.
   *
   * @return array<string, mixed>
   *   Normalized field data.
   */
  private function normalizeFormField(NodeInterface $field): array {
    return [
      'key' => $this->fieldValue($field, 'field_form_field_key'),
      'type' => $this->fieldValue($field, 'field_form_field_type') ?: 'text',
      'label' => $this->fieldValue($field, 'field_form_field_label') ?: $field->label(),
      'required' => (bool) $this->fieldValue($field, 'field_form_field_required'),
      'placeholder' => $this->fieldValue($field, 'field_form_field_placeholder'),
      'help' => $this->fieldValue($field, 'field_form_field_help'),
      'options' => $this->fieldValue($field, 'field_form_field_options'),
      'weight' => (int) ($this->fieldValue($field, 'field_form_field_weight') ?: 0),
    ];
  }

  /**
   * Validates a submission payload.
   *
   * @return array<string, string>
   *   Validation errors keyed by field key.
   */
  private function validateSubmission(NodeInterface $form, array $payload): array {
    $errors = [];

    foreach ($this->loadFields($form) as $field) {
      $key = (string) $this->fieldValue($field, 'field_form_field_key');
      $type = (string) ($this->fieldValue($field, 'field_form_field_type') ?: 'text');
      $required = (bool) $this->fieldValue($field, 'field_form_field_required');
      $value = $payload[$key] ?? '';

      if ($required && trim((string) $value) === '') {
        $errors[$key] = 'This field is required.';
        continue;
      }

      if ($type === 'email' && trim((string) $value) !== '' && !filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
        $errors[$key] = 'Enter a valid email address.';
      }
    }

    return $errors;
  }

  /**
   * Saves a submission.
   */
  private function saveSubmission(int $siteProfileId, NodeInterface $form, array $payload): NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $submission = $storage->create([
      'type' => 'site_form_submission',
      'title' => $form->label() . ' submission ' . gmdate('Y-m-d H:i:s'),
      'status' => 1,
      'uid' => 0,
    ]);

    $submission->set('field_site_profile', ['target_id' => $siteProfileId]);
    $submission->set('field_site_form', ['target_id' => $form->id()]);
    $submission->set('field_submission_payload', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $submission->set('field_submission_status', 'received');
    $submission->save();

    return $submission;
  }

  /**
   * Gets a field value safely.
   */
  private function fieldValue(NodeInterface $node, string $fieldName): mixed {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return '';
    }

    return $node->get($fieldName)->value ?? '';
  }

  /**
   * Returns TRUE when a field config exists.
   */
  private function fieldExists(string $fieldId): bool {
    try {
      return (bool) $this->sitePlatformEntityTypeManager
        ->getStorage('field_config')
        ->load($fieldId);
    }
    catch (\Throwable) {
      return FALSE;
    }
  }

  /**
   * Normalizes a machine key.
   */
  private function normalizeKey(string $key): string {
    $key = strtolower(trim($key));
    $key = preg_replace('/[^a-z0-9_-]+/', '-', $key) ?: $key;

    return trim($key, '-');
  }

  /**
   * Builds a success response.
   *
   * @param mixed $data
   *   Response data.
   * @param array<int, string> $cacheTags
   *   Cache tags.
   */
  private function successResponse(mixed $data, SiteContext $context, array $cacheTags): JsonResponse {
    return new JsonResponse([
      'data' => $data,
      'meta' => [
        'siteKey' => $context->getSiteKey(),
        'language' => $context->getLanguageId(),
        'resolvedBy' => $context->getResolvedBy(),
        'generatedAt' => gmdate('c'),
      ],
      'cache' => [
        'maxAge' => 300,
        'tags' => $cacheTags,
        'contexts' => ['url.site', 'headers:host'],
      ],
    ]);
  }

  /**
   * Builds an error response.
   *
   * @param array<string, mixed> $details
   *   Error details.
   */
  private function errorResponse(string $code, string $message, array $details, int $status): JsonResponse {
    return new JsonResponse([
      'error' => [
        'code' => $code,
        'message' => $message,
        'details' => $details,
      ],
    ], $status);
  }

}
