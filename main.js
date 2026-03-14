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

  // Handle network errors (only for main frame, not sub-resources)
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL, isMainFrame) => {
    if (isMainFrame) {
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
        { label: 'About', click: () => dialog.showMessageBox(mainWindow, { icon: getResourcePath('icon.ico'), message: 'Preventive Maintenance Desktop App\nVersion 1.0' }) }
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

  // Also handle target="_blank" and window.open via deprecated new-window event
  mainWindow.webContents.on('will-navigate', (event, url) => {
    // Allow navigation within the same window (no action needed, it stays in mainWindow)
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