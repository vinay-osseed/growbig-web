<?php

namespace Drupal\site_platform_native\EventSubscriber;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sends content editors to the Site Platform start workflow after login.
 */
final class EditorLoginRedirectSubscriber implements EventSubscriberInterface {

  public function __construct(
    private readonly AccountProxyInterface $currentUser,
    private readonly RequestStack $requestStack,
    private readonly CurrentPathStack $currentPath,
  ) {}

  /**
   * Redirects once after login.
   */
  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest() || $this->currentUser->isAnonymous()) {
      return;
    }

    $request = $event->getRequest();
    $session = $request->getSession();
    if (!$session || !$session->get('site_platform_native_redirect_after_login')) {
      return;
    }

    $path = $this->currentPath->getPath($request);
    if (str_starts_with($path, '/admin/site-platform/start') || str_starts_with($path, '/api/') || str_starts_with($path, '/core/')) {
      $session->remove('site_platform_native_redirect_after_login');
      return;
    }

    $session->remove('site_platform_native_redirect_after_login');
    $event->setResponse(new RedirectResponse('/admin/site-platform/start'));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [KernelEvents::REQUEST => ['onRequest', 20]];
  }

}
