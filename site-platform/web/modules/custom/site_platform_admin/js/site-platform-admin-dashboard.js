(function (Drupal, once) {
  Drupal.behaviors.sitePlatformAdminDashboard = {
    attach(context) {
      once('site-platform-dashboard', '.site-dashboard', context).forEach((dashboard) => {
        const refresh = dashboard.querySelector('.site-dashboard-toolbar__refresh');

        if (refresh) {
          refresh.addEventListener('click', () => {
            refresh.classList.add('is-loading');
            window.location.reload();
          });
        }

        const filterButtons = dashboard.querySelectorAll('[data-recent-filter]');
        const rows = dashboard.querySelectorAll('.site-dashboard-recent-row');

        filterButtons.forEach((button) => {
          button.addEventListener('click', () => {
            const type = button.dataset.recentFilter || '';

            filterButtons.forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');

            rows.forEach((row) => {
              const rowType = row.dataset.recentType || '';
              row.hidden = type !== '' && rowType !== type;
            });
          });
        });
      });
    },
  };
})(Drupal, once);
