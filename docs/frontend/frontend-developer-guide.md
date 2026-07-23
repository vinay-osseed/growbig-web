# Site Platform Frontend Developer Guide

## Main Integration Flow

1. Call GET /api/v1/site.
2. Resolve route using GET /api/v1/routes/{path} or call page directly with GET /api/v1/pages/{slug}.
3. Render components by component type.
4. Load menus from GET /api/v1/menus/header and GET /api/v1/menus/footer.
5. Load reusable lists from GET /api/v1/content/{source}.
6. Load forms from GET /api/v1/forms/{form}.
7. Submit forms to POST /api/v1/forms/{form}/submit.

## Component Rendering

Frontend maps component types to UI components.

Example map:

    const componentMap = {
      Header,
      Hero,
      HeroSlider,
      SliderBasic,
      SliderAdvanced,
      MediaSlider,
      ContentCarousel,
      LogoSlider,
      RichText,
      ImageText,
      CardGrid,
      FeatureGrid,
      ServiceGrid,
      PartnerGrid,
      TeamGrid,
      JobList,
      ContactInfo,
      FormEmbed,
      Footer,
    };

## Important Rule

Frontend should not depend on Drupal field names.

Use normalized API contracts only.

## Unknown Components

Frontend should safely ignore or fallback unknown components.

Example:

    function RenderComponent({ component }) {
      const Component = componentMap[component.type];

      if (!Component) {
        console.warn(`Unsupported component: ${component.type}`);
        return null;
      }

      return <Component {...component.props} />;
    }
