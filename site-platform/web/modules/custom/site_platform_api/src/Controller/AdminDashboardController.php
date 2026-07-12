<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\SiteResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides role-aware admin dashboard data.
 */
final class AdminDashboardController extends ControllerBase {

  /**
   * Constructs the admin dashboard controller.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $apiEntityTypeManager,
    private readonly ModuleHandlerInterface $apiModuleHandler,
    private readonly SiteResolver $siteResolver,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('site_platform_api.site_resolver'),
    );
  }

  /**
   * Returns dashboard cards and counts for the current user.
   */
  public function dashboard(): JsonResponse {
    $account = $this->currentUser();
    $roles = $account->getRoles();

    return new JsonResponse([
      'id' => 'admin_dashboard',
      'title' => 'Admin Dashboard',
      'roles' => array_values($roles),
      'cards' => $this->buildCards($roles),
      'counts' => $this->buildCounts(),
      'recent' => [
        'content' => $this->getRecentContent(),
      ],
      'filters' => [
        'contentTypes' => [
          'site_page' => 'Pages',
          'service' => 'Services',
          'partner' => 'Partners',
          'team_member' => 'Team Members',
          'job' => 'Jobs',
        ],
      ],
    ]);
  }

  /**
   * Builds role-aware dashboard cards.
   */
  private function buildCards(array $roles): array {
    $cards = [];

    if ($this->hasAnyRole($roles, ['administrator'])) {
      $cards[] = $this->card('site_config', 'Site Setup', 'Manage site profile, domains, branding, setup, and platform defaults.', '/admin/site-setup');
      $cards[] = $this->card('api_status', 'API Status', 'Review the active Site API response.', '/api/v1/site');
    }

    if ($this->hasAnyRole($roles, ['administrator', 'content_editor'])) {
      $cards[] = $this->card('pages', 'Pages', 'Manage frontend pages and page sections.', '/admin/content?type=site_page');
      $cards[] = $this->card('menus', 'Header & Footer Menus', 'Manage pages used in frontend header and footer menus.', '/admin/content?type=site_page');
      $cards[] = $this->card('reusable_content', 'Reusable Content', 'Manage services, partners, and team members.', '/admin/content?type=service');
      $cards[] = $this->card('media', 'Media Library', 'Manage images and files.', '/admin/content/media');
    }

    if ($this->hasAnyRole($roles, ['administrator', 'content_editor', 'hr_manager'])) {
      $cards[] = $this->card('jobs', 'Jobs', 'Manage career openings.', '/admin/content?type=job');
      $cards[] = $this->card('job_applications', 'Job Applications', 'Review candidate applications.', '/admin/structure/webform/manage/job_application/results/submissions');
    }

    if ($this->hasAnyRole($roles, ['administrator', 'content_editor'])) {
      $cards[] = $this->card('contact_enquiries', 'Contact Enquiries', 'Review contact form submissions.', '/admin/structure/webform/manage/contact_us/results/submissions');
    }

    if ($this->hasAnyRole($roles, ['administrator'])) {
      $cards[] = $this->card('analytics', 'Analytics Settings', 'Manage Google Analytics Measurement ID used by the frontend.', '/admin/config/site-platform/analytics');
    }

    return $cards;
  }

  /**
   * Builds a dashboard card.
   */
  private function card(string $id, string $title, string $description, string $url): array {
    return [
      'id' => $id,
      'title' => $title,
      'description' => $description,
      'url' => $url,
    ];
  }

  /**
   * Checks if current role list has any target role.
   */
  private function hasAnyRole(array $roles, array $allowed): bool {
    return (bool) array_intersect($roles, $allowed);
  }

  /**
   * Builds content counts.
   */
  private function buildCounts(): array {
    return [
      'pages' => $this->countNodes('site_page'),
      'services' => $this->countNodes('service'),
      'partners' => $this->countNodes('partner'),
      'teamMembers' => $this->countNodes('team_member'),
      'jobs' => $this->countNodes('job'),
      'contactSubmissions' => $this->countWebformSubmissions('contact_us'),
      'jobApplications' => $this->countWebformSubmissions('job_application'),
    ];
  }

  /**
   * Counts nodes by bundle, scoped to the active site when possible.
   */
  private function countNodes(string $bundle): int {
    $query = $this->apiEntityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle);

    $this->siteResolver->applyCurrentSiteFilter($query, $bundle);

    return (int) $query->count()->execute();
  }

  /**
   * Counts Webform submissions by Webform ID.
   */
  private function countWebformSubmissions(string $webform_id): int {
    if (!$this->apiModuleHandler->moduleExists('webform')) {
      return 0;
    }

    return (int) $this->apiEntityTypeManager
      ->getStorage('webform_submission')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('webform_id', $webform_id)
      ->count()
      ->execute();
  }

  /**
   * Returns recent content updates scoped to the current site.
   */
  private function getRecentContent(): array {
    $storage = $this->apiEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', ['site_page', 'service', 'partner', 'team_member', 'job'], 'IN')
      ->sort('changed', 'DESC')
      ->range(0, 20);

    $ids = $query->execute();
    $items = [];

    foreach ($storage->loadMultiple($ids) as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }

      if (!$this->siteResolver->nodeBelongsToCurrentSite($node)) {
        continue;
      }

      $changed = (int) $node->getChangedTime();
      $owner = $node->getOwner();

      $items[] = [
        'id' => (int) $node->id(),
        'type' => $node->bundle(),
        'typeLabel' => $this->getBundleLabel($node->bundle()),
        'title' => $node->label(),
        'changed' => $changed,
        'changedFormatted' => date('Y-m-d H:i', $changed),
        'updatedBy' => $owner ? $owner->getDisplayName() : 'Unknown',
        'editUrl' => '/node/' . $node->id() . '/edit',
        'viewUrl' => '/node/' . $node->id(),
      ];

      if (count($items) >= 10) {
        break;
      }
    }

    return $items;
  }

  /**
   * Gets a human label for a content bundle.
   */
  private function getBundleLabel(string $bundle): string {
    return match ($bundle) {
      'site_page' => 'Page',
      'service' => 'Service',
      'partner' => 'Partner',
      'team_member' => 'Team Member',
      'job' => 'Job',
      default => ucfirst(str_replace('_', ' ', $bundle)),
    };
  }

}
