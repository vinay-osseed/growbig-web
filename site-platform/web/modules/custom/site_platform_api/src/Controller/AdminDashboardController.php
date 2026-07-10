<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides role-aware admin dashboard data.
 */
final class AdminDashboardController extends ControllerBase {

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
    ]);
  }

  /**
   * Builds role-aware dashboard cards.
   */
  private function buildCards(array $roles): array {
    $cards = [];

    if ($this->hasAnyRole($roles, ['administrator', 'site_developer'])) {
      $cards[] = $this->card('site_config', 'Site Configuration', 'Manage platform and site settings.', '/admin/config');
      $cards[] = $this->card('api_status', 'API Status', 'Review backend API availability.', '/api/v1/site');
      $cards[] = $this->card('roles', 'Roles & Permissions', 'Manage users, roles, and permissions.', '/admin/people/roles');
    }

    if ($this->hasAnyRole($roles, ['administrator', 'site_developer', 'content_admin'])) {
      $cards[] = $this->card('pages', 'Pages', 'Manage dynamic frontend pages.', '/admin/content');
      $cards[] = $this->card('menus', 'Header & Footer Menus', 'Control page visibility in frontend menus.', '/admin/content');
      $cards[] = $this->card('reusable_content', 'Reusable Content', 'Manage services, partners, team, and jobs.', '/admin/content');
      $cards[] = $this->card('media', 'Media Library', 'Manage images and files.', '/admin/content/media');
    }

    if ($this->hasAnyRole($roles, ['administrator', 'site_developer', 'content_admin', 'hr_manager'])) {
      $cards[] = $this->card('jobs', 'Jobs', 'Manage career openings.', '/admin/content');
      $cards[] = $this->card('job_applications', 'Job Applications', 'Review candidate applications.', '/admin/structure/webform/manage/job_application/results/submissions');
    }

    if ($this->hasAnyRole($roles, ['administrator', 'site_developer', 'form_manager'])) {
      $cards[] = $this->card('contact_enquiries', 'Contact Enquiries', 'Review contact form submissions.', '/admin/structure/webform/manage/contact_us/results/submissions');
    }

    if ($this->hasAnyRole($roles, ['administrator', 'site_developer', 'analytics_viewer'])) {
      $cards[] = $this->card('analytics', 'Analytics', 'View analytics and reporting summaries.', '/admin/reports');
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
   * Counts published and unpublished nodes by type.
   */
  private function countNodes(string $bundle): int {
    $storage = $this->entityTypeManager()->getStorage('node');

    return (int) $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle)
      ->count()
      ->execute();
  }

  /**
   * Counts Webform submissions by Webform ID.
   */
  private function countWebformSubmissions(string $webform_id): int {
    if (!$this->moduleHandler()->moduleExists('webform')) {
      return 0;
    }

    $storage = $this->entityTypeManager()->getStorage('webform_submission');

    return (int) $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('webform_id', $webform_id)
      ->count()
      ->execute();
  }

  /**
   * Returns recent content updates.
   */
  private function getRecentContent(): array {
    $storage = $this->entityTypeManager()->getStorage('node');

    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', ['site_page', 'service', 'partner', 'team_member', 'job'], 'IN')
      ->sort('changed', 'DESC')
      ->range(0, 10)
      ->execute();

    $items = [];

    foreach ($storage->loadMultiple($ids) as $node) {
      $items[] = [
        'id' => (int) $node->id(),
        'type' => $node->bundle(),
        'title' => $node->label(),
        'changed' => (int) $node->getChangedTime(),
      ];
    }

    return $items;
  }

}
