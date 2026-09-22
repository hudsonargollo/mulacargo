const PASSWORD = "mula2026";
const AUTH_COOKIE_NAME = "mula_session";
const AUTH_TOKEN = "mula_auth_token_948fbc2a81";

function getCookie(request, name) {
  const cookieString = request.headers.get("Cookie") || "";
  const cookies = cookieString.split(";").map(c => c.trim());
  for (const cookie of cookies) {
    if (cookie.startsWith(name + "=")) {
      return cookie.substring(name.length + 1);
    }
  }
  return null;
}

function renderLoginScreen(error = false) {
  return `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso Protegido — MulaCargo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;600;700&family=Inter:wght@400;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --k: #0A0A0C;
      --k-surface: #12141A;
      --k-card: #181C26;
      --gold: #D4AF37;
      --orange: #FF5500;
      --w1: #FFFFFF;
      --w2: #E0E6ED;
      --g1: #8C929D;
      --line: rgba(224, 230, 237, 0.12);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--k);
      color: var(--w1);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
    }
    body::before {
      content: "";
      position: absolute;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(255, 85, 0, 0.12) 0%, transparent 70%);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }
    .auth-box {
      width: 100%;
      max-width: 420px;
      background: var(--k-surface);
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 2.5rem 2rem;
      position: relative;
      box-shadow: 0 24px 60px rgba(0,0,0,0.8);
      z-index: 1;
    }
    .cnr {
      position: absolute;
      width: 6px;
      height: 6px;
      border: 1px solid var(--gold);
    }
    .cnr.tl { top: -1px; left: -1px; border-right: 0; border-bottom: 0; }
    .cnr.tr { top: -1px; right: -1px; border-left: 0; border-bottom: 0; }
    .cnr.bl { bottom: -1px; left: -1px; border-right: 0; border-top: 0; }
    .cnr.br { bottom: -1px; right: -1px; border-left: 0; border-top: 0; }

    .brand {
      font-family: 'Syne', sans-serif;
      font-size: 1.6rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: -0.02em;
      margin-bottom: 0.25rem;
      text-align: center;
    }
    .brand span { color: var(--orange); }
    .sub {
      text-align: center;
      font-family: 'Geist Mono', monospace;
      font-size: 11px;
      color: var(--gold);
      letter-spacing: 0.1em;
      text-transform: uppercase;
      margin-bottom: 2rem;
    }
    .prompt-label {
      display: block;
      font-size: 0.85rem;
      color: var(--w2);
      margin-bottom: 0.5rem;
      font-weight: 500;
    }
    .input-wrap {
      position: relative;
      margin-bottom: 1.25rem;
    }
    input[type="password"] {
      width: 100%;
      background: #000;
      border: 1px solid var(--line);
      border-radius: 6px;
      padding: 0.85rem 1rem;
      color: #fff;
      font-family: 'Geist Mono', monospace;
      font-size: 1rem;
      outline: none;
      transition: all 0.2s;
    }
    input[type="password"]:focus {
      border-color: var(--orange);
      box-shadow: 0 0 12px rgba(255, 85, 0, 0.25);
    }
    button {
      width: 100%;
      background: var(--orange);
      color: #fff;
      border: none;
      padding: 0.85rem;
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: 0.95rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s;
    }
    button:hover {
      background: #e04b00;
      box-shadow: 0 4px 20px rgba(255, 85, 0, 0.4);
    }
    .error-msg {
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #F87171;
      padding: 0.6rem 0.85rem;
      border-radius: 6px;
      font-size: 0.85rem;
      margin-bottom: 1.25rem;
      text-align: center;
      font-family: 'Geist Mono', monospace;
    }
    .hint {
      text-align: center;
      font-size: 0.75rem;
      color: var(--g1);
      margin-top: 1.5rem;
      font-family: 'Geist Mono', monospace;
    }
  </style>
</head>
<body>
  <div class="auth-box">
    <div class="cnr tl"></div><div class="cnr tr"></div><div class="cnr bl"></div><div class="cnr br"></div>
    <div class="brand">MULA<span>CARGO</span></div>
    <div class="sub">// PORTAL CONFIDENCIAL</div>

    ${error ? '<div class="error-msg">Contraseña incorrecta. Intente nuevamente.</div>' : ''}

    <form method="POST" action="/login">
      <label class="prompt-label" for="pw">Ingrese Contraseña de Acceso:</label>
      <div class="input-wrap">
        <input type="password" id="pw" name="password" autofocus required placeholder="••••••••">
      </div>
      <button type="submit">Ingresar al Portal</button>
    </form>

    <div class="hint">Área protegida para revisión técnica y ejecutiva</div>
  </div>
</body>
</html>`;
}

