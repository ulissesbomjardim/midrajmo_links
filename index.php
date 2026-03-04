<?php

declare(strict_types=1);

// Headers de segurança que permitem scripts inline
header("Content-Type: text/html; charset=utf-8");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
// CSP permissivo para permitir scripts inline (necessário para o teste funcionar)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'self';");

echo <<<'HTML'
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teste de Conexão MySQL - Meu Links</title>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: #0f172a;
      color: #e2e8f0;
      font-family: Arial, sans-serif;
    }
    .card {
      width: min(560px, 92vw);
      background: #111827;
      border: 1px solid #334155;
      border-radius: 14px;
      padding: 20px;
    }
    h1 { margin-top: 0; }
    button {
      background: #22d3ee;
      color: #05202a;
      border: 0;
      border-radius: 10px;
      padding: 10px 14px;
      cursor: pointer;
      font-weight: 700;
      margin-right: 8px;
      margin-bottom: 12px;
    }
    button:hover {
      background: #1aaec7;
    }
    pre {
      margin-top: 14px;
      background: #020617;
      border: 1px solid #1e293b;
      border-radius: 10px;
      padding: 12px;
      white-space: pre-wrap;
      word-break: break-word;
      font-size: 0.9rem;
    }
    .ok { color: #22c55e; }
    .err { color: #ef4444; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Meu Links - Teste de Conexão</h1>
    <p>Clique para testar a conexão usando as credenciais do arquivo <b>.env</b>.</p>
    <div>
      <button id="healthBtn">Verificar servidor</button>
      <button id="runTest">Executar teste</button>
    </div>
    <pre id="result">Aguardando execução...</pre>
  </div>

  <script>
    const healthBtn = document.getElementById('healthBtn');
    const runBtn = document.getElementById('runTest');
    const result = document.getElementById('result');

    function getApiUrl(endpoint) {
      const pathname = window.location.pathname;
      
      if (pathname.includes('/links/')) {
        return '/links' + endpoint;
      }
      
      return endpoint;
    }

    async function tryEndpoint(url) {
      const response = await fetch(url);
      const rawText = await response.text();
      
      try {
        return JSON.parse(rawText);
      } catch (_) {
        throw new Error('Resposta não é JSON');
      }
    }

    healthBtn.addEventListener('click', async function () {
      result.className = '';
      result.textContent = 'Verificando servidor...';

      const endpoints = [
        { url: getApiUrl('/public/test-connection.php'), name: 'PHP' },
        { url: getApiUrl('/health'), name: 'Python' },
      ];

      for (const endpoint of endpoints) {
        try {
          console.log('Tentando health check:', endpoint.url);
          const data = await tryEndpoint(endpoint.url);
          
          result.className = 'ok';
          result.textContent = 'Servidor respondendo (via ' + endpoint.name + '):\n' + JSON.stringify(data, null, 2);
          return;
        } catch (error) {
          console.log('Falhou via', endpoint.name, ':', error.message);
        }
      }

      result.className = 'err';
      result.textContent = 'Servidor não está respondendo em nenhum endpoint.';
    });

    runBtn.addEventListener('click', async function () {
      result.className = '';
      result.textContent = 'Executando teste de conexão...';

      const endpoints = [
        { url: getApiUrl('/public/test-connection.php'), name: 'PHP' },
        { url: getApiUrl('/api/test-connection'), name: 'Python' },
      ];

      for (const endpoint of endpoints) {
        try {
          console.log('Testando conexão em:', endpoint.url);
          const data = await tryEndpoint(endpoint.url);
          
          if (data.ok) {
            result.className = 'ok';
          } else {
            result.className = 'err';
          }

          result.textContent = JSON.stringify(data, null, 2);
          return;
        } catch (error) {
          console.log('Falhou:', endpoint.url, error.message);
        }
      }

      result.className = 'err';
      result.textContent = 'Nenhum endpoint de teste disponível.';
    });
  </script>
</body>
</html>
HTML;
