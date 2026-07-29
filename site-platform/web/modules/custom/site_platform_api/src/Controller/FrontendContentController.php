<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class FrontendContentController extends ControllerBase {

  private const STATE_KEY = 'site_platform.frontend_payload';

  public function bootstrap(): JsonResponse {
    $payload = $this->payload();

    return $this->json([
      'contractVersion' => $payload['contractVersion'] ?? '2.0',
      'mode' => 'single_backend',
      'template' => $payload['template'] ?? [],
      'site' => $payload['site'] ?? [],
      'navigation' => $payload['navigation'] ?? [],
      'footer' => $payload['footer'] ?? [],
      'analytics' => $payload['analytics'] ?? [],
      'media' => $payload['media'] ?? [],
      'pages' => array_values($payload['pages'] ?? []),
      'content' => $payload['content'] ?? [],
      'forms' => $this->publicForms($payload['forms'] ?? []),
    ]);
  }

  public function page(string $slug): JsonResponse {
    $payload = $this->payload();
    $slug = trim($slug, '/') ?: 'home';

    if (empty($payload['pages'][$slug])) {
      return $this->error('not_found', 'Page not found.', 404);
    }

    return $this->json([
      'contractVersion' => $payload['contractVersion'] ?? '2.0',
      'slug' => $slug,
      'page' => $payload['pages'][$slug],
    ]);
  }

  public function content(string $source): JsonResponse {
    $payload = $this->payload();
    $source = trim($source);

    if (!array_key_exists($source, $payload['content'] ?? [])) {
      return $this->error('invalid_source', 'Unsupported content source.', 400);
    }

    $items = $payload['content'][$source];

    return $this->json([
      'contractVersion' => $payload['contractVersion'] ?? '2.0',
      'source' => $source,
      'count' => is_array($items) ? count($items) : 0,
      'items' => array_values(is_array($items) ? $items : []),
    ]);
  }

  public function form(string $id): JsonResponse {
    $payload = $this->payload();
    $id = $this->normalizeId($id);

    if (empty($payload['forms'][$id])) {
      return $this->error('not_found', 'Form not found.', 404);
    }

    $form = $payload['forms'][$id];
    unset($form['webformElements'], $form['notification']);

    return $this->json([
      'contractVersion' => $payload['contractVersion'] ?? '2.0',
      'form' => $form,
    ]);
  }

  public function analytics(): JsonResponse {
    $payload = $this->payload();

    return $this->json([
      'contractVersion' => $payload['contractVersion'] ?? '2.0',
      'analytics' => $payload['analytics'] ?? [],
    ]);
  }

  public function submitForm(string $id, Request $request): JsonResponse {
    $payload = $this->payload();
    $id = $this->normalizeId($id);

    if (empty($payload['forms'][$id])) {
      return $this->error('not_found', 'Form not found.', 404);
    }

    $form = $payload['forms'][$id];
    $webform_id = $form['webformId'] ?? '';

    if (!$webform_id || !Webform::load($webform_id)) {
      return $this->error('webform_missing', 'Backing Webform is missing.', 500);
    }

    $data = json_decode($request->getContent(), TRUE);

    if (!is_array($data)) {
      $data = $request->request->all();
    }

    $errors = $this->validate($form, $data);

    if ($errors) {
      return $this->json([
        'status' => 'error',
        'error' => [
          'code' => 'validation_failed',
          'message' => 'Required fields are missing.',
          'fields' => $errors,
        ],
      ], 422);
    }

    $submission = WebformSubmission::create([
      'webform_id' => $webform_id,
      'data' => $this->filterSubmissionData($form, $data),
      'in_draft' => FALSE,
      'remote_addr' => $request->getClientIp(),
    ]);
    $submission->save();

    return $this->json([
      'status' => 'ok',
      'message' => $form['successMessage'] ?? 'Submission received.',
      'submissionId' => (int) $submission->id(),
      'webformId' => $webform_id,
    ], 201);
  }

  private function publicForms(array $forms): array {
    foreach ($forms as &$form) {
      unset($form['webformElements'], $form['notification']);
    }

    return $forms;
  }

  private function payload(): array {
    $payload = \Drupal::state()->get(self::STATE_KEY, []);
    return is_array($payload) ? $payload : [];
  }

  private function normalizeId(string $id): string {
    return str_replace('_', '-', trim($id));
  }

  private function validate(array $form, array $data): array {
    $errors = [];

    foreach (($form['fields'] ?? []) as $field) {
      if (empty($field['required'])) {
        continue;
      }

      $name = (string) ($field['name'] ?? '');

      if ($name === '') {
        continue;
      }

      if (!array_key_exists($name, $data) || trim((string) $data[$name]) === '') {
        $errors[$name] = sprintf('%s is required.', $field['label'] ?? $name);
      }
    }

    return $errors;
  }

  private function filterSubmissionData(array $form, array $data): array {
    $allowed = [];

    foreach (($form['fields'] ?? []) as $field) {
      if (!empty($field['name'])) {
        $allowed[] = $field['name'];
      }
    }

    return $allowed ? array_intersect_key($data, array_flip($allowed)) : $data;
  }

  private function error(string $code, string $message, int $status): JsonResponse {
    return $this->json([
      'status' => 'error',
      'error' => [
        'code' => $code,
        'message' => $message,
      ],
    ], $status);
  }

  private function json(array $data, int $status = 200): JsonResponse {
    $response = new JsonResponse($data, $status);
    $response->headers->set('Cache-Control', 'no-cache, private');
    return $response;
  }

}