export default {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);

    // 0. Allow App Landing, Proposal, Simulator, Conductor & Image Assets to be completely PUBLIC
    const isAsset =
      url.pathname.endsWith(".webp") ||
      url.pathname.endsWith(".png") ||
      url.pathname.endsWith(".jpg") ||
      url.pathname.endsWith(".svg") ||
      url.pathname.endsWith(".ico");

    const isPublicRoute =
      isAsset ||
      url.pathname === "/app" ||
      url.pathname === "/app.html" ||
      url.pathname === "/proposal-rene-quiroz" ||
      url.pathname === "/proposal-rene-quiroz.html" ||
      url.pathname === "/proposal" ||
      url.pathname === "/proposal.html" ||
      url.pathname === "/simulator" ||
      url.pathname === "/simulator.html" ||
      url.pathname === "/conductor" ||
      url.pathname === "/conductor.html";

    if (isPublicRoute) {
      return env.ASSETS.fetch(request);
    }

    // 1. Direct query param unlock (e.g. ?key=mula2026)
    if (url.searchParams.get("key") === PASSWORD) {
      url.searchParams.delete("key");
      const cleanUrl = url.pathname + (url.search ? url.search : "");
      return new Response(null, {
        status: 302,
        headers: {
          "Location": cleanUrl,
          "Set-Cookie": `${AUTH_COOKIE_NAME}=${AUTH_TOKEN}; Path=/; HttpOnly; SameSite=Lax; Max-Age=2592000`,
        },
      });
    }

    // 2. Handle POST login form
    if (request.method === "POST" && url.pathname === "/login") {
      try {
        const formData = await request.formData();
        const submittedPassword = formData.get("password");

        if (submittedPassword === PASSWORD) {
          const redirectTo = getCookie(request, "mula_redirect") || "/";
          return new Response(null, {
            status: 302,
            headers: {
              "Location": redirectTo,
              "Set-Cookie": `${AUTH_COOKIE_NAME}=${AUTH_TOKEN}; Path=/; HttpOnly; SameSite=Lax; Max-Age=2592000`,
            },
          });
        } else {
          return new Response(renderLoginScreen(true), {
            status: 401,
            headers: { "Content-Type": "text/html; charset=utf-8" },
          });
        }
      } catch (err) {
        return new Response(renderLoginScreen(true), {
          status: 400,
          headers: { "Content-Type": "text/html; charset=utf-8" },
        });
      }
    }

    // 3. Check Session Cookie
    const sessionCookie = getCookie(request, AUTH_COOKIE_NAME);
    const isAuthenticated = sessionCookie === AUTH_TOKEN;

    if (!isAuthenticated) {
      // Store intended destination in cookie and show login screen
      return new Response(renderLoginScreen(false), {
        status: 401,
        headers: {
          "Content-Type": "text/html; charset=utf-8",
          "Set-Cookie": `mula_redirect=${encodeURIComponent(url.pathname)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=300`,
        },
      });
    }

    // 4. Authenticated: Fetch static assets
    return env.ASSETS.fetch(request);
  },
};
