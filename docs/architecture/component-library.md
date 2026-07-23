# Site Platform Component Library

## Purpose

The component library defines backend-managed components that can be rendered by any frontend framework.

Each component must have:

- machine type
- human label
- admin description
- variants
- fields/props
- demo preview data
- API contract
- frontend rendering guidance

## API Shape

Each component should normalize to this shape:

    {
      "id": "hero-home",
      "type": "Hero",
      "variant": "split",
      "props": {},
      "cache": {}
    }

## Required Components

### Header / Navigation

Supports:

- logo
- menu
- CTA button
- mobile menu behavior
- language switcher later

### Hero

Variants:

- centered
- split
- minimal
- dark background
- image background
- video background

### HeroSlider

Hero section with multiple slides.

Variants:

- basic
- advanced
- fullscreen
- split slide
- media background

### SliderBasic

Simple image/content slider.

### SliderAdvanced

Advanced configurable slider.

Supports:

- images
- videos
- per-slide CTA buttons
- per-slide background style
- autoplay settings
- transition settings
- overlay settings
- responsive behavior

### MediaSlider

Slider for images, videos, or mixed media.

### ContentCarousel

Dynamic slider based on content source.

Examples:

- services carousel
- partners carousel
- jobs carousel
- team carousel
- case studies carousel
- blog/news carousel

### LogoSlider

Used for partner/client logos.

### TestimonialSlider

Used for testimonial cards.

### RichText

Formatted editorial content.

### ImageText

Image plus text layout.

### CardGrid

Generic cards.

### FeatureGrid

Feature/icon grid.

### ServiceGrid

Services listing component.

### PartnerGrid

Partners listing component.

### TeamGrid

Team/leadership listing component.

### JobList

Careers/open roles listing component.

### ContactInfo

Contact details block.

### FormEmbed

Embeds backend form schema.

### MapBlock

Map/address block.

### CTA

Call-to-action section.

### Stats

Number/stat section.

### FAQ

Question and answer list.

### Gallery

Image gallery.

### PricingTable

Pricing/packages component.

### ProcessSteps

Step-by-step workflow component.

### Tabs

Tabbed content.

### Accordion

Expandable content.

### AlertBanner

Announcement/banner component.

### Spacer

Layout spacing utility.

### Divider

Section divider.

### Footer

Footer content/menu/social block.

## Slider Requirements

All slider components must support:

- manual slides
- dynamic content source where needed
- image/video/media fields
- title
- subtitle
- summary
- primary button
- secondary button
- active/inactive slide status
- ordering/weight
- autoplay on/off
- interval
- transition style
- arrows on/off
- dots on/off
- pause on hover
- touch/swipe-friendly frontend behavior
- accessible labels
- reduced-motion safe fallback

## Preview Requirement

Every component must provide demo preview data.

Backend admin should show at least one of:

- static preview card
- preview JSON
- screenshot/mock preview reference
- connected frontend preview later
