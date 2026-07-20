<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\site_platform_core\Context\SiteContext;
use Drupal\site_platform_core\Context\SiteContextResolverInterface;
use Drupal\webform\Entity\Webform;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form API controller.
 *
 * Webform is the primary storage for real forms and submissions. The older
 * Site Form node model remains as a legacy fallback/wrapper so existing demo
 * data and API contracts do not break during the transition.
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

    $webform = $this->loadWebformByKey((string) $context->getSiteKey(), $form_key);
    if ($webform instanceof Webform) {
      return $this->successResponse($this->normalizeWebform($webform, $form_key), $context, [
        'config:webform.webform.' . $webform->id(),
        'node:' . $context->getSiteProfileId(),
      ]);
    }

    $form_node = $this->loadLegacyFormByKey((int) $context->getSiteProfileId(), $form_key);

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

    return $this->successResponse($this->normalizeLegacyForm($form_node, TRUE), $context, [
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

    $payload = json_decode((string) $request->getContent(), TRUE);
    if (!is_array($payload)) {
      return $this->errorResponse(
        'invalid_json',
        'Request body must be valid JSON.',
        ['form' => $form_key],
        400,
      );
    }

    $webform = $this->loadWebformByKey((string) $context->getSiteKey(), $form_key);
    if ($webform instanceof Webform) {
      $errors = $this->validateWebformSubmission($webform, $payload);
      if ($errors !== []) {
        return $this->errorResponse(
          'validation_failed',
          'Submission validation failed.',
          ['form' => $form_key, 'errors' => $errors],
          422,
        );
      }

      $submission = $this->saveWebformSubmission($webform, $payload, $request);

      return new JsonResponse([
        'data' => [
          'submissionId' => 'webform_submission-' . $submission->id(),
          'form' => $form_key,
          'webformId' => (string) $webform->id(),
          'status' => 'received',
          'message' => $this->webformSuccessMessage($webform),
        ],
        'meta' => [
          'siteKey' => $context->getSiteKey(),
          'language' => $context->getLanguageId(),
          'resolvedBy' => $context->getResolvedBy(),
          'generatedAt' => gmdate('c'),
          'storage' => 'webform',
        ],
        'cache' => [
          'maxAge' => 0,
          'tags' => ['config:webform.webform.' . $webform->id()],
          'contexts' => ['url.site', 'headers:host'],
        ],
      ], 201);
    }

    $form_node = $this->loadLegacyFormByKey((int) $context->getSiteProfileId(), $form_key);

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

    $errors = $this->validateLegacySubmission($form_node, $payload);
    if ($errors !== []) {
      return $this->errorResponse(
        'validation_failed',
        'Submission validation failed.',
        ['form' => $form_key, 'errors' => $errors],
        422,
      );
    }

    $submission = $this->saveLegacySubmission((int) $context->getSiteProfileId(), $form_node, $payload);

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
        'storage' => 'legacy_node',
      ],
      'cache' => [
        'maxAge' => 0,
        'tags' => ['node:' . $form_node->id()],
        'contexts' => ['url.site', 'headers:host'],
      ],
    ], 201);
  }

  /**
   * Loads a Webform by site key and public form key.
   */
  private function loadWebformByKey(string $siteKey, string $formKey): ?Webform {
    if (!class_exists(Webform::class)) {
      return NULL;
    }

    $candidates = array_values(array_unique([
      $this->webformIdFromSiteAndKey($siteKey, $formKey),
      $this->machineName($siteKey . '_' . $formKey),
      $this->machineName($formKey),
    ]));

    foreach ($candidates as $id) {
      $webform = Webform::load($id);
      if ($webform instanceof Webform && $webform->status()) {
        return $webform;
      }
    }

    return NULL;
  }

  /**
   * Loads one legacy Site Form node by key.
   */
  private function loadLegacyFormByKey(int $siteProfileId, string $formKey): ?NodeInterface {
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
   * Loads legacy fields for a form.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Form field nodes.
   */
  private function loadLegacyFields(NodeInterface $form): array {
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
   * Normalizes a Webform.
   *
   * @return array<string, mixed>
   *   Normalized form data.
   */
  private function normalizeWebform(Webform $webform, string $formKey): array {
    return [
      'id' => 'webform-' . $webform->id(),
      'type' => 'Webform',
      'storage' => 'webform',
      'key' => $formKey,
      'webformId' => (string) $webform->id(),
      'label' => $webform->label(),
      'description' => (string) ($webform->get('description') ?? ''),
      'successMessage' => $this->webformSuccessMessage($webform),
      'fields' => $this->normalizeWebformFields($webform),
    ];
  }

  /**
   * Normalizes Webform elements into the stable public API field shape.
   *
   * @return array<int, array<string, mixed>>
   *   Normalized fields.
   */
  private function normalizeWebformFields(Webform $webform): array {
    $fields = [];
    $elements = $this->webformElements($webform);

    foreach ($elements as $key => $element) {
      if (!is_string($key) || !is_array($element)) {
        continue;
      }

      $type = (string) ($element['#type'] ?? 'textfield');
      if (in_array($type, ['actions', 'processed_text', 'markup', 'hidden', 'webform_actions'], TRUE)) {
        continue;
      }

      $fields[] = [
        'key' => $key,
        'type' => $this->publicFieldType($type),
        'webformType' => $type,
        'label' => (string) ($element['#title'] ?? $key),
        'required' => (bool) ($element['#required'] ?? FALSE),
        'placeholder' => (string) ($element['#placeholder'] ?? ''),
        'help' => (string) ($element['#description'] ?? ''),
        'options' => $this->normalizeOptions($element['#options'] ?? []),
        'weight' => (int) ($element['#weight'] ?? 0),
      ];
    }

    usort($fields, static fn(array $a, array $b): int => ($a['weight'] <=> $b['weight']) ?: strcmp((string) $a['key'], (string) $b['key']));

    return $fields;
  }

  /**
   * Normalizes a legacy form.
   *
   * @return array<string, mixed>
   *   Normalized form data.
   */
  private function normalizeLegacyForm(NodeInterface $form, bool $includeFields): array {
    $data = [
      'id' => 'form-' . $form->id(),
      'type' => 'SiteForm',
      'storage' => 'legacy_node',
      'key' => $this->fieldValue($form, 'field_form_key'),
      'label' => $this->fieldValue($form, 'field_form_label') ?: $form->label(),
      'description' => $this->fieldValue($form, 'field_form_description'),
      'successMessage' => $this->fieldValue($form, 'field_form_success_message') ?: 'Thank you.',
    ];

    if ($includeFields) {
      $data['fields'] = array_map(function (NodeInterface $field): array {
        return $this->normalizeLegacyFormField($field);
      }, $this->loadLegacyFields($form));
    }

    return $data;
  }

  /**
   * Normalizes one legacy form field.
   *
   * @return array<string, mixed>
   *   Normalized field data.
   */
  private function normalizeLegacyFormField(NodeInterface $field): array {
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
   * Validates a Webform submission payload for API use.
   *
   * @return array<string, string>
   *   Validation errors keyed by field key.
   */
  private function validateWebformSubmission(Webform $webform, array $payload): array {
    $errors = [];

    foreach ($this->normalizeWebformFields($webform) as $field) {
      $key = (string) $field['key'];
      $type = (string) $field['type'];
      $required = (bool) $field['required'];
      $value = $payload[$key] ?? '';

      if ($required && $this->isEmptyValue($value)) {
        $errors[$key] = 'This field is required.';
        continue;
      }

      if ($type === 'email' && !$this->isEmptyValue($value) && !filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
        $errors[$key] = 'Enter a valid email address.';
      }
    }

    return $errors;
  }

  /**
   * Validates a legacy submission payload.
   *
   * @return array<string, string>
   *   Validation errors keyed by field key.
   */
  private function validateLegacySubmission(NodeInterface $form, array $payload): array {
    $errors = [];

    foreach ($this->loadLegacyFields($form) as $field) {
      $key = (string) $this->fieldValue($field, 'field_form_field_key');
      $type = (string) ($this->fieldValue($field, 'field_form_field_type') ?: 'text');
      $required = (bool) $this->fieldValue($field, 'field_form_field_required');
      $value = $payload[$key] ?? '';

      if ($required && $this->isEmptyValue($value)) {
        $errors[$key] = 'This field is required.';
        continue;
      }

      if ($type === 'email' && !$this->isEmptyValue($value) && !filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
        $errors[$key] = 'Enter a valid email address.';
      }
    }

    return $errors;
  }

  /**
   * Saves a Webform submission.
   */
  private function saveWebformSubmission(Webform $webform, array $payload, Request $request): object {
    $submission = $this->sitePlatformEntityTypeManager->getStorage('webform_submission')->create([
      'webform_id' => $webform->id(),
      'remote_addr' => $request->getClientIp() ?? '',
      'data' => $payload,
    ]);
    $submission->save();

    return $submission;
  }

  /**
   * Saves a legacy node submission.
   */
  private function saveLegacySubmission(int $siteProfileId, NodeInterface $form, array $payload): NodeInterface {
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
   * Gets flattened Webform elements safely.
   *
   * @return array<string, mixed>
   *   Elements.
   */
  private function webformElements(Webform $webform): array {
    if (method_exists($webform, 'getElementsDecodedAndFlattened')) {
      $elements = $webform->getElementsDecodedAndFlattened();
      return is_array($elements) ? $elements : [];
    }

    if (method_exists($webform, 'getElementsDecoded')) {
      $elements = $webform->getElementsDecoded();
      return is_array($elements) ? $elements : [];
    }

    return [];
  }

  /**
   * Gets a Webform confirmation/success message.
   */
  private function webformSuccessMessage(Webform $webform): string {
    $settings = $webform->get('settings') ?? [];
    if (is_array($settings) && !empty($settings['confirmation_message'])) {
      return trim(strip_tags((string) $settings['confirmation_message'])) ?: 'Thank you.';
    }

    return 'Thank you.';
  }

  /**
   * Normalizes option values.
   *
   * @return array<string, string>|array<int, string>
   *   Normalized options.
   */
  private function normalizeOptions(mixed $options): array {
    if (!is_array($options)) {
      return [];
    }

    $normalized = [];
    foreach ($options as $key => $value) {
      if (is_array($value)) {
        continue;
      }
      $normalized[(string) $key] = (string) $value;
    }

    return $normalized;
  }

  /**
   * Maps Webform element types to stable public API types.
   */
  private function publicFieldType(string $type): string {
    return match ($type) {
      'textfield' => 'text',
      'webform_email_confirm' => 'email',
      'webform_tel' => 'tel',
      'webform_url' => 'url',
      'webform_select_other' => 'select',
      'webform_radios_other' => 'radios',
      'webform_checkboxes_other' => 'checkboxes',
      default => $type,
    };
  }

  /**
   * Checks if a submitted value is empty.
   */
  private function isEmptyValue(mixed $value): bool {
    if (is_array($value)) {
      return $value === [];
    }

    return trim((string) $value) === '';
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
   * Builds the default Webform ID for one site-scoped form key.
   */
  private function webformIdFromSiteAndKey(string $siteKey, string $formKey): string {
    return $this->machineName($siteKey . '_' . $formKey);
  }

  /**
   * Normalizes a URL/public key.
   */
  private function normalizeKey(string $key): string {
    $key = strtolower(trim($key));
    $key = preg_replace('/[^a-z0-9_-]+/', '-', $key) ?: $key;

    return trim($key, '-');
  }

  /**
   * Normalizes a machine name for config entity IDs.
   */
  private function machineName(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9_]+/', '_', str_replace('-', '_', $value)) ?: $value;
    $value = trim($value, '_');

    return $value !== '' ? $value : 'form';
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
