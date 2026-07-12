(function (Drupal, once) {
  Drupal.behaviors.sitePlatformAdminDashboard = {
    attach(context) {
      once('site-platform-dashboard', '.site-dashboard', context).forEach((dashboard) => {
        const search = dashboard.querySelector('.site-dashboard-toolbar__search');
        const typeFilter = dashboard.querySelector('.site-dashboard-toolbar__type-filter');
        const cards = dashboard.querySelectorAll('.site-dashboard-card');
        const refresh = dashboard.querySelector('.site-dashboard-toolbar__refresh');

        const applyFilters = () => {
          const query = search ? search.value.trim().toLowerCase() : '';
          const category = typeFilter ? typeFilter.value : '';

          cards.forEach((card) => {
            const text = card.textContent.toLowerCase();
            const cardCategory = card.dataset.dashboardCategory || '';
            const matchesSearch = query === '' || text.includes(query);
            const matchesCategory = category === '' || cardCategory === category;

            card.hidden = !matchesSearch || !matchesCategory;
          });
        };

        if (search) {
          search.addEventListener('input', applyFilters);
        }

        if (typeFilter) {
          typeFilter.addEventListener('change', applyFilters);
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
