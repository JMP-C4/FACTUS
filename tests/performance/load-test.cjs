#!/usr/bin/env node
'use strict';

/**
 * Test de rendimiento y latencia para FACTUS (carga mixta).
 *
 * Niveles de usuarios concurrentes: 10 (baseline), 100, 500, 1000, 2000.
 * Mezcla operativa: consultas, búsquedas, logins, compras (checkout),
 * creación de facturas, creación de usuarios y actualización de perfil.
 *
 * Nota: `php -S` (artisan serve) no soporta keep-alive y usa backlog=128,
 * por lo que se limita el vuelo total a 120 peticiones (seguro bajo backlog).
 * La espera excedente se acumula en el cliente, similar a una cola de proxy.
 *
 * Uso: node load-test.js [puerto]
 */

const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const { execSync } = require('node:child_process');
const { performance } = require('node:perf_hooks');

const BASE_HOST = '127.0.0.1';
const BASE_PORT = Number(process.argv[2] || process.env.PERF_PORT || 8090);
const META_PATH = path.join(__dirname, 'perf-meta.json');
const RESULTS_DIR = path.join(__dirname, 'results');

const INFLIGHT_CAP = 120;
const REQUEST_TIMEOUT_MS = 90000;

const LEVELS = [
  { c: 10, warmupMs: 1500, durationMs: 8000, label: 'baseline' },
  { c: 100, warmupMs: 1500, durationMs: 15000, label: '' },
  { c: 500, warmupMs: 1500, durationMs: 15000, label: '' },
  { c: 1000, warmupMs: 1500, durationMs: 20000, label: '' },
  { c: 2000, warmupMs: 1500, durationMs: 25000, label: '' },
];

const CONSULTAS = [
  '/dashboard',
  '/productos',
  '/facturacion/facturas',
  '/inventario/movimientos',
  '/reportes/ventas',
  '/clientes',
  '/usuarios',
  '/roles',
  '/facturacion/pos',
  '/profile',
];

const BUSQUEDAS = [
  '/facturacion/pos/productos?term=cafe',
  '/facturacion/pos/clientes?term=ana',
  '/clientes/buscar?term=ana',
  '/productos/buscar-codigo?barcode=7701000000011',
];

const MIX = [
  { name: 'consulta', w: 35, expect: 200 },
  { name: 'busqueda', w: 15, expect: 200 },
  { name: 'login', w: 5, expect: 302 },
  { name: 'compra', w: 15, expect: 201 },
  { name: 'factura', w: 10, expect: 302 },
  { name: 'usuario', w: 5, expect: 302 },
  { name: 'perfil', w: 15, expect: 302 },
];

// ---------------------------------------------------------------- infra ----

function makeSemaphore(limit) {
  let active = 0;
  const waiters = [];
  return {
    async acquire() {
      if (active < limit) {
        active += 1;
        return;
      }
      await new Promise((resolve) => waiters.push(resolve));
    },
    release() {
      const next = waiters.shift();
      if (next) next();
      else active -= 1;
    },
  };
}

function httpReq({ method, path: reqPath, cookie, headers = {}, body = null, wantBody = false }) {
  return new Promise((resolve, reject) => {
    const finalHeaders = {
      Host: `${BASE_HOST}:${BASE_PORT}`,
      'User-Agent': 'factus-perf/1.0',
      Accept: 'text/html,application/json;q=0.9,*/*;q=0.8',
      Connection: 'close',
      ...headers,
    };
    if (cookie) finalHeaders.Cookie = cookie;

    const req = http.request(
      { host: BASE_HOST, port: BASE_PORT, path: reqPath, method, headers: finalHeaders },
      (res) => {
        const chunks = [];
        res.on('data', (chunk) => {
          if (wantBody) chunks.push(chunk);
        });
        res.on('end', () =>
          resolve({
            status: res.statusCode,
            headers: res.headers,
            body: wantBody ? Buffer.concat(chunks).toString('utf8') : '',
          }),
        );
      },
    );
    req.setTimeout(REQUEST_TIMEOUT_MS, () => {
      const err = new Error('request timeout');
      err.code = 'ETIMEDOUT';
      req.destroy(err);
    });
    req.on('error', reject);
    if (body) req.write(body);
    req.end();
  });
}

