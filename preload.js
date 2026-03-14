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
      padding: 10px 0 4px 0;
      z-index: 10;
      position: relative;
    }
    #electron-header img {
      max-height: 60px;
      opacity: 0.9;
      object-fit: contain;
    }
    @media print {
      #electron-bg-overlay { display: none !important; }
      #electron-header { display: none !important; }
    }
  `;
  document.head.appendChild(bgStyle);
});