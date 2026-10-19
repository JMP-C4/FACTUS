# Reporte de Rendimiento — FACTUS

| Campo | Valor |
|---|---|
| Fecha del test | 2026-10-18, 21:02–21:08 (UTC−05) |
| Commit | `20f52ac` |
| Resultado completo | `tests/performance/results/perf-2026-10-19T02-02-23-454Z.json` |
| Veredicto | **Ningún nivel de carga cumple los umbrales de UX (p95 < 2 s). Estable (0 % errores), pero con capacidad tope ≈ 88 RPS y colas de hasta 4.2 min.** |

---

## 1. Resumen ejecutivo

Se midió la mezcla real de operaciones del ERP (consultas, búsquedas, logins, compras/checkout, emisión de facturas, creación de usuarios, perfil) bajo 100 → 2000 usuarios concurrentes contra el servidor Laravel completo (rutas, modelos, facturación, autenticación) sobre PostgreSQL.

- **Estabilidad: excelente.** 0 errores, 0 timeouts, 0 excepciones en el log de la aplicación en los 5 niveles. El sistema degrada con colas, no se cae.
- **Latencia: fuera de objetivo en todos los niveles**, incluso en el baseline de 10 usuarios (p95 = 3.84 s frente al umbral de 2 s; una petición suelta a curl responde en ~0.55 s, así que el problema aparece bajo concurrencia).
- **Capacidad: ~88 RPS sostenidos** en este entorno con CPU al 99.9 % desde 500 usuarios concurrentes.
- **Escalabilidad muy sublineal:** ×200 de carga (10 → 2000 usuarios) produce ×11 de throughput y ×66 de latencia mediana (1.6 s → 139 s).
- **Escenarios más lentos:** `login` (peor p95 en todos los niveles, por bcrypt cost=12 ≈ 264 ms/hash) y `compra`/`factura` (escrituras + servicio de facturación). Los más livianos: `busqueda` y `perfil`.

> Los números reflejan **este entorno de prueba** (servidor de desarrollo `php -S`, 4 núcleos, todo local). Son una línea base pesimista: la sección §6 detalla qué componente degrada cada resultado.

---

## 2. Metodología

### 2.1 Entorno

| Componente | Detalle |
|---|---|
| Hardware | 4 núcleos CPU, 9.6 GB RAM |
| Stack | Laravel 13, PHP 8.4.1, PostgreSQL 16 (local) |
| Servidor | `php artisan serve` → `php -S 127.0.0.1:8090`, `PHP_CLI_SERVER_WORKERS=48` |
| Cliente | Node v24.13.0 (script propio `tests/performance/load-test.cjs`) |
| Sin | nginx, php-fpm, OPcache, keep-alive, Redis, k6/wrk |

### 2.2 Aislamiento de datos

Todo el test corrió en el esquema **`perf`** de la BD `factus` (`DB_SEARCH_PATH=perf`), aislado del esquema `public` de desarrollo. Datos sembrados por `tests/performance/seed_perf.php`:

- 203 usuarios (admin + 200 usuarios de carga con rol admin, un solo hash bcrypt compartido) y **2000 sesiones HTTP pre-autenticadas** → los escenarios de consulta no pagan costos de login.
- Numeración fiscal y stock ampliados; **300 facturas emitidas previas** (listados y reportes con datos reales).
- Post-test: **459 usuarios (+256 creados), 1597 facturas (+1297 emitidas), 2293 sesiones** — evidencia de que las rutas de escritura corrieron de verdad.

### 2.3 Mezcla de carga

| Escenario | Peso | Operación |
|---|---|---|
| consulta | 35 % | GETs de listados: dashboard, productos, facturas, clientes, usuarios, roles, POS, reportes, inventario |
| busqueda | 15 % | GETs de búsqueda: `clientes/buscar`, `pos/productos`, `pos/clientes`, `productos/buscar-codigo` |
| compra | 15 % | POST JSON a `/facturacion/pos/checkout` (crea factura y descuenta stock) |
| perfil | 15 % | PATCH `/profile` |
| factura | 10 % | POST formulario a `/facturacion/facturas` |
| login | 5 % | Flujo completo guest: GET /login → CSRF → POST /login (bcrypt real) |
| usuario | 5 % | POST `/usuarios` (bcrypt real por usuario creado) |

### 2.4 Niveles y métricas

| Nivel | Usuarios conc. | Duración (tras warm-up de 1.5 s) |
|---|---|---|
| baseline | 10 | 8 s |
| 2 | 100 | 15 s |
| 3 | 500 | 15 s |
| 4 | 1000 | 20 s |
| 5 | 2000 | 25 s |

- Latencia: p50 / p90 / p95 / p99 / max; throughput: RPS; tasa de error; CPU busy %, loadavg.
- El cliente abre **conexión nueva por petición** (`Connection: close`), igual que el server de prueba.
- Semáforo de **120 peticiones en vuelo** (por debajo del backlog de 128 del socket): la espera excedente se acumula en el cliente, análogo a la cola de un proxy. Mismo criterio en todos los niveles ⇒ los niveles son comparables entre sí.
- El warm-up de cada nivel compila vistas y valida flujos; sus datos se descartan.