function extractSessionCookie(setCookies, cookieName) {
  if (!setCookies) return null;
  for (const raw of setCookies) {
    const match = raw.match(new RegExp(`(?:^|;\\s*)${cookieName}=([^;]+)`));
    if (match) return `${cookieName}=${match[1]}`;
  }
  return null;
}

function extractCsrfToken(html) {
  const match = html.match(/<input[^>]*name="_token"[^>]*value="([^"]+)"/);
  return match ? match[1] : null;
}

function readLoadavg() {
  try {
    return fs.readFileSync('/proc/loadavg', 'utf8').trim().split(' ').slice(0, 3).join(' ');
  } catch {
    return 'n/a';
  }
}

function readCpuJiffies() {
  const line = fs.readFileSync('/proc/stat', 'utf8').split('\n')[0];
  const parts = line.trim().split(/\s+/).slice(1).map(Number);
  const [user, nice, system, idle, iowait, irq, softirq, steal] = parts;
  const idleAll = idle + iowait;
  const total = user + nice + system + idle + iowait + irq + softirq + steal;
  return { idleAll, total };
}

function cpuBusyPct(before, after) {
  const total = after.total - before.total;
  const idle = after.idleAll - before.idleAll;
  if (total <= 0) return 0;
  return Math.round(((total - idle) / total) * 1000) / 10;
}

// ------------------------------------------------------------ estadística ---

function percentile(sorted, p) {
  if (sorted.length === 0) return 0;
  const idx = Math.max(0, Math.min(sorted.length - 1, Math.ceil((p / 100) * sorted.length) - 1));
  return sorted[idx];
}

function latencyStats(latencies) {
  if (latencies.length === 0) {
    return { min: 0, avg: 0, p50: 0, p90: 0, p95: 0, p99: 0, max: 0 };
  }
  const sorted = [...latencies].sort((a, b) => a - b);
  const sum = sorted.reduce((acc, v) => acc + v, 0);
  return {
    min: Math.round(sorted[0]),
    avg: Math.round(sum / sorted.length),
    p50: Math.round(percentile(sorted, 50)),
    p90: Math.round(percentile(sorted, 90)),
    p95: Math.round(percentile(sorted, 95)),
    p99: Math.round(percentile(sorted, 99)),
    max: Math.round(sorted[sorted.length - 1]),
  };
}

function freshStats() {
  const scenarios = {};
  for (const { name } of MIX) {
    scenarios[name] = { count: 0, ok: 0, err: 0, statuses: {}, lat: [] };
  }
  return { total: 0, ok: 0, err: 0, statuses: {}, scenarios };
}

function bump(map, key) {
  map[key] = (map[key] || 0) + 1;
}

// --------------------------------------------------------------- runners ---

const sem = makeSemaphore(INFLIGHT_CAP);
let recording = false;
let stats = freshStats();

function record(name, ok, status, ms) {
  stats.total += 1;
  bump(stats.statuses, String(status));
  const sc = stats.scenarios[name];
  sc.count += 1;
  bump(sc.statuses, String(status));
  if (ok) {
    stats.ok += 1;
    sc.ok += 1;
  } else {
    stats.err += 1;
    sc.err += 1;
  }
  sc.lat.push(ms);
}

function pickScenario() {
  const totalWeight = MIX.reduce((acc, s) => acc + s.w, 0);
  let roll = Math.random() * totalWeight;
  for (const s of MIX) {
    roll -= s.w;
    if (roll <= 0) return s;
  }
  return MIX[MIX.length - 1];
}

