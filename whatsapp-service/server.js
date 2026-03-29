require('dotenv').config();
const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const path = require('path');
const fs = require('fs');

// IMPORTANT: Force Puppeteer to use a local cache directory in the project folder.
// This prevents "Could not find Chrome" errors when moving between Windows/Linux.
const PUPPETEER_CACHE_PATH = path.join(__dirname, '.puppeteer_cache');
process.env.PUPPETEER_CACHE_DIR = PUPPETEER_CACHE_PATH;

const app = express();
const port = process.env.WHATSAPP_PORT || 3001;
const apiKey = process.env.WHATSAPP_API_KEY || 'tofof-secret-key';

app.use(express.json());

let client = null;
let isInitializing = false;

const state = {
  status: 'disconnected', // disconnected, initializing, authenticated, connected, error, auth_failure
  qr: null,
  phone: null,
  lastError: null,
};

const normalizePhone = (phone) => {
  if (!phone) return null;
  const numeric = String(phone).replace(/\D/g, '');
  if (numeric.length === 0) return null;
  return numeric.includes('@c.us') ? numeric : `${numeric}@c.us`;
};

const authMiddleware = (req, res, next) => {
  const token = req.headers['x-api-key'];
  if (token !== apiKey) {
    return res.status(401).json({ error: 'Unauthorized: Invalid API Key' });
  }
  next();
};

const scheduleInitialize = (from, ms) => {
  console.log(`[WA] Initializing in ${ms}ms (caused by: ${from})`);
  setTimeout(() => initializeClient(), ms);
};

async function initializeClient() {
  if (isInitializing) {
     console.log('[WA] Already initializing, skipping...');
     return;
  }
  
  if (client) {
     console.log('[WA] Client already exists, skipping...');
     return;
  }

  isInitializing = true;
  state.status = 'initializing';
  state.lastError = null;
  state.qr = null;

  try {
    console.log(`[WhatsApp] Starting initialization...`);
    console.log(`[WhatsApp] Puppeteer Cache: ${PUPPETEER_CACHE_PATH}`);

    client = new Client({
      restartOnAuthFail: true,
      authStrategy: new LocalAuth({
        clientId: 'tofof-session',
        dataPath: path.join(__dirname, '.wwebjs_auth')
      }),
      puppeteer: {
        headless: true,
        args: [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-dev-shm-usage',
          '--disable-accelerated-2d-canvas',
          '--no-first-run',
          '--no-zygote',
          '--single-process',
          '--disable-gpu'
        ],
        executablePath: process.env.CHROME_PATH || undefined,
        // Make sure it looks into our custom cache
        cacheDirectory: PUPPETEER_CACHE_PATH
      }
    });

    client.on('qr', async (qr) => {
      console.log('[WA] QR Code received.');
      state.status = 'disconnected';
      state.qr = await qrcode.toDataURL(qr);
      state.lastError = null;
    });

    client.on('loading_screen', (percent, message) => {
      console.log('[WA] Loading:', percent, '%', message);
      state.status = 'initializing';
    });

    client.on('authenticated', () => {
      console.log('[WA] Authenticated.');
      state.status = 'authenticated';
      state.lastError = null;
    });

    client.on('ready', () => {
      const user = client.info && client.info.wid ? client.info.wid.user : null;
      state.phone = user ? `+${user}` : null;
      state.status = 'connected';
      state.qr = null;
      state.lastError = null;
      console.log('[WA] Ready! Phone:', state.phone);
    });

    client.on('auth_failure', (message) => {
      console.error('[WA] Auth failure:', message);
      state.status = 'auth_failure';
      state.phone = null;
      state.qr = null;
      state.lastError = String(message || 'Authentication failed');
    });

    client.on('disconnected', async (reason) => {
      console.warn('[WA] Disconnected:', reason);
      state.status = 'disconnected';
      state.phone = null;
      state.qr = null;

      const activeClient = client;
      client = null;

      try {
        if (activeClient) {
          await activeClient.destroy();
        }
      } catch (error) {
        console.error('[WA] Destroy error:', error.message);
      }
      
      scheduleInitialize('disconnected-event', 5000);
    });

    console.log('[WA] Calling client.initialize()...');
    await client.initialize();
    console.log('[WA] client.initialize() completed.');

  } catch (error) {
    const message = String(error.message || error);
    console.error('[WA] initializeClient error:', message);
    state.status = 'error';
    state.lastError = message;
    client = null;

    if (message.includes('browser is already running')) {
      state.status = 'disconnected';
      scheduleInitialize('browser-lock-retry', 5000);
    }
  } finally {
    isInitializing = false;
  }
}

app.get('/health', (_, res) => {
  res.json({ ok: true, service: 'whatsapp-web', status: state.status });
});

app.use('/api', authMiddleware);

app.get('/api/status', (_, res) => {
  res.json({
    status: state.status,
    phone: state.phone,
    hasQr: Boolean(state.qr),
    lastError: state.lastError,
  });
});

app.get('/api/qr', (_, res) => {
  res.json({
    qr: state.qr,
    status: state.status,
  });
});

app.post('/api/logout', async (req, res) => {
  console.log('[WA] Logout requested.');
  if (!client) {
    return res.status(200).json({ success: true, message: 'No active client to logout' });
  }

  try {
    const activeClient = client;
    client = null;
    
    // Add a race protection to avoid infinite loops if it hangs
    const logoutTimeout = setTimeout(() => {
        console.warn('[WA] Logout timeout - forcing destruction');
        try { activeClient.destroy(); } catch(e) {}
    }, 10000);

    await activeClient.logout();
    await activeClient.destroy();
    clearTimeout(logoutTimeout);

    state.status = 'disconnected';
    state.phone = null;
    state.qr = null;

    initializeClient();
    return res.json({ success: true });
  } catch (error) {
    console.error('[WA] Logout error:', error);
    return res.status(500).json({ success: false, message: error.message });
  }
});

app.post('/api/send', async (req, res) => {
  const phone = normalizePhone(req.body.phone);
  const message = String(req.body.message || '').trim();

  if (!phone || !message) {
    return res.status(422).json({ success: false, message: 'Invalid phone or message' });
  }

  if (!client || state.status !== 'connected') {
    return res.status(409).json({ success: false, message: 'WhatsApp is not connected' });
  }

  try {
    await client.sendMessage(phone, message);
    return res.json({ success: true });
  } catch (error) {
    state.lastError = String(error.message || error);
    return res.status(500).json({ success: false, message: state.lastError });
  }
});

app.listen(port, () => {
  console.log(`[WhatsApp] Service running on port ${port}`);
  console.log(`[WhatsApp] Local cache enforced at: ${PUPPETEER_CACHE_PATH}`);
  initializeClient();
});

// Periodic status check to ensure client is alive
setInterval(() => {
  if (!client && !isInitializing) {
     console.log('[WA] Client watchdog: client is null, re-initializing...');
     initializeClient();
  }
}, 30000);
