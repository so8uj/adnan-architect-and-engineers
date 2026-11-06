const puppeteer = require('../pptr/node_modules/puppeteer-core');
const fs = require('fs');
(async () => {
  const FPS = 30, DUR = 56;
  const FROM = process.argv[3] ? Number(process.argv[3]) : 0;   // re-render only the frames from this index on (the earlier ones are unchanged)
  const only = process.argv[2] ? process.argv[2].split(',').map(Number) : null;   // optional list of seconds for a quick preview
  const browser = await puppeteer.launch({
    executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new',
    args: ['--allow-file-access-from-files', '--no-sandbox', '--force-device-scale-factor=1', '--hide-scrollbars'],
    defaultViewport: { width: 1920, height: 1080, deviceScaleFactor: 1 },
  });
  const page = await browser.newPage();
  page.on('pageerror', (e) => console.log('PAGEERROR', e.message));
  page.on('console', (m) => { if (m.type() === 'error') console.log('CONSOLE', m.text()); });
  await page.goto('file:///C:/Users/User/AppData/Local/Temp/claude/d--ProjectsPath-Adnan-Architect-and-Engineers/eef3cfc4-7078-48d2-8b38-8099e9b7be2c/scratchpad/vid/composer.html', { waitUntil: 'load' });
  await page.evaluate(() => document.fonts.ready);
  await new Promise((r) => setTimeout(r, 2500));
  const cdp = await page.createCDPSession();
  const dir = only ? 'preview' : 'frames';
  if (only || FROM === 0) fs.rmSync(dir, { recursive: true, force: true });
  fs.mkdirSync(dir, { recursive: true });
  const times = only || Array.from({ length: FPS * DUR - FROM }, (_, i) => (i + FROM) / FPS);
  let n = only ? 0 : FROM;
  for (const t of times) {
    await page.evaluate((x) => window.seek(x), t);
    const { data } = await cdp.send('Page.captureScreenshot', { format: 'jpeg', quality: only ? 80 : 93, optimizeForSpeed: true });
    fs.writeFileSync(`${dir}/${only ? 't' + t.toFixed(1) : String(n).padStart(5, '0')}.jpg`, Buffer.from(data, 'base64'));
    if (!(n % 60)) console.log('frame', n, 't=', t.toFixed(2));
    n++;
  }
  console.log('DONE', n);
  await browser.close();
})().catch((e) => { console.error('FAIL', e); process.exit(1); });