async function execScenario(name, ctx, level) {
  const cookieHdr = `${ctx.meta.cookieName}=${ctx.session.cookie || ctx.session.id}`;
  const token = ctx.meta.csrfToken;
  const authHeaders = { 'X-CSRF-TOKEN': token };

  switch (name) {
    case 'consulta': {
      const url = CONSULTAS[ctx.seq++ % CONSULTAS.length];
      const res = await httpReq({ method: 'GET', path: url, cookie: cookieHdr });
      return { ok: res.status === 200, status: res.status };
    }
    case 'busqueda': {
      const url = BUSQUEDAS[ctx.seq++ % BUSQUEDAS.length];
      const res = await httpReq({ method: 'GET', path: url, cookie: cookieHdr });
      return { ok: res.status === 200, status: res.status };
    }
    case 'login': {
      const emails = ctx.meta.loginEmails;
      const email = emails[(ctx.workerIdx + ctx.loginSeq++) % emails.length];
      const guest = await httpReq({ method: 'GET', path: '/login', cookie: null, wantBody: true });
      if (guest.status !== 200) return { ok: false, status: guest.status };
      const guestCookie = extractSessionCookie(guest.headers['set-cookie'], ctx.meta.cookieName);
      const guestToken = extractCsrfToken(guest.body);
      if (!guestCookie || !guestToken) return { ok: false, status: 'NO_CSRF' };
      const form = new URLSearchParams({
        _token: guestToken,
        email,
        password: ctx.meta.adminPassword,
        remember: '0',
      });
      const res = await httpReq({
        method: 'POST',
        path: '/login',
        cookie: guestCookie,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': guestToken,
        },
        body: form.toString(),
      });
      return { ok: res.status === 302, status: res.status };
    }
    case 'compra': {
      const amount =
        Math.round(ctx.meta.productPrice * (1 + ctx.meta.productTaxRate / 100) * 100) / 100;
      const payload = {
        _token: token,
        payment_form_code: '1',
        payment_method_code: 'cash',
        items: [
          {
            product_id: ctx.meta.productId,
            description: ctx.meta.productName,
            quantity: 1,
            unit_price: ctx.meta.productPrice,
            tax_rate: ctx.meta.productTaxRate,
          },
        ],
        payments: [
          {
            payment_method_code: 'cash',
            amount,
            cash_received: amount,
            cash_change: 0,
          },
        ],
      };
      const res = await httpReq({
        method: 'POST',
        path: '/facturacion/pos/checkout',
        cookie: cookieHdr,
        headers: { 'Content-Type': 'application/json', ...authHeaders },
        body: JSON.stringify(payload),
      });
      return { ok: res.status === 201, status: res.status };
    }
    case 'factura': {
      const amount =
        Math.round(ctx.meta.productPrice * (1 + ctx.meta.productTaxRate / 100) * 100) / 100;
      const form = new URLSearchParams({
        _token: token,
        payment_form_code: '1',
        payment_method_code: 'cash',
        'items[0][product_id]': String(ctx.meta.productId),
        'items[0][description]': ctx.meta.productName,
        'items[0][quantity]': '1',
        'items[0][unit_price]': String(ctx.meta.productPrice),
        'items[0][tax_rate]': String(ctx.meta.productTaxRate),
        'payments[0][payment_method_code]': 'cash',
        'payments[0][amount]': String(amount),
        'payments[0][cash_received]': String(amount),
        'payments[0][cash_change]': '0',
      });
      const res = await httpReq({
        method: 'POST',
        path: '/facturacion/facturas',
        cookie: cookieHdr,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', ...authHeaders },
        body: form.toString(),
      });
      return { ok: res.status === 302, status: res.status };
    }
    case 'usuario': {
      const seq = ctx.createSeq++;
      const form = new URLSearchParams({
        _token: token,
        name: `Load User ${level}_${ctx.workerIdx}_${seq}`,
        email: `u${level}_${ctx.workerIdx}_${seq}@perf.test`,
        password: 'Password123!',
        password_confirmation: 'Password123!',
        company_id: String(ctx.meta.companyId),
        branch_id: String(ctx.meta.branchId),
      });
      const res = await httpReq({
        method: 'POST',
        path: '/usuarios',
        cookie: cookieHdr,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', ...authHeaders },
        body: form.toString(),
      });
      return { ok: res.status === 302, status: res.status };
    }
    case 'perfil': {
      const seq = ctx.profileSeq++;
      const form = new URLSearchParams({
        _token: token,
        name: `Perf ${ctx.workerIdx}-${seq}`,
        email: ctx.session.email,
      });
      const res = await httpReq({
        method: 'PATCH',
        path: '/profile',
        cookie: cookieHdr,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', ...authHeaders },
        body: form.toString(),
      });
      return { ok: res.status === 302, status: res.status };
    }
    default:
      return { ok: false, status: 'UNKNOWN_SCENARIO' };
  }
}

