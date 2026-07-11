<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\State\StateInterface;
use Drupal\file\FileInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds setup runtime values to the Site API response.
 */
final class SiteSetupProfileSubscriber implements EventSubscriberInterface {

  /**
   * Constructs the site setup profile subscriber.
   */
  public function __construct(
    private readonly StateInterface $state,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  /**
   * Adds setup profile data to the Site API response.
   */
  public function onResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    if ($request->getPathInfo() !== '/api/v1/site') {
      return;
    }

    $response = $event->getResponse();
    if (!$response instanceof JsonResponse) {
      return;
    }

    $content = $response->getContent();
    if (!is_string($content) || $content === '') {
      return;
    }

    $data = json_decode($content, TRUE);
    if (!is_array($data)) {
      return;
    }

    $values = $this->state->get('site_platform_admin.setup_values', []);
    if (!is_array($values) || $values === []) {
      return;
    }

    $branding = is_array($values['branding'] ?? NULL) ? $values['branding'] : [];

    $data['setupProfile'] = [
      'mode' => (string) ($values['mode'] ?? ''),
      'environment' => (string) ($values['environment'] ?? ''),
      'siteKey' => (string) ($values['site']['key'] ?? ''),
      'primaryDomain' => (string) ($values['site']['primary_domain'] ?? ''),
      'frontendUrl' => (string) ($values['site']['frontend_url'] ?? ''),
      'adminUrl' => (string) ($values['site']['admin_url'] ?? ''),
      'apiUrl' => (string) ($values['site']['api_url'] ?? ''),
      'companyName' => (string) ($values['contact']['company_name'] ?? ''),
      'primaryEmail' => (string) ($values['contact']['primary_email'] ?? ''),
      'country' => (string) ($values['contact']['country'] ?? ''),
      'branding' => [
        'themeColor' => (string) ($branding['theme_color'] ?? ''),
        'siteTitleBar' => (string) ($branding['site_title_bar'] ?? ''),
        'headerLogo' => $this->buildFileInfo((int) ($branding['header_logo'] ?? 0)),
        'headerLogoAlt' => (string) ($branding['header_logo_alt'] ?? ''),
        'footerLogo' => $this->buildFileInfo((int) ($branding['footer_logo'] ?? 0)),
        'footerLogoAlt' => (string) ($branding['footer_logo_alt'] ?? ''),
        'favicon' => $this->buildFileInfo((int) ($branding['favicon_ico'] ?? 0)),
        'appIcon' => $this->buildFileInfo((int) ($branding['app_icon'] ?? 0)),
        'socialImage' => $this->buildFileInfo((int) ($branding['social_image'] ?? 0)),
        'footerCopyright' => (string) ($branding['footer_copyright'] ?? ''),
      ],
      'extraSites' => is_array($values['extra_sites'] ?? NULL) ? $values['extra_sites'] : [],
    ];

    $response->setData($data);
  }

  /**
   * Builds frontend-safe file info.
   */
  private function buildFileInfo(int $file_id): ?array {
    if ($file_id <= 0) {
      return NULL;
    }

    $file = $this->entityTypeManager->getStorage('file')->load($file_id);
    if (!$file instanceof FileInterface) {
      return NULL;
    }

    return [
      'id' => (int) $file->id(),
      'uuid' => $file->uuid(),
      'filename' => $file->getFilename(),
      'mime' => $file->getMimeType(),
      'url' => $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onResponse'],
    ];
  }

}
