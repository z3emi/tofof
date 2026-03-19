const path = require('path');
const express = require('express');
const qrcode = require('qrcode');
const { Client, LocalAuth } = require('whatsapp-web.js');
require('dotenv').config({ path: path.resolve(__dirname, '.env') });

const app = express();
app.use(express.json({ limit: '1mb' }));

const PORT = Number(process.env.PORT || 3001);
const API_KEY = String(process.env.WHATSAPP_SERVICE_KEY || '').trim();
const CHROME_PATH = (process.env.PUPPETEER_EXECUTABLE_PATH || process.env.CHROME_BIN || '').trim() || null;

console.log('[Config] PORT:', PORT);
console.log('[Config] CHROME_PATH:', CHROME_PATH || '(bundled)');
console.log('[Config] API auth enabled:', API_KEY !== '');

const state = {
  status: 'initializing',
  qr: null,
  phone: null,
  lastError: null,
};

let client = null;
let isInitializing = false;

function normalizePhone(rawPhone) {
  const raw = String(rawPhone || '').trim();

  if (raw.endsWith('@c.us')) {
    return raw;
  }

  let digits = raw.replace(/\D+/g, '');

  if (!digits) {
    return null;
  }

  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.startsWith('0')) {
    digits = `964${digits.replace(/^0+/, '')}`;
  }

  return `${digits}@c.us`;
}

function authMiddleware(req, res, next) {
  if (!API_KEY) {
    return next();
  }

  const provided = String(req.header('X-API-Key') || '').trim();

  if (provided !== API_KEY) {
    console.warn('[Auth] Unauthorized request', {
      providedLength: provided.length,
      expectedLength: API_KEY.length,
    });

    return res.status(401).json({ success: false, message: 'Unauthorized' });
  }

  return next();
}

async function initializeClient() {
  if (isInitializing) {
    return;
  }

  isInitializing = true;
  state.status = 'initializing';
  state.lastError = null;

  console.log('[WA] Initializing WhatsApp client...');

  try {
    const puppeteerOpts = {
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--no-first-run',
        '--no-zygote',
        '--single-process',
        '--disable-extensions',
      ],
    };

    if (CHROME_PATH) {
      puppeteerOpts.executablePath = CHROME_PATH;
      console.log('[WA] Using Chrome at:', CHROME_PATH);
    } else {
      console.log('[WA] Using bundled Chromium from puppeteer');
    }

    client = new Client({
      authStrategy: new LocalAuth({
        clientId: 'tofof-main',
        dataPath: path.resolve(__dirname, '.wwebjs_auth'),
      }),
      puppeteer: puppeteerOpts,
    });

    client.on('qr', async (qrText) => {
      console.log('[WA] QR received, generating image...');
      try {
        state.qr = await qrcode.toDataURL(qrText, { width: 320, margin: 2 });
        state.status = 'qr';
        state.phone = null;
        console.log('[WA] QR ready.');
      } catch (error) {
        console.error('[WA] QR generation error:', error.message);
        state.status = 'error';
        state.lastError = String(error.message || error);
      }
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

      try {
        if (client) {
          await client.destroy();
        }
      } catch (error) {
        console.error('[WA] Destroy error:', error.message);
        state.lastError = String(error.message || error);
      }

      client = null;
      setTimeout(() => {
        initializeClient().catch((e) => console.error('[WA] Re-init error:', e.message));
      }, 1500);
    });

    console.log('[WA] Calling client.initialize()...');
    await client.initialize();
    console.log('[WA] client.initialize() completed.');
  } catch (error) {
    console.error('[WA] initializeClient error:', error.message || error);
    state.status = 'error';
    state.lastError = String(error.message || error);
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

app.post('/api/logout', async (_, res) => {
  if (!client) {
    state.status = 'logged_out';
    state.phone = null;
    state.qr = null;
    state.lastError = null;

    setTimeout(() => {
      initializeClient().catch((e) => console.error('[WA] Re-init after empty logout:', e.message));
    }, 1000);

    return res.json({ success: true, message: 'Already logged out' });
  }

  let logoutError = null;
  let destroyError = null;

  try {
    await client.logout();
    console.log('[WA] Logout request sent successfully.');
  } catch (error) {
    logoutError = String(error.message || error);
    console.warn('[WA] client.logout() failed, continuing with destroy:', logoutError);
  }

  try {
    await client.destroy();
    console.log('[WA] Client destroyed after logout.');
  } catch (error) {
    destroyError = String(error.message || error);
    console.error('[WA] client.destroy() failed:', destroyError);
  }

  client = null;
  state.status = 'logged_out';
  state.phone = null;
  state.qr = null;
  state.lastError = destroyError || logoutError;

  setTimeout(() => {
    initializeClient().catch((e) => console.error('[WA] Re-init after logout:', e.message));
  }, 1000);

  if (destroyError) {
    return res.status(500).json({
      success: false,
      message: destroyError,
      warning: logoutError,
    });
  }

  return res.json({
    success: true,
    message: logoutError ? 'Logged out with warning' : 'Logged out successfully',
    warning: logoutError,
  });
});

app.listen(PORT, () => {
  console.log(`WhatsApp service running on port ${PORT}`);
  initializeClient().catch((error) => {
    console.error('[WA] Startup init error:', error.message || error);
    state.status = 'error';
    state.lastError = String(error.message || error);
  });
});
