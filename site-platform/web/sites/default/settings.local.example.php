<?php

declare(strict_types=1);

/**
 * @file
 * Example local-only overrides.
 *
 * Copy to settings.local.php when needed.
 * Do not commit settings.local.php.
 */

$config['system.logging']['error_level'] = 'verbose';

$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';

$settings['extension_discovery_scan_tests'] = FALSE;

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

$settings['rebuild_access'] = TRUE;
$settings['skip_permissions_hardening'] = TRUE;
