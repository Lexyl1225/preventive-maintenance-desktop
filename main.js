const { app, BrowserWindow, Menu, ipcMain, dialog, shell, protocol, net } = require('electron');
const path = require('path');
const fs = require('fs');
const { pathToFileURL } = require('url');

let mainWindow;

// Resolve paths that work in both dev and packaged (asar) mode
function getResourcePath(...parts) {
  if (app.isPackaged) {
    return path.join(process.resourcesPath, ...parts);
  }
  return path.join(__dirname, ...parts);
}

// Register custom protocol to serve local assets (images) to remote pages
protocol.registerSchemesAsPrivileged([
  { scheme: 'app-asset', privileges: { bypassCSP: true, supportFetchAPI: true } }
]);

function createWindow() {
  // Handle app-asset:// protocol to serve local files from extraResources
  protocol.handle('app-asset', (request) => {
    const urlPath = decodeURIComponent(new URL(request.url).pathname);
    const filePath = getResourcePath(urlPath);
    return net.fetch(pathToFileURL(filePath).toString());
  });
  mainWindow = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      sandbox: false,  // Required for preload script to use require()
      preload: path.join(__dirname, 'preload.js')
    },
    icon: getResourcePath('icon.ico')
  });

  // Load your web app URL
  mainWindow.loadURL('https://preventive-maintenance.tomsworld.cloud');

  // Small JS snippets to show/hide a toast inside the page
  const SHOW_LOADING_TOAST_JS = `
    (function(){
      try{
        var id = 'epm-loading-toast';
        var el = document.getElementById(id);
        if(!el){
          el = document.createElement('div');
          el.id = id;
          Object.assign(el.style, {
            position: 'fixed',
            bottom: '20px',
            right: '20px',
            padding: '8px 12px',
            background: 'rgba(0,0,0,0.75)',
            color: '#fff',
            borderRadius: '6px',
            zIndex: 2147483647,
            fontFamily: 'system-ui, -apple-system, Roboto, "Segoe UI", Arial, sans-serif',
            fontSize: '13px',
            opacity: '0',
            transition: 'opacity 180ms ease'
          });
          el.textContent = 'Loading page';
          document.documentElement.appendChild(el);
          requestAnimationFrame(function(){ el.style.opacity = '1'; });
        } else {
          el.style.opacity = '1';
        }
        if(window.__epmLoadingToastTimeout) clearTimeout(window.__epmLoadingToastTimeout);
        window.__epmLoadingToastTimeout = setTimeout(function(){ try{ var e=document.getElementById('epm-loading-toast'); if(e){ e.style.opacity='0'; setTimeout(function(){ e.remove(); },200); } }catch(e){} }, 10000);
      }catch(e){}
    })();
  `;

  const HIDE_LOADING_TOAST_JS = `
    (function(){
      try{
        if(window.__epmLoadingToastTimeout){ clearTimeout(window.__epmLoadingToastTimeout); window.__epmLoadingToastTimeout = null; }
        var el = document.getElementById('epm-loading-toast');
        if(el){ el.style.opacity = '0'; setTimeout(function(){ try{ el.remove(); }catch(e){} }, 180); }
      }catch(e){}
    })();
  `;

  // Handle network errors (only for main frame, not sub-resources)
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL, isMainFrame) => {
    if (isMainFrame) {
      // hide any loading toast in case it is showing
      mainWindow.webContents.executeJavaScript(HIDE_LOADING_TOAST_JS).catch(() => {});
      dialog.showMessageBox(mainWindow, {
        type: 'error',
        title: 'Connection Error',
        message: 'Failed to load the app. Check your internet connection.',
        detail: `Error: ${errorDescription} (${errorCode})`,
        buttons: ['Retry', 'Quit']
      }).then(result => {
        if (result.response === 0) {
          mainWindow.loadURL('https://preventive-maintenance.tomsworld.cloud');
        } else {
          app.quit();
        }
      });
    }
  });

  // Open dev tools only in development
  if (!app.isPackaged) {
    mainWindow.webContents.openDevTools();
  }

  // Custom menu
  const menu = Menu.buildFromTemplate([
    {
      label: 'Navigation',
      submenu: [
        { label: 'Back', accelerator: 'Alt+Left', click: () => { if (mainWindow.webContents.canGoBack()) mainWindow.webContents.goBack(); } },
        { label: 'Forward', accelerator: 'Alt+Right', click: () => { if (mainWindow.webContents.canGoForward()) mainWindow.webContents.goForward(); } },
        { label: 'Reload', accelerator: 'CmdOrCtrl+R', click: () => mainWindow.reload() },
        { type: 'separator' },
        { label: 'Home', accelerator: 'CmdOrCtrl+H', click: () => mainWindow.loadURL('https://preventive-maintenance.tomsworld.cloud') },
        { type: 'separator' },
        { label: 'Print', accelerator: 'CmdOrCtrl+P', click: () => printCurrentPage(mainWindow) }
      ]
    },
    {
      label: 'View',
      submenu: [
        { label: 'Toggle Full Screen', accelerator: 'F11', click: () => mainWindow.setFullScreen(!mainWindow.isFullScreen()) },
        { label: 'Zoom In', accelerator: 'CmdOrCtrl+Plus', click: () => { mainWindow.webContents.zoomLevel += 0.5; } },
        { label: 'Zoom Out', accelerator: 'CmdOrCtrl+-', click: () => { mainWindow.webContents.zoomLevel -= 0.5; } },
        { label: 'Reset Zoom', accelerator: 'CmdOrCtrl+0', click: () => { mainWindow.webContents.zoomLevel = 0; } }
      ]
    },
    {
      label: 'Help',
      submenu: [
        { label: 'About', click: () => dialog.showMessageBox(mainWindow, { icon: getResourcePath('icon.ico'), message: 'Preventive Maintenance Desktop App\nVersion 1.0.3' }) },
        { type: 'separator' },
        { label: 'How to use EPM', accelerator: 'CmdOrCtrl+G', click: () => mainWindow.loadURL('https://serverx.ratfish-regulus.ts.net/how-to-use-epm-system/') },
        { type: 'separator' }
      ]
    },
    {
      label: 'Electrical Accomplishments',
      submenu: [
        { label: 'Open Reports', accelerator: 'CmdOrCtrl+R', click: () => mainWindow.loadURL('https://serverx.ratfish-regulus.ts.net') },
        { type: 'separator' }

      ]
    },
    {
      label: 'BOM/BOQ System',
      submenu: [
        { label: 'Login', accelerator: 'CmdOrCtrl+B', click: () => mainWindow.loadURL('https://servery.ratfish-regulus.ts.net/bom/login.php') },
        { type: 'separator' }

      ]
    },
    {
      label: 'Quit',
      click: () => app.quit(),
      accelerator: 'CmdOrCtrl+Q'
    }
  ]);
  Menu.setApplicationMenu(menu);

  // Prevent new windows — open preview/print popups as child windows, navigate links in main window
  mainWindow.webContents.setWindowOpenHandler(({ url, frameName, features }) => {
    // If it has window features (popup-style) or is about:blank, it's likely a preview/print popup — allow it
    if (features || url === 'about:blank' || url === '') {
      return {
        action: 'allow',
        overrideBrowserWindowOptions: {
          parent: mainWindow,
          icon: getResourcePath('icon.ico'),
          webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            sandbox: false,
            preload: path.join(__dirname, 'preload.js')
          }
        }
      };
    }

    // For regular links (_blank), open in the same main window
    mainWindow.loadURL(url);
    return { action: 'deny' };
  });

  // Handle navigations — intercept PDF URLs so they render in the app
  mainWindow.webContents.on('will-navigate', async (event, url) => {
    try {
      if (typeof url === 'string' && url.match(/\.pdf(\?|$)/i)) {
        event.preventDefault();
        const tempPath = path.join(app.getPath('temp'), `epm-pdf-${Date.now()}.pdf`);
        const res = await net.fetch(url);
        if (!res || !res.ok) {
          throw new Error(`Network response ${res && res.status}`);
        }
        const arrayBuffer = await res.arrayBuffer();
        fs.writeFileSync(tempPath, Buffer.from(arrayBuffer));
        // Create a small HTML wrapper that embeds the PDF in an iframe.
        // This prevents any site background images or styles from covering the PDF.
        const wrapperPath = path.join(app.getPath('temp'), `epm-pdf-${Date.now()}.html`);
        const pdfUrl = pathToFileURL(tempPath).toString();
        const wrapperHtml = `<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge" /><meta name="viewport" content="width=device-width,initial-scale=1" /><style>html,body,iframe{height:100%;width:100%;margin:0;background:#fff}body{background:#fff!important}iframe{border:0}</style></head><body><iframe src="${pdfUrl}" allowfullscreen></iframe></body></html>`;
        fs.writeFileSync(wrapperPath, wrapperHtml, 'utf8');
        await mainWindow.loadURL(pathToFileURL(wrapperPath).toString());
        return;
      }
    } catch (err) {
      console.error('Failed to display PDF inline:', err);
      dialog.showMessageBox(mainWindow, {
        type: 'info',
        message: 'Opening PDF externally',
        detail: `Could not display PDF inside the app: ${err && err.message ? err.message : err}`
      }).then(() => {
        shell.openExternal(url);
      });
    }
    // otherwise allow normal navigation
  });

  // Fix print preview: use Electron's built-in print dialog (works for main + child windows)
  ipcMain.on('print-page', (event) => {
    const win = BrowserWindow.fromWebContents(event.sender);
    if (win) {
      printCurrentPage(win);
    }
  });

  // Handle back navigation IPC from preload
  ipcMain.on('nav-back', () => { if (mainWindow.webContents.canGoBack()) mainWindow.webContents.goBack(); });
  ipcMain.on('nav-quit', () => app.quit());

  // Domains where we want to remove any background images
  const NO_BG_HOSTNAMES = new Set([
    'serverx.ratfish-regulus.ts.net'
  ]);

  // When a page finishes loading, remove background images for matching hosts.
  mainWindow.webContents.on('did-finish-load', async () => {
    try {
      const currentURL = mainWindow.webContents.getURL();
      const hostname = new URL(currentURL).hostname;
      if (NO_BG_HOSTNAMES.has(hostname)) {
        // Strong CSS override to remove background images but preserve background colors
        await mainWindow.webContents.insertCSS(`
          html, body, * {
            background-image: none !important;
          }
        `);

        // Also clear inline styles that may set background images
        await mainWindow.webContents.executeJavaScript(`
          try {
            Array.from(document.querySelectorAll('*')).forEach(el => {
              if (el && el.style) {
                el.style.backgroundImage = 'none';
              }
            });
            if (document.documentElement) document.documentElement.style.backgroundImage = 'none';
            if (document.body) document.body.style.backgroundImage = 'none';
          } catch (e) {}
        `, true);

        // hide loading toast once finished
        await mainWindow.webContents.executeJavaScript(HIDE_LOADING_TOAST_JS, true).catch(() => {});
      }
    } catch (e) {
      // ignore malformed URLs or other errors
    }
  });

  // Handle in-page navigation (single-page apps) as well
  mainWindow.webContents.on('did-navigate-in-page', (event, url) => {
    try {
      const hostname = new URL(url).hostname;
      if (NO_BG_HOSTNAMES.has(hostname)) {
        mainWindow.webContents.insertCSS(`
          html, body, * {
            background-image: none !important;
          }
        `).catch(() => {});
        mainWindow.webContents.executeJavaScript(`
          try {
            Array.from(document.querySelectorAll('*')).forEach(el => {
              if (el && el.style) {
                el.style.backgroundImage = 'none';
              }
            });
            if (document.documentElement) document.documentElement.style.backgroundImage = 'none';
            if (document.body) document.body.style.backgroundImage = 'none';
          } catch (e) {}
        `, true).catch(() => {});
        // show toast for in-page navigation
        mainWindow.webContents.executeJavaScript(SHOW_LOADING_TOAST_JS).catch(() => {});
      }
    } catch (e) {}
  });

  // Show toast when a top-level navigation starts
  mainWindow.webContents.on('did-start-loading', () => {
    try {
      const currentURL = mainWindow.webContents.getURL();
      const hostname = new URL(currentURL).hostname;
      if (NO_BG_HOSTNAMES.has(hostname)) {
        mainWindow.webContents.executeJavaScript(SHOW_LOADING_TOAST_JS).catch(() => {});
      }
    } catch (e) {}
  });

  // Also show toast for did-navigate (top-level navigations)
  mainWindow.webContents.on('did-navigate', (event, url) => {
    try {
      const hostname = new URL(url).hostname;
      if (NO_BG_HOSTNAMES.has(hostname)) {
        mainWindow.webContents.executeJavaScript(SHOW_LOADING_TOAST_JS).catch(() => {});
      }
    } catch (e) {}
  });
}

// Print current page as A4 PDF capturing all content, then open for printing
async function printCurrentPage(win) {
  try {
    // Inject CSS to expand all content before printing
    await win.webContents.insertCSS(`
      @media print {
        * { overflow: visible !important; max-height: none !important; }
        html, body { height: auto !important; overflow: visible !important; }
      }
    `);

    const pdfData = await win.webContents.printToPDF({
      paperWidth: 8.27,       // A4 width in inches (210mm)
      paperHeight: 11.69,     // A4 height in inches (297mm)
      marginsType: 0,         // Default margins
      printBackground: true,  // Include background colors/images
      printSelectionOnly: false,
      landscape: false,
      generateTaggedPDF: false,
      preferCSSPageSize: true  // Respect @page CSS if present
    });

    // Save to temp file and open with default PDF viewer for printing
    const tempPath = path.join(app.getPath('temp'), `pm-print-${Date.now()}.pdf`);
    fs.writeFileSync(tempPath, pdfData);
    shell.openPath(tempPath);
  } catch (err) {
    dialog.showErrorBox('Print Error', `Failed to generate PDF: ${err.message}`);
  }
}

// App lifecycle
app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) createWindow();
});
