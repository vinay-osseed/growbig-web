const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const baseUrl = 'https://growbigllp.com/';
const outDir = path.resolve('docs/ui-audit/growbigllp');

fs.mkdirSync(outDir, { recursive: true });

function slugForUrl(url) {
  const parsed = new URL(url);
  let slug = parsed.pathname.replace(/^\/|\/$/g, '') || 'home';
  if (parsed.hash) {
    slug += parsed.hash.replace('#', '-');
  }
  return slug.replace(/[^a-z0-9-]+/gi, '-').toLowerCase();
}

function sameSiteUrl(href) {
  try {
    const url = new URL(href, baseUrl);
    return url.hostname === 'growbigllp.com';
  }
  catch {
    return false;
  }
}

function normalizeInternalUrl(href) {
  const url = new URL(href, baseUrl);
  url.search = '';
  return url.toString();
}

async function extractPage(page, url) {
  await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });

  const slug = slugForUrl(url);

  await page.screenshot({
    path: path.join(outDir, `${slug}.png`),
    fullPage: true,
  });

  const data = await page.evaluate(() => {
    const cleanText = (value) => (value || '').replace(/\s+/g, ' ').trim();

    const headings = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6')]
      .map((h) => ({
        tag: h.tagName.toLowerCase(),
        text: cleanText(h.innerText),
      }))
      .filter((h) => h.text);

    const text = document.body.innerText
      .split('\n')
      .map((line) => cleanText(line))
      .filter(Boolean);

    const links = [...document.querySelectorAll('a')]
      .map((a) => ({
        text: cleanText(a.innerText),
        href: a.href,
      }));

    const navLinks = [...document.querySelectorAll('header a, nav a, footer a')]
      .map((a) => ({
        text: cleanText(a.innerText),
        href: a.href,
      }));

    const images = [...document.querySelectorAll('img')]
      .map((img) => ({
        alt: img.alt || '',
        src: img.currentSrc || img.src,
        width: img.naturalWidth,
        height: img.naturalHeight,
      }));

    const buttons = [...document.querySelectorAll('button,a')]
      .map((el) => {
        const style = window.getComputedStyle(el);
        return {
          text: cleanText(el.innerText),
          tag: el.tagName.toLowerCase(),
          href: el.href || '',
          background: style.backgroundColor,
          color: style.color,
          borderRadius: style.borderRadius,
          fontSize: style.fontSize,
          fontWeight: style.fontWeight,
        };
      })
      .filter((item) => item.text);

    const sections = [...document.querySelectorAll('section, header, footer, main > div')]
      .map((section, index) => {
        const heading = section.querySelector('h1,h2,h3,h4,h5,h6');
        return {
          index,
          tag: section.tagName.toLowerCase(),
          heading: heading ? cleanText(heading.innerText) : '',
          textSample: cleanText(section.innerText).slice(0, 500),
          className: section.className || '',
        };
      })
      .filter((section) => section.textSample);

    const colors = [];
    const fonts = [];

    [...document.querySelectorAll('body *')].forEach((el) => {
      const style = window.getComputedStyle(el);
      colors.push(style.color, style.backgroundColor, style.borderColor);
      fonts.push(style.fontFamily);
    });

    return {
      url: window.location.href,
      title: document.title,
      metaDescription: document.querySelector('meta[name="description"]')?.content || '',
      headings,
      sections,
      text,
      links,
      navLinks,
      images,
      buttons,
      colors: [...new Set(colors)].filter((c) => c && c !== 'rgba(0, 0, 0, 0)').slice(0, 100),
      fonts: [...new Set(fonts)].filter(Boolean).slice(0, 40),
      html: document.documentElement.outerHTML,
    };
  });

  fs.writeFileSync(path.join(outDir, `${slug}.html`), data.html);
  delete data.html;

  fs.writeFileSync(path.join(outDir, `${slug}.json`), JSON.stringify(data, null, 2));

  return data;
}

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({
    viewport: { width: 1440, height: 2400 },
    deviceScaleFactor: 1,
  });

  const home = await extractPage(page, baseUrl);

  const discovered = home.navLinks
    .map((link) => link.href)
    .filter(Boolean)
    .filter(sameSiteUrl)
    .map(normalizeInternalUrl);

  const required = [
    'https://growbigllp.com/',
    'https://growbigllp.com/about',
    'https://growbigllp.com/careers',
    'https://growbigllp.com/contact',
    'https://growbigllp.com/#services',
  ];

  const urls = [...new Set([...required, ...discovered])];

  const pages = [];

  for (const url of urls) {
    console.log(`Auditing ${url}`);
    try {
      const existingHome = url === baseUrl || url === 'https://growbigllp.com/';
      const data = existingHome ? home : await extractPage(page, url);
      pages.push(data);
    }
    catch (error) {
      pages.push({
        url,
        error: error.message,
      });
    }
  }

  const siteAudit = {
    auditedAt: new Date().toISOString(),
    baseUrl,
    pages,
    summary: {
      pageCount: pages.length,
      urls: pages.map((item) => item.url),
      headings: pages.flatMap((item) => item.headings || []),
      images: pages.flatMap((item) => item.images || []),
      internalLinks: [...new Set(
        pages
          .flatMap((item) => item.links || [])
          .map((link) => link.href)
          .filter(Boolean)
          .filter(sameSiteUrl)
      )],
      externalLinks: [...new Set(
        pages
          .flatMap((item) => item.links || [])
          .map((link) => link.href)
          .filter(Boolean)
          .filter((href) => !sameSiteUrl(href))
      )],
      fonts: [...new Set(pages.flatMap((item) => item.fonts || []))],
      colors: [...new Set(pages.flatMap((item) => item.colors || []))],
    },
  };

  fs.writeFileSync(path.join(outDir, 'site-audit.json'), JSON.stringify(siteAudit, null, 2));

  const markdown = [
    '# GrowBig LLP Site Audit',
    '',
    `Audited: ${siteAudit.auditedAt}`,
    '',
    '## Pages',
    '',
    ...pages.map((item) => `- ${item.url}${item.error ? ` — ERROR: ${item.error}` : ''}`),
    '',
    '## Headings',
    '',
    ...siteAudit.summary.headings.map((h) => `- ${h.tag.toUpperCase()}: ${h.text}`),
    '',
    '## Images',
    '',
    ...siteAudit.summary.images.map((img) => `- ${img.alt || '(no alt)'} — ${img.src}`),
    '',
    '## Internal Links',
    '',
    ...siteAudit.summary.internalLinks.map((href) => `- ${href}`),
    '',
    '## External Links',
    '',
    ...siteAudit.summary.externalLinks.map((href) => `- ${href}`),
    '',
    '## Fonts',
    '',
    ...siteAudit.summary.fonts.map((font) => `- ${font}`),
    '',
    '## Notes for frontend rebuild',
    '',
    '- Rebuild as one frontend only.',
    '- Use the Drupal backend APIs as the content source.',
    '- Keep GrowBig branding, header, hero, services, partners, footer, careers, about, and contact pages.',
    '- Ignore old multisite/domain behavior in frontend implementation.',
    '',
  ].join('\n');

  fs.writeFileSync(path.join(outDir, 'site-audit.md'), markdown);

  await browser.close();

  console.log(`Saved full site audit to ${outDir}`);
})();
