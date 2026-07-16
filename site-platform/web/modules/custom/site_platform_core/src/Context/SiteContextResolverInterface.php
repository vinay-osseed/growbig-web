<?php

declare(strict_types=1);

namespace Drupal\site_platform_core\Context;

use Symfony\Component\HttpFoundation\Request;

/**
 * Resolves the active site context for the current request.
 */
interface SiteContextResolverInterface {

  /**
   * Resolves the active site context.
   */
  public function resolve(?Request $request = NULL): SiteContext;

  /**
   * Resolves the active site context from a host.
   */
  public function resolveFromHost(string $host): SiteContext;

}
