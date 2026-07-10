(function (Drupal, once) {
  Drupal.behaviors.sitePlatformAdminDashboard = {
    attach(context) {
      once('site-platform-dashboard', '.site-dashboard', context).forEach((dashboard) => {
        const search = dashboard.querySelector('.site-dashboard-toolbar__search');
        const cards = dashboard.querySelectorAll('.site-dashboard-card');
        const refresh = dashboard.querySelector('.site-dashboard-toolbar__refresh');

        if (search) {
          search.addEventListener('input', () => {
            const query = search.value.trim().toLowerCase();

            cards.forEach((card) => {
              const text = card.textContent.toLowerCase();
              card.hidden = query !== '' && !text.includes(query);
            });
          });
        }

        if (refresh) {
          refresh.addEventListener('click', () => {
            refresh.classList.add('is-loading');
            window.location.reload();
          });
        }
      });
    },
  };
})(Drupal, once);
