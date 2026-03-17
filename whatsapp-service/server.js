const path = require('path');
const express = require('express');
const qrcode = require('qrcode');
const { Client, LocalAuth } = require('whatsapp-web.js');
require('dotenv').config({ path: path.resolve(__dirname, '.env') });

const app = express();
app.use(express.json({ limit: '1mb' }));

const PORT = Number(process.env.PORT || 3001);
const API_KEY = process.env.WHATSAPP_SERVICE_KEY || '';
const CHROME_PATH = process.env.PUPPETEER_EXECUTABLE_PATH || process.env.CHROME_BIN || undefined;

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

  const provided = req.header('X-API-Key') || '';

  if (provided !== API_KEY) {
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

  try {
    client = new Client({
      authStrategy: new LocalAuth({
        clientId: 'tofof-main',
        dataPath: path.resolve(__dirname, '.wwebjs_auth'),
      }),
      puppeteer: {
        headless: true,
        executablePath: CHROME_PATH,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
      },
    });

    client.on('qr', async (qrText) => {
      try {
        state.qr = await qrcode.toDataURL(qrText, { width: 320, margin: 2 });
        state.status = 'qr';
        state.phone = null;
      } catch (error) {
        state.status = 'error';
        state.lastError = String(error.message || error);
      }
    });

    client.on('authenticated', () => {
      state.status = 'authenticated';
      state.lastError = null;
    });

    client.on('ready', () => {
      state.status = 'connected';
      state.qr = null;
      state.lastError = null;

      const user = client.info && client.info.wid ? client.info.wid.user : null;
      state.phone = user ? `+${user}` : null;
    });

    client.on('auth_failure', (message) => {
      state.status = 'auth_failure';
      state.phone = null;
      state.qr = null;
      state.lastError = String(message || 'Authentication failed');
    });

    client.on('disconnected', async () => {
      state.status = 'disconnected';
      state.phone = null;
      state.qr = null;

      try {
        if (client) {
          await client.destroy();
        }
      } catch (error) {
        state.lastError = String(error.message || error);
      }

      client = null;
      setTimeout(() => {
        initializeClient().catch(() => undefined);
      }, 1500);
    });

    await client.initialize();
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
    return res.json({ success: true, message: 'Already logged out' });
  }

  try {
    await client.logout();
    await client.destroy();
    client = null;

    state.status = 'logged_out';
    state.phone = null;
    state.qr = null;
    state.lastError = null;

    setTimeout(() => {
      initializeClient().catch(() => undefined);
    }, 1000);

    return res.json({ success: true });
  } catch (error) {
    state.lastError = String(error.message || error);
    return res.status(500).json({ success: false, message: state.lastError });
  }
});

app.listen(PORT, () => {
  // eslint-disable-next-line no-console
  console.log(`WhatsApp service running on port ${PORT}`);
  initializeClient().catch((error) => {
    state.status = 'error';
    state.lastError = String(error.message || error);
  });
});