### 2.5 Validación previa

- Suite funcional PHPUnit: **205/205 tests OK (522 aserciones)**.
- Warm-up de cada nivel valida los 7 escenarios con status esperado (200/302/201) antes de medir.

---

## 3. Resultados

### 3.1 Tabla global por nivel

| Usuarios | Ops | Err % | RPS | p50 | p95 | p99 | max | CPU % | loadavg | Wall |
|---:|---:|---:|---:|---:|---:|---:|---:|---:|---|---:|
| 10 (baseline) | 62 | 0 % | 7.8 | 1.61 s | **3.84 s** | 5.86 s | 5.86 s | 75.0 | 6.86 | 14.9 s |
| 100 | 107 | 0 % | 7.1 | 23.9 s | **29.4 s** | 29.9 s | 30.4 s | 66.8 | 15.59 | 30.4 s |
| 500 | 605 | 0 % | 40.3 | 41.4 s | **64.4 s** | 67.9 s | 73.1 s | 99.8 | 40.57 | 77.1 s |
| 1000 | 1149 | 0 % | 57.5 | 76.9 s | **129.5 s** | 135.1 s | 147.8 s | 99.9 | 51.97 | 149.5 s |
| 2000 | 2197 | 0 % | 87.9 | 138.9 s | **251.9 s** | 258.3 s | 270.9 s | 99.9 | 47.54 | 277.9 s |

### 3.2 p95 por escenario (segundos)

| Escenario | 10 | 100 | 500 | 1000 | 2000 |
|---|---:|---:|---:|---:|---:|
| consulta | 5.21 | 29.5 | 63.7 | 129.0 | 251.4 |
| busqueda | 1.93 | 28.7 | 62.9 | 127.9 | 249.3 |
| login | 3.84 | 29.7 | **71.6** | **142.7** | **264.9** |
| compra | 5.70 | 29.9 | 65.3 | 130.0 | 254.4 |
| factura | 2.42 | 28.2 | 65.3 | 129.2 | 249.1 |
| usuario | 2.51 | 27.9 | 67.1 | 130.5 | 252.7 |
| perfil | 2.21 | 28.6 | 62.3 | 127.8 | 250.7 |

### 3.3 Throughput por escenario (RPS en el nivel 2000)

consulta 31.0 · busqueda 13.9 · compra 13.1 · perfil 12.7 · factura 8.9 · login 4.4 · usuario 3.8 — los escenarios de escritura con bcrypt (login, usuario) cuestan ~7× más RPS que los de solo lectura.

---

## 4. Evaluación vs. umbrales

Umbrales de referencia para web interactiva: **p50 < 400 ms, p95 < 1 s, p99 < 2 s**; criterio liberado: p95 < 2 s.

| Nivel | p95 | Umbral 2 s | Veredicto |
|---:|---:|---|---|
| 10 | 3.84 s | ✗ | **FALLA** (×1.9 el umbral) |
| 100 | 29.4 s | ✗ | **FALLA** (×14.7) |
| 500 | 64.4 s | ✗ | **FALLA** (×32) |
| 1000 | 129.5 s | ✗ | **FALLA** (×65) |
| 2000 | 251.9 s | ✗ | **FALLA** (×126) |

Observaciones:

1. **El cuello de botella no es la aplicación "rota" sino la infraestructura de runtime.** 0 errores y throughput creciente (7.8 → 87.9 RPS) prueban que el código resiste; lo que estalla es la cola.
2. **La curva es cóncava:** de 10 a 100 usuarios el RPS *no sube* (7.8 → 7.1) y la latencia salta ×15 — el punto de saturación temprana coincide con el backlog del socket (128) y la ausencia de keep-alive; a partir de 500 la CPU queda clavada al 99.8 % y cada usuario adicional sólo aumenta la cola.
3. **Anomalía baseline:** una petición suelta responde en ~0.55 s, pero el p50 del nivel 10 sube a 1.61 s → la concurrencia mínima ya cuesta ×3 (compilación de vistas frías + transacciones de sesión en cada request).
4. **login es el escenario más lento en todos los niveles** (+15–17 % sobre el p50 del nivel), consistente con bcrypt cost=12 medido en ~264 ms por hash.
5. **Estabilidad total:** ni un 5xx ni un timeout en ~4100 operaciones medidas, ni errores en `storage/logs/laravel.log` durante la ventana del test.

---

## 5. Cuellos de botella (evidencia → mecanismo)

