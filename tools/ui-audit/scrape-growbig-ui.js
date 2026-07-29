const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const url = 'https://growbigllp.com/';
const outDir = path.resolve('docs/ui-audit/growbigllp');

fs.mkdirSync(outDir, { recursive: true });

function uniq(items) {
  return [...new Set(items.filter(Boolean))];
}

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({
    viewport: { width: 1440, height: 2400 },
    deviceScaleFactor: 1,
  });

  await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });

  await page.screenshot({
    path: path.join(outDir, 'homepage-full.png'),
    fullPage: true,
  });

  const data = await page.evaluate(() => {
    const text = document.body.innerText
      .split('\n')
      .map((line) => line.trim())
      .filter(Boolean);

    const links = [...document.querySelectorAll('a')].map((a) => ({
      text: a.innerText.trim(),
      href: a.href,
    }));

    const images = [...document.querySelectorAll('img')].map((img) => ({
      alt: img.alt || '',
      src: img.currentSrc || img.src,
      width: img.naturalWidth,
      height: img.naturalHeight,
    }));

    const headings = [...document.querySelectorAll('h1,h2,h3,h4')].map((h) => ({
      tag: h.tagName.toLowerCase(),
      text: h.innerText.trim(),
    }));

    const buttons = [...document.querySelectorAll('button,a')].map((el) => {
      const style = window.getComputedStyle(el);
      return {
        text: el.innerText.trim(),
        tag: el.tagName.toLowerCase(),
        href: el.href || '',
        background: style.backgroundColor,
        color: style.color,
        borderRadius: style.borderRadius,
        fontSize: style.fontSize,
      };
    }).filter((item) => item.text);

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
      headings,
      text,
      links,
      images,
      buttons,
      colors: [...new Set(colors)].filter((c) => c && c !== 'rgba(0, 0, 0, 0)').slice(0, 80),
      fonts: [...new Set(fonts)].filter(Boolean).slice(0, 30),
      html: document.documentElement.outerHTML,
    };
  });

  fs.writeFileSync(path.join(outDir, 'homepage.html'), data.html);
  delete data.html;
  fs.writeFileSync(path.join(outDir, 'ui-audit.json'), JSON.stringify(data, null, 2));

  await browser.close();

  console.log(`Saved UI audit to ${outDir}`);
})();