async function workerLoop(ctx, level, endAt) {
  while (performance.now() < endAt) {
    const scenario = pickScenario();
    const t0 = performance.now();
    await sem.acquire();
    let out;
    try {
      out = await execScenario(scenario.name, ctx, level);
    } catch (err) {
      out = { ok: false, status: err && err.code ? err.code : 'ERR' };
    } finally {
      sem.release();
    }
    if (recording) record(scenario.name, out.ok, out.status, performance.now() - t0);
  }
}

// ------------------------------------------------------------- warm-up -----

async function warmup(meta) {
  const cookieHdr = `${meta.cookieName}=${meta.sessions[0].cookie || meta.sessions[0].id}`;
  const failures = [];
  const check = async (label, expected, opts) => {
    try {
      const res = await httpReq(opts);
      if (res.status !== expected) failures.push(`${label}: status ${res.status} (esperado ${expected})`);
    } catch (err) {
      failures.push(`${label}: ${err.code || err.message}`);
    }
  };

  for (const url of CONSULTAS) await check(`GET ${url}`, 200, { method: 'GET', path: url, cookie: cookieHdr });
  for (const url of BUSQUEDAS) await check(`GET ${url}`, 200, { method: 'GET', path: url, cookie: cookieHdr });
  await check('GET /login', 200, { method: 'GET', path: '/login', cookie: null, wantBody: true });
  const guest = await httpReq({ method: 'GET', path: '/login', cookie: null, wantBody: true });
  const guestCookie = extractSessionCookie(guest.headers['set-cookie'], meta.cookieName);
  const guestToken = extractCsrfToken(guest.body);
  if (guest.status !== 200 || !guestCookie || !guestToken) {
    failures.push('GET /login (warmup login) sin sesión/token');
  } else {
    const res = await httpReq({
      method: 'POST',
      path: '/login',
      cookie: guestCookie,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': guestToken },
      body: new URLSearchParams({
        _token: guestToken,
        email: meta.adminEmail,
        password: meta.adminPassword,
        remember: '0',
      }).toString(),
    });
    if (res.status !== 302) failures.push(`POST /login: status ${res.status}`);
  }

  const amount = Math.round(meta.productPrice * (1 + meta.productTaxRate / 100) * 100) / 100;
  const token = meta.csrfToken;
  const authHeaders = { 'X-CSRF-TOKEN': token };
  await check('POST checkout', 201, {
    method: 'POST',
    path: '/facturacion/pos/checkout',
    cookie: cookieHdr,
    headers: { 'Content-Type': 'application/json', ...authHeaders },
    body: JSON.stringify({
      _token: token,
      payment_form_code: '1',
      payment_method_code: 'cash',
      items: [
        {
          product_id: meta.productId,
          description: meta.productName,
          quantity: 1,
          unit_price: meta.productPrice,
          tax_rate: meta.productTaxRate,
        },
      ],
      payments: [
        { payment_method_code: 'cash', amount, cash_received: amount, cash_change: 0 },
      ],
    }),
  });
  await check('POST facturas', 302, {
    method: 'POST',
    path: '/facturacion/facturas',
    cookie: cookieHdr,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', ...authHeaders },
    body: new URLSearchParams({
      _token: token,
      payment_form_code: '1',
      payment_method_code: 'cash',
      'items[0][product_id]': String(meta.productId),
      'items[0][description]': meta.productName,
      'items[0][quantity]': '1',
      'items[0][unit_price]': String(meta.productPrice),
      'items[0][tax_rate]': String(meta.productTaxRate),
      'payments[0][payment_method_code]': 'cash',
      'payments[0][amount]': String(amount),
      'payments[0][cash_received]': String(amount),
      'payments[0][cash_change]': '0',
    }).toString(),
  });
  await check('POST usuarios', 302, {
    method: 'POST',
    path: '/usuarios',
    cookie: cookieHdr,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', ...authHeaders },
    body: new URLSearchParams({
      _token: token,
      name: 'Warmup User',
      email: `warmup_${Date.now()}@perf.test`,
      password: 'Password123!',
      password_confirmation: 'Password123!',
      company_id: String(meta.companyId),
      branch_id: String(meta.branchId),
    }).toString(),
  });
  await check('PATCH profile', 302, {
    method: 'PATCH',
    path: '/profile',
    cookie: cookieHdr,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', ...authHeaders },
    body: new URLSearchParams({
      _token: token,
      name: 'Warmup Profile',
      email: meta.sessions[0].email,
    }).toString(),
  });

  return failures;
}