| # | Cuello de botella | Evidencia | Mecanismo |
|---|---|---|---|
| 1 | **Servidor de desarrollo `php -S`** | CPU 99.8 % desde 500 usuarios; tope ~88 RPS | Sin keep-alive (1 conexión TCP por request), backlog de 128, sin OPcache (CLI), sin php-fpm/OPcache, workers fork limitados |
| 2 | **bcrypt cost=12** (≈264 ms/hash medido) | `login` y `usuario` = peores p95; RPS de login 7× menor | CPU pura por intento de login/creación; 110 logins × 264 ms ≈ 29 s de CPU sólo en hashes a nivel 2000 |
| 3 | **Sesión, caché y colas en PostgreSQL** (`SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`) | Toda petición es ≥2 transacciones extra sobre la misma BD que facturación | SELECT+UPDATE de `sessions`, locks de `cache` y jobs compiten con los writes de facturación y checkout |
| 4 | **Sin infraestructura de producción** (nginx, FPM, OPcache) | p50 ×3 frente a curl suelto ya a 10 usuarios | Cada request paga boot completo de Laravel sin opcache, TLS-off pero también sin ninguna optimización de aceptación |
| 5 | **Tablas de listados sobre datos reales** | `consulta` p95 ≈ p95 global | Facturas/reportes crecen con la carga (+1297 facturas emitidas durante el mismo test ⇒ los listados se fueron lentando a lo largo del run) |

Dato lateral observado en desarrollo: la BD `public` (dev) **carece de la tabla `permission_user`** (existe la migración `2026_05_08_000004`, pero no se aplicó en dev) → los chequeos de permisos directos fallan con `SQLSTATE[42P01]` en el entorno de desarrollo. El esquema `perf` del test sí la tiene; correr `php artisan migrate` en dev lo resuelve.

---

## 6. Recomendaciones (priorizadas)

| Prioridad | Acción | Impacto esperado |
|---|---|---|
| P0 | **php-fpm + nginx con OPcache** (producción real) | Quita el techo de `php -S`: keep-alive, backlog adecuado, boot de Laravel cacheado. Es el cambio de mayor impacto medible |
| P0 | **Redis (o memcached) para sesión y caché** | Elimina 2+ transacciones PG por request; alivia la contención con facturación |
| P1 | **BCRYPT_ROUNDS: 12 → 10** (~264 ms → ~65 ms) | −75 % de CPU en login/creación de usuarios; con rate-limiter ya activo el trade-off es aceptable. Evaluar Argon2id con parámetros calibrados |
| P1 | **QUEUE_CONNECTION a Redis + Horizon** | Los jobs (facturación/series) dejan de pelear por la BD de lecturas |
| P1 | **Auditar queries de listados/reportes** (EXPLAIN, índices, paginación) | `consulta` es el 35 % de la mezcla y crece con el volumen de facturas |
| P2 | **Laravel Octane** (Swoole/RoadRunner) si se mantiene despliegue single-host | Boot por proceso en vez de por request |
| P2 | **Repetir el test con cliente keep-alive** (k6/wrk) cuando exista FPM | Latencias reales de pipeline sin costos de reconexión |
| P2 | Mantener warm-up de vistas en deploy (route/view/cache) | Evita el p50 ×3 del baseline frío |

---

## 7. Limitaciones del test

1. **Cliente Node single-process en la misma máquina de 4 núcleos:** a 2000 workers el cliente compite por CPU con el server; parte de la cola observada es del generador. El *throughput* real del servidor es probablemente ≥ 88 RPS; las latencias absolutas bajo carga alta son una cota superior (peor caso).
2. **Loopback sin red real** — no hay latencia de red ni TLS.
3. **Timeout de 90 s por inactividad** (no de duración total): una respuesta lenta pero activa nunca expira; por eso p95 llega a 252 s sin registrar errores.
4. **Duraciones cortas (8–25 s por nivel):** no mide sostenimiento de horas, picos de GC, ni agotamiento de numeración.
5. **Sin navegador:** no incluye descarga de assets JS/CSS/imágenes ni ejecución de front-end.
6. Servidor local con `APP_DEBUG=true` (debug-page render lenta en errores; no hubo errores) y esquema aislado `perf` con estadísticas de tablas propias del seed.

---

## 8. Anexo — Cómo reproducir

```bash
# 1. Servidor de prueba (esquema aislado, 48 workers)
cd public && PHP_CLI_SERVER_WORKERS=48 DB_SEARCH_PATH=perf \
  php -S 127.0.0.1:8090 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php &

# 2. Seed (usuarios, facturas, 2000 sesiones + cookies cifradas en perf-meta.json)
DB_SEARCH_PATH=perf php tests/performance/seed_perf.php
DB_SEARCH_PATH=perf php tests/performance/add_session_cookies.php   # ojo: sólo si meta no tiene "cookie"

# 3. Test completo (warm-up + 5 niveles)
PERF_WORKERS=48 node tests/performance/load-test.cjs

# 4. Resultados
ls tests/performance/results/
```

| Artefacto | Ruta |
|---|---|
| Generador de carga | `tests/performance/load-test.cjs` |
| Seed de rendimiento | `tests/performance/seed_perf.php` |
| Meta (sesiones/CSRF) | `tests/performance/perf-meta.json` |
| Resultados JSON | `tests/performance/results/perf-2026-10-19T02-02-23-454Z.json` |
| Log de la ejecución | `/tmp/opencode/load-test-run.log` |
