<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\EventSubscriber;

use Drupal\Core\State\StateInterface;
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
        'themeColor' => (string) ($values['branding']['theme_color'] ?? ''),
        'logo' => (string) ($values['branding']['logo'] ?? ''),
        'favicon' => (string) ($values['branding']['favicon'] ?? ''),
      ],
      'extraSites' => is_array($values['extra_sites'] ?? NULL) ? $values['extra_sites'] : [],
    ];

    $response->setData($data);
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