// -------------------------------------------------------------- niveles ----

async function runLevel(level, meta) {
  recording = false;
  stats = freshStats();

  const cpuBefore = readCpuJiffies();
  const loadBefore = readLoadavg();
  const t0 = performance.now();
  const measureEndAt = t0 + level.warmupMs + level.durationMs;

  const flip = setTimeout(() => {
    stats = freshStats();
    recording = true;
  }, level.warmupMs);

  const workers = [];
  for (let i = 0; i < level.c; i++) {
    const session = meta.sessions[i % meta.sessions.length];
    const ctx = {
      meta,
      session,
      workerIdx: i,
      seq: 0,
      loginSeq: 0,
      createSeq: 0,
      profileSeq: 0,
    };
    workers.push(workerLoop(ctx, level.c, measureEndAt));
  }
  await Promise.all(workers);
  clearTimeout(flip);
  recording = false;

  const cpuAfter = readCpuJiffies();
  const loadAfter = readLoadavg();
  const wallSec = Math.round((performance.now() - t0) / 10) / 100;
  const durationSec = level.durationMs / 1000;

  const allLat = [];
  const scenarios = {};
  for (const { name } of MIX) {
    const sc = stats.scenarios[name];
    const lat = latencyStats(sc.lat);
    sc.lat.forEach((v) => allLat.push(v));
    scenarios[name] = {
      count: sc.count,
      ok: sc.ok,
      err: sc.err,
      errRate: sc.count ? Math.round((sc.err / sc.count) * 10000) / 100 : 0,
      rps: Math.round((sc.count / durationSec) * 10) / 10,
      latency: lat,
      statuses: sc.statuses,
    };
  }

  return {
    concurrency: level.c,
    label: level.label,
    warmupSec: level.warmupMs / 1000,
    durationSec,
    wallSec,
    total: stats.total,
    ok: stats.ok,
    err: stats.err,
    errRate: stats.total ? Math.round((stats.err / stats.total) * 10000) / 100 : 0,
    rps: Math.round((stats.total / durationSec) * 10) / 10,
    okRps: Math.round((stats.ok / durationSec) * 10) / 10,
    latency: latencyStats(allLat),
    statuses: stats.statuses,
    scenarios,
    cpuBusyPct: cpuBusyPct(cpuBefore, cpuAfter),
    loadavgBefore: loadBefore,
    loadavgAfter: loadAfter,
  };
}

function fmtMs(ms) {
  if (ms >= 10000) return `${(ms / 1000).toFixed(1)}s`;
  if (ms >= 1000) return `${(ms / 1000).toFixed(2)}s`;
  return `${ms}ms`;
}

