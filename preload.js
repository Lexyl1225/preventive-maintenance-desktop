// preload.js
const { ipcRenderer, contextBridge } = require('electron');

// Expose safe IPC methods to renderer
contextBridge.exposeInMainWorld('electronNav', {
  goBack: () => ipcRenderer.send('nav-back'),
  quit: () => ipcRenderer.send('nav-quit'),
  print: () => ipcRenderer.send('print-page'),
  assetURL: (filePath) => `app-asset://local/${filePath}`
});

window.addEventListener('DOMContentLoaded', () => {
  // Override window.print() to use Electron's native print dialog
  window.print = () => ipcRenderer.send('print-page');

  // Only inject UI enhancements on the main window (not popups/previews)
  if (window.opener) return;

  const tomsImgURL = `app-asset://local/images/toms.png`;

  // Inject blurred background image
  const bgOverlay = document.createElement('div');
  bgOverlay.id = 'electron-bg-overlay';
  document.body.prepend(bgOverlay);

  // Inject centered header logo
  const header = document.createElement('div');
  header.id = 'electron-header';
  header.innerHTML = `<img src="${tomsImgURL}" alt="Logo" />`;
  document.body.prepend(header);

  // Detect app background color to blend the overlay
  const bodyBg = getComputedStyle(document.body).backgroundColor || 'rgba(0,0,0,0.85)';

  const bgStyle = document.createElement('style');
  bgStyle.textContent = `
    #electron-bg-overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: -1;
      background: url('${tomsImgURL}') no-repeat center center / cover;
      filter: blur(25px) brightness(0.3);
      transform: scale(1.1);
      pointer-events: none;
    }
    /* Blend the page background to be semi-transparent so the blur shows through */
    body {
      background-color: transparent !important;
      position: relative;
    }
    /* Header logo */
    #electron-header {
      text-align: center;
      padding: 24px 48px 24px 0;
      z-index: 10;
      position: relative;
    }
    #electron-header canvas {
      display: block;
      margin: 0 auto;
      opacity: 0.9;
      max-width: 100%;
    }
    @media print {
      #electron-bg-overlay { display: none !important; }
      #electron-header { display: none !important; }
    }
  `;

  // Login-page overrides: logo panel fixed on left (66vw), login form gets remaining 34vw
  if (/login\.php/i.test(window.location.pathname)) {
    const loginStyle = document.createElement('style');
    loginStyle.textContent = `
      #electron-header {
        position: fixed !important;
        top: 0; left: 0;
        width: 66vw;
        height: 100vh;
        padding: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        /* Gradient fades right-edge into transparent so it blends with login side */
        background: linear-gradient(
          to right,
          rgba(8, 40, 80, 0.72) 0%,
          rgba(8, 40, 80, 0.55) 60%,
          rgba(8, 40, 80, 0.15) 85%,
          transparent 100%
        ) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-right: none !important;
        box-shadow: none !important;
        z-index: 100;
      }
      #electron-header canvas {
        width: min(680px, calc(66vw - 60px)) !important;
        height: auto !important;
        aspect-ratio: auto !important;
        max-width: 94% !important;
        margin: 0 auto;
      }
      body > *:not(#electron-header):not(#electron-bg-overlay) {
        margin-left: 66vw !important;
        width: 34vw !important;
        box-sizing: border-box !important;
      }
    `;
    document.head.appendChild(loginStyle);
  }

  document.head.appendChild(bgStyle);

  // Detect login page — used by setupClothWave for sizing.
  const isLoginPage = /login\.php/i.test(window.location.pathname);

  // Canvas-based cloth wave: each vertical strip is displaced by a
  // traveling sine wave — the same technique used in real cloth simulations.
  function setupClothWave(srcImg) {
    const nat_w = srcImg.naturalWidth  || 200;
    const nat_h = srcImg.naturalHeight || 60;
    const H_CANVAS = isLoginPage ? 400 : 60; // larger on login, standard elsewhere
    const W_CANVAS = Math.round(H_CANVAS * (nat_w / nat_h)) || 200;

    const canvas = document.createElement('canvas');
    canvas.width  = W_CANVAS;
    canvas.height = H_CANVAS;
    srcImg.parentNode.replaceChild(canvas, srcImg);

    const ctx = canvas.getContext('2d');
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';

    const amplitude = isLoginPage ? 20 : 5; // scale amplitude with image size
    const waveCycles = 2.2; // how many full wave cycles span the image width
    const speed      = 0.055; // phase advance per frame (radians)
    const sw = nat_w / W_CANVAS; // source slice width
    let phase = 0;

    (function draw() {
      ctx.clearRect(0, 0, W_CANVAS, H_CANVAS);
      ctx.save();
      ctx.beginPath();
      ctx.rect(0, 0, W_CANVAS, H_CANVAS);
      ctx.clip();
      for (let x = 0; x < W_CANVAS; x++) {
        const dy = amplitude * Math.sin((x / W_CANVAS) * Math.PI * 2 * waveCycles - phase);
        ctx.drawImage(srcImg, x * sw, 0, Math.max(sw, 1), nat_h, x, dy, 1, H_CANVAS);
      }
      ctx.restore();
      phase += speed;
      requestAnimationFrame(draw);
    })();
  }

  const logoImg = header.querySelector('img');
  if (logoImg.complete && logoImg.naturalHeight > 0) {
    setupClothWave(logoImg);
  } else {
    logoImg.addEventListener('load', () => setupClothWave(logoImg));
  }


});