function printLevel(summary) {
  const l = summary.latency;
  console.log(
    `Nivel ${String(summary.concurrency).padStart(4)}${summary.label ? ` (${summary.label})` : ''}` +
      ` | ops=${summary.total} err=${summary.err} (${summary.errRate}%)` +
      ` | RPS=${summary.rps} ok=${summary.okRps}` +
      ` | p50=${fmtMs(l.p50)} p95=${fmtMs(l.p95)} p99=${fmtMs(l.p99)} max=${fmtMs(l.max)}` +
      ` | CPU=${summary.cpuBusyPct}% load=${summary.loadavgAfter}` +
      ` | wall=${summary.wallSec}s`,
  );
  for (const { name } of MIX) {
    const sc = summary.scenarios[name];
    if (sc.count === 0) continue;
    console.log(
      `    ${name.padEnd(9)} n=${String(sc.count).padStart(5)} ok=${String(sc.ok).padStart(5)}` +
        ` err=${String(sc.err).padStart(4)} rps=${String(sc.rps).padStart(6)}` +
        ` p50=${fmtMs(sc.latency.p50).padStart(7)} p95=${fmtMs(sc.latency.p95).padStart(7)}` +
        ` p99=${fmtMs(sc.latency.p99).padStart(7)}` +
        (sc.err ? ` statuses=${JSON.stringify(sc.statuses)}` : ''),
    );
  }
}

// ----------------------------------------------------------------- main ----

async function main() {
  if (!fs.existsSync(META_PATH)) {
    console.error(`No existe ${META_PATH}. Ejecuta primero: DB_SEARCH_PATH=perf php tests/performance/seed_perf.php`);
    process.exit(1);
  }
  const meta = JSON.parse(fs.readFileSync(META_PATH, 'utf8'));
  fs.mkdirSync(RESULTS_DIR, { recursive: true });

  console.log(`FACTUS perf test → http://${BASE_HOST}:${BASE_PORT}`);
  console.log(`Sesiones: ${meta.sessions.length} | vuelo máx: ${INFLIGHT_CAP} | niveles: ${LEVELS.map((l) => l.c).join(', ')}`);

  console.log('\n[1/2] Warm-up (compila vistas y valida flujos)...');
  const failures = await warmup(meta);
  if (failures.length) {
    console.error('Warm-up con fallos:');
    for (const f of failures) console.error(`  - ${f}`);
    process.exit(2);
  }
  console.log('Warm-up OK.\n');

  console.log('[2/2] Ejecutando niveles de carga:');
  const levelResults = [];
  for (const level of LEVELS) {
    const summary = await runLevel(level, meta);
    levelResults.push(summary);
    printLevel(summary);
  }

  let commit = 'n/a';
  try {
    commit = execSync('git rev-parse --short HEAD', { cwd: __dirname }).toString().trim();
  } catch {
    /* noop */
  }

  const result = {
    generatedAt: new Date().toISOString(),
    target: `http://${BASE_HOST}:${BASE_PORT}`,
    gitCommit: commit,
    node: process.version,
    cpuCores: os.cpus().length,
    totalMemGB: Math.round((os.totalmem() / 1024 ** 3) * 10) / 10,
    inflightCap: INFLIGHT_CAP,
    requestTimeoutMs: REQUEST_TIMEOUT_MS,
    serverWorkers: process.env.PERF_WORKERS || 'unknown',
    mix: MIX,
    seedCounts: meta.counts,
    levels: levelResults,
  };

  const stamp = result.generatedAt.replace(/[:.]/g, '-');
  const file = path.join(RESULTS_DIR, `perf-${stamp}.json`);
  fs.writeFileSync(file, JSON.stringify(result, null, 2));
  fs.writeFileSync(path.join(RESULTS_DIR, 'latest.json'), JSON.stringify(result, null, 2));

  console.log(`\nResultados: ${file}`);
  console.log('Resumen (p95 por escenario):');
  const header = ['escenario'.padEnd(10), ...LEVELS.map((l) => `c=${l.c}`.padStart(12))].join(' ');
  console.log(header);
  for (const { name } of MIX) {
    const row = [name.padEnd(10)];
    for (const level of LEVELS) {
      const res = levelResults.find((r) => r.concurrency === level.c);
      const sc = res.scenarios[name];
      row.push(sc.count ? `p95 ${fmtMs(sc.latency.p95)}`.padStart(12) : '-'.padStart(12));
    }
    console.log(row.join(' '));
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
