#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Fiscaut Domínio — Interface Web Local
Execute: python fiscaut_app.py
Acesse:  http://localhost:8765
"""
import http.server, socketserver, json, os, sys, tempfile, threading, webbrowser
from urllib.parse import parse_qs, urlparse

# Adiciona o diretório do script ao path para importar os módulos
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

HTML = r"""<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fiscaut × Domínio</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;600&display=swap');

  :root {
    --bg:       #0e0f11;
    --surface:  #161719;
    --border:   #2a2c30;
    --accent:   #00d4a0;
    --accent2:  #0099ff;
    --warn:     #f5a623;
    --danger:   #ff4757;
    --text:     #e8eaed;
    --muted:    #6b7280;
    --mono:     'IBM Plex Mono', monospace;
    --sans:     'IBM Plex Sans', sans-serif;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--sans);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 20px 80px;
  }

  /* Grid de fundo */
  body::before {
    content: '';
    position: fixed; inset: 0;
    background-image:
      linear-gradient(rgba(0,212,160,.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,212,160,.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
  }

  .container { max-width: 720px; width: 100%; position: relative; z-index: 1; }

  /* Header */
  header { margin-bottom: 48px; }
  .logo {
    display: flex; align-items: baseline; gap: 10px;
    margin-bottom: 8px;
  }
  .logo-main {
    font-family: var(--mono);
    font-size: 28px; font-weight: 600;
    color: var(--accent);
    letter-spacing: -1px;
  }
  .logo-sep { color: var(--border); font-size: 24px; }
  .logo-sub {
    font-family: var(--mono);
    font-size: 28px; font-weight: 400;
    color: var(--text);
    letter-spacing: -1px;
  }
  .tagline {
    font-size: 13px; color: var(--muted);
    font-family: var(--mono);
    letter-spacing: .5px;
  }
  .version-badge {
    display: inline-block;
    background: rgba(0,212,160,.1);
    border: 1px solid rgba(0,212,160,.25);
    color: var(--accent);
    font-family: var(--mono);
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 3px;
    margin-left: 10px;
    vertical-align: middle;
  }

  /* Card */
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 20px;
  }
  .card-title {
    font-family: var(--mono);
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
  }

  /* Drop zone */
  #dropzone {
    border: 2px dashed var(--border);
    border-radius: 6px;
    padding: 48px 32px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    position: relative;
  }
  #dropzone:hover, #dropzone.over {
    border-color: var(--accent);
    background: rgba(0,212,160,.04);
  }
  #dropzone.has-file {
    border-color: var(--accent);
    border-style: solid;
    background: rgba(0,212,160,.06);
  }
  #dropzone input[type=file] {
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer; width: 100%; height: 100%;
  }
  .drop-icon {
    font-size: 36px; margin-bottom: 12px;
    filter: grayscale(1);
    transition: filter .2s;
  }
  #dropzone:hover .drop-icon, #dropzone.has-file .drop-icon { filter: none; }
  .drop-text {
    font-size: 15px; color: var(--text);
    margin-bottom: 4px;
  }
  .drop-hint { font-size: 12px; color: var(--muted); font-family: var(--mono); }
  #file-name {
    margin-top: 12px;
    font-family: var(--mono);
    font-size: 13px;
    color: var(--accent);
    display: none;
  }

  /* Opções */
  .options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 4px;
  }
  .field label {
    display: block;
    font-family: var(--mono);
    font-size: 11px;
    color: var(--muted);
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 6px;
  }
  .field input, .field select {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 4px;
    color: var(--text);
    font-family: var(--mono);
    font-size: 13px;
    padding: 8px 12px;
    outline: none;
    transition: border-color .2s;
  }
  .field input:focus, .field select:focus { border-color: var(--accent); }
  .field input::placeholder { color: var(--muted); }

  /* Botão */
  #btn-gerar {
    width: 100%;
    background: var(--accent);
    color: #000;
    border: none;
    border-radius: 6px;
    padding: 14px;
    font-family: var(--mono);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all .2s;
    margin-top: 8px;
    position: relative;
    overflow: hidden;
  }
  #btn-gerar:hover:not(:disabled) { background: #00ffbf; transform: translateY(-1px); }
  #btn-gerar:disabled { background: var(--border); color: var(--muted); cursor: not-allowed; transform: none; }
  #btn-gerar .spinner {
    display: none;
    width: 16px; height: 16px;
    border: 2px solid rgba(0,0,0,.3);
    border-top-color: #000;
    border-radius: 50%;
    animation: spin .7s linear infinite;
    margin: 0 auto;
  }
  #btn-gerar.loading .btn-text { display: none; }
  #btn-gerar.loading .spinner { display: block; }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Log */
  #log-card { display: none; }
  #log {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 16px;
    font-family: var(--mono);
    font-size: 12px;
    line-height: 1.7;
    max-height: 360px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
  }
  #log::-webkit-scrollbar { width: 4px; }
  #log::-webkit-scrollbar-track { background: transparent; }
  #log::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

  .log-ok    { color: var(--accent); }
  .log-warn  { color: var(--warn); }
  .log-error { color: var(--danger); }
  .log-info  { color: var(--muted); }
  .log-title { color: var(--accent2); font-weight: 600; }

  /* Resultado */
  #result-card { display: none; }
  .result-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
  }
  .stat {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 16px;
    text-align: center;
  }
  .stat-value {
    font-family: var(--mono);
    font-size: 28px;
    font-weight: 600;
    color: var(--accent);
    display: block;
    line-height: 1.2;
  }
  .stat-label {
    font-size: 11px;
    color: var(--muted);
    font-family: var(--mono);
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-top: 4px;
    display: block;
  }
  #btn-download {
    width: 100%;
    background: transparent;
    color: var(--accent);
    border: 1px solid var(--accent);
    border-radius: 6px;
    padding: 13px;
    font-family: var(--mono);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: 10px;
  }
  #btn-download:hover { background: rgba(0,212,160,.1); }

  /* Avisos */
  .warns-list {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .warn-item {
    background: rgba(245,166,35,.07);
    border: 1px solid rgba(245,166,35,.2);
    border-radius: 4px;
    padding: 8px 12px;
    font-family: var(--mono);
    font-size: 11px;
    color: var(--warn);
  }

  /* Aviso IPI na BC */
  .aviso-ipi-bc {
    margin-top: 16px;
    background: rgba(255,71,87,.07);
    border: 1px solid rgba(255,71,87,.3);
    border-radius: 6px;
    padding: 14px 16px;
  }
  .aviso-ipi-bc-title {
    font-family: var(--mono);
    font-size: 12px;
    font-weight: 600;
    color: var(--danger);
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .aviso-ipi-bc-item {
    font-family: var(--mono);
    font-size: 11px;
    color: #ffb3bb;
    padding: 4px 0;
    border-bottom: 1px solid rgba(255,71,87,.1);
    line-height: 1.5;
  }
  .aviso-ipi-bc-item:last-child { border-bottom: none; }
  .aviso-ipi-bc-footer {
    font-family: var(--mono);
    font-size: 10px;
    color: var(--muted);
    margin-top: 8px;
    font-style: italic;
  }

  /* Novo lote */
  #btn-novo {
    background: none;
    border: none;
    color: var(--muted);
    font-family: var(--mono);
    font-size: 12px;
    cursor: pointer;
    margin-top: 12px;
    text-decoration: underline;
    display: block;
    width: 100%;
    text-align: center;
  }
  #btn-novo:hover { color: var(--text); }
</style>
</head>
<body>
<div class="container">
  <header>
    <div class="logo">
      <span class="logo-main">Fiscaut</span>
      <span class="logo-sep">×</span>
      <span class="logo-sub">Domínio</span>
      <span class="version-badge">v22g</span>
    </div>
    <div class="tagline">// gerador de arquivo TXT para importação NF-e</div>
  </header>

  <!-- Upload -->
  <div class="card" id="upload-card">
    <div class="card-title">01 — Lote de NFs</div>
    <div id="dropzone">
      <input type="file" id="file-input" accept=".zip">
      <div class="drop-icon">📦</div>
      <div class="drop-text">Arraste o ZIP do lote aqui</div>
      <div class="drop-hint">ou clique para selecionar</div>
      <div id="file-name"></div>
    </div>
  </div>

  <!-- Opções -->
  <div class="card">
    <div class="card-title">02 — Configuração</div>
    <div class="options-grid">
      <div class="field">
        <label>Nome do arquivo de saída</label>
        <input type="text" id="output-name" placeholder="dominio_abril_2026.txt">
      </div>
      <div class="field">
        <label>Empresa</label>
        <select id="empresa">
          <option value="kopron">Kopron do Brasil</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Botão -->
  <button id="btn-gerar" disabled>
    <span class="btn-text">⚡ GERAR ARQUIVO TXT</span>
    <span class="spinner"></span>
  </button>

  <!-- Log -->
  <div class="card" id="log-card" style="margin-top:20px">
    <div class="card-title">03 — Processamento</div>
    <div id="log"></div>
  </div>

  <!-- Resultado -->
  <div class="card" id="result-card" style="margin-top:20px">
    <div class="card-title">04 — Resultado</div>
    <div class="result-grid">
      <div class="stat">
        <span class="stat-value" id="stat-nfs">—</span>
        <span class="stat-label">NFs processadas</span>
      </div>
      <div class="stat">
        <span class="stat-value" id="stat-linhas">—</span>
        <span class="stat-label">Linhas geradas</span>
      </div>
      <div class="stat">
        <span class="stat-value" id="stat-erros">—</span>
        <span class="stat-label">Erros</span>
      </div>
    </div>
    <button id="btn-download">
      <span>⬇</span> Baixar arquivo TXT
    </button>
    <div class="warns-list" id="warns-list"></div>
    <div id="aviso-ipi-bc-block" style="display:none"></div>
    <button id="btn-novo">processar novo lote</button>
  </div>
</div>

<script>
let selectedFile = null;
let resultToken  = null;
let warns        = [];

const dropzone   = document.getElementById('dropzone');
const fileInput  = document.getElementById('file-input');
const fileName   = document.getElementById('file-name');
const btnGerar   = document.getElementById('btn-gerar');
const logCard    = document.getElementById('log-card');
const logDiv     = document.getElementById('log');
const resultCard = document.getElementById('result-card');
const warnsList  = document.getElementById('warns-list');

// Drag & drop
dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.classList.add('over'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('over'));
dropzone.addEventListener('drop', e => {
  e.preventDefault();
  dropzone.classList.remove('over');
  const f = e.dataTransfer.files[0];
  if (f && f.name.endsWith('.zip')) setFile(f);
});
fileInput.addEventListener('change', () => {
  if (fileInput.files[0]) setFile(fileInput.files[0]);
});

function setFile(f) {
  selectedFile = f;
  dropzone.classList.add('has-file');
  fileName.style.display = 'block';
  fileName.textContent = '📎 ' + f.name + ' (' + (f.size/1024/1024).toFixed(1) + ' MB)';
  // Sugerir nome de saída
  const base = f.name.replace(/\.zip$/i,'');
  document.getElementById('output-name').placeholder = 'dominio_' + base + '.txt';
  btnGerar.disabled = false;
}

// Gerar
btnGerar.addEventListener('click', async () => {
  if (!selectedFile) return;
  btnGerar.classList.add('loading');
  btnGerar.disabled = true;
  logCard.style.display = 'block';
  resultCard.style.display = 'none';
  logDiv.innerHTML = '';
  warns = [];

  const outputName = document.getElementById('output-name').value ||
                     ('dominio_' + selectedFile.name.replace(/\.zip$/i,'') + '.txt');

  appendLog('Enviando lote...', 'info');

  const form = new FormData();
  form.append('zip', selectedFile);
  form.append('output_name', outputName);

  try {
    // Timeout de 10 minutos para lotes grandes
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 600000);
    const resp = await fetch('/processar', { method: 'POST', body: form, signal: controller.signal });
    clearTimeout(timer);
    const data = await resp.json();

    // Exibir log formatado
    logDiv.innerHTML = '';
    (data.log || []).forEach(line => {
      const div = document.createElement('div');
      const l = line;
      if (l.startsWith('  OK'))        div.className = 'log-ok';
      else if (l.includes('⚠️'))       div.className = 'log-warn';
      else if (l.startsWith('  ERRO'))  div.className = 'log-error';
      else if (l.startsWith('===='))    div.className = 'log-title';
      else                              div.className = 'log-info';
      div.textContent = l;
      logDiv.appendChild(div);
      if (l.includes('⚠️')) warns.push(l.trim());
    });
    logDiv.scrollTop = logDiv.scrollHeight;

    if (data.ok) {
      resultCard.style.display = 'block';
      document.getElementById('stat-nfs').textContent    = data.nfs;
      document.getElementById('stat-linhas').textContent = data.linhas;
      document.getElementById('stat-erros').textContent  = data.erros || '0';
      resultToken = data.token;

      warnsList.innerHTML = '';
      warns.forEach(w => {
        const d = document.createElement('div');
        d.className = 'warn-item'; d.textContent = w;
        warnsList.appendChild(d);
      });

      // Avisos de IPI excluído da BC ICMS
      const avisoBlock = document.getElementById('aviso-ipi-bc-block');
      const avisos = data.avisos_ipi_bc || [];
      if (avisos.length > 0) {
        avisoBlock.style.display = 'block';
        avisoBlock.innerHTML = `
          <div class="aviso-ipi-bc">
            <div class="aviso-ipi-bc-title">
              ⚠ IPI EXCLUÍDO DA BASE DE CÁLCULO DO ICMS — ${avisos.length} item(s)
            </div>
            ${avisos.map(a => `<div class="aviso-ipi-bc-item">• ${a}</div>`).join('')}
            <div class="aviso-ipi-bc-footer">
              Oriente o(s) cliente(s) a emitir carta de não aproveitamento do crédito de IPI.
            </div>
          </div>`;
      } else {
        avisoBlock.style.display = 'none';
        avisoBlock.innerHTML = '';
      }
    }
  } catch(e) {
    appendLog('Erro de comunicação: ' + e.message, 'error');
  }

  btnGerar.classList.remove('loading');
  btnGerar.disabled = false;
});

// Download
document.getElementById('btn-download').addEventListener('click', () => {
  if (!resultToken) return;
  window.location = '/download?token=' + resultToken;
});

// Novo lote
document.getElementById('btn-novo').addEventListener('click', () => {
  selectedFile = null; resultToken = null; warns = [];
  dropzone.classList.remove('has-file');
  fileName.style.display = 'none';
  fileInput.value = '';
  logCard.style.display = 'none';
  resultCard.style.display = 'none';
  logDiv.innerHTML = '';
  btnGerar.disabled = true;
  document.getElementById('output-name').value = '';
  const ab = document.getElementById('aviso-ipi-bc-block');
  if (ab) { ab.style.display='none'; ab.innerHTML=''; }
});

function appendLog(text, cls) {
  const d = document.createElement('div');
  d.className = 'log-' + cls; d.textContent = text;
  logDiv.appendChild(d);
  logDiv.scrollTop = logDiv.scrollHeight;
}
</script>
</body>
</html>"""

# Armazenamento temporário dos resultados
_results = {}

class Handler(http.server.BaseHTTPRequestHandler):
    def log_message(self, *a): pass  # silencia o log HTTP

    def do_GET(self):
        path = urlparse(self.path).path
        if path == '/':
            self._send(200, 'text/html; charset=utf-8', HTML.encode('utf-8'))
        elif path == '/download':
            token = parse_qs(urlparse(self.path).query).get('token', [''])[0]
            if token in _results:
                data   = _results[token]['data']
                fname  = _results[token]['name']
                self.send_response(200)
                self.send_header('Content-Type', 'text/plain; charset=latin-1')
                self.send_header('Content-Disposition', f'attachment; filename="{fname}"')
                self.send_header('Content-Length', str(len(data)))
                self.end_headers()
                self.wfile.write(data)
            else:
                self._send(404, 'text/plain', b'Not found')
        else:
            self._send(404, 'text/plain', b'Not found')

    def do_POST(self):
        if urlparse(self.path).path != '/processar':
            self._send(404, 'text/plain', b'Not found'); return

        content_length = int(self.headers.get('Content-Length', 0))
        content_type   = self.headers.get('Content-Type', '')

        # Ler o body completo (sem limite de tamanho)
        raw = self.rfile.read(content_length)

        # Parser manual de multipart/form-data (robusto para arquivos grandes)
        import re as _re
        boundary = None
        for part in content_type.split(';'):
            part = part.strip()
            if part.startswith('boundary='):
                boundary = part[9:].strip('"').encode()
                break

        zip_data    = b''
        output_name = 'saida.txt'

        if boundary:
            sep = b'--' + boundary
            parts = raw.split(sep)
            for part in parts:
                if b'Content-Disposition' not in part: continue
                header_end = part.find(b'\r\n\r\n')
                if header_end < 0: continue
                header = part[:header_end].decode('utf-8', errors='replace')
                body   = part[header_end+4:]
                if body.endswith(b'\r\n'): body = body[:-2]

                name_m = _re.search(r'name="([^"]+)"', header)
                if not name_m: continue
                field_name = name_m.group(1)

                if field_name == 'zip':
                    zip_data = body
                elif field_name == 'output_name':
                    output_name = body.decode('utf-8', errors='replace').strip()

        if not output_name.endswith('.txt'):
            output_name += '.txt'

        if not zip_data:
            self._json({'ok': False, 'log': ['ERRO: ZIP não recebido ou vazio.'],
                        'nfs': 0, 'linhas': 0, 'erros': 1, 'token': None})
            return

        # Importar gerador
        try:
            import importlib.util as _ilu
            _spec = _ilu.spec_from_file_location(
                'g', os.path.join(os.path.dirname(os.path.abspath(__file__)),
                                  'gerar_dominio.py'))
            g = _ilu.module_from_spec(_spec)
            _spec.loader.exec_module(g)
        except Exception as e:
            self._json({'ok': False, 'log': [f'ERRO ao importar gerador: {e}'],
                        'nfs': 0, 'linhas': 0, 'erros': 1, 'token': None})
            return

        # Redirecionar stdout para capturar o log
        import io, sys as _sys
        buf = io.StringIO()
        old_stdout = _sys.stdout
        _sys.stdout = buf

        out_bytes = b''
        zip_path = out_path = None
        try:
            with tempfile.NamedTemporaryFile(suffix='.zip', delete=False) as tf:
                tf.write(zip_data); zip_path = tf.name
            with tempfile.NamedTemporaryFile(suffix='.txt', delete=False) as tf:
                out_path = tf.name

            resultado = g.processar_zip(zip_path, out_path)
            avisos_ipi_bc = resultado[1] if resultado and len(resultado) > 1 else []

            with open(out_path, 'rb') as f: out_bytes = f.read()
        except Exception as e:
            buf.write(f'  ERRO FATAL: {e}\n')
            import traceback; buf.write(traceback.format_exc())
        finally:
            _sys.stdout = old_stdout
            for p in [zip_path, out_path]:
                try:
                    if p: os.unlink(p)
                except: pass

        log_lines = buf.getvalue().splitlines()
        linhas = nfs = erros = 0
        if out_bytes:
            content = out_bytes.decode('latin-1')
            linhas  = content.count('\n')
            nfs     = len(set(
                l.split('|')[8] for l in content.splitlines()
                if l.startswith('|1000|') and len(l.split('|')) > 8
            ))
        erros = sum(1 for l in log_lines if 'ERRO' in l)

        token = None
        if out_bytes:
            import uuid
            token = str(uuid.uuid4())[:8]
            _results[token] = {'data': out_bytes, 'name': output_name}
            if len(_results) > 5:
                del _results[next(iter(_results))]

        self._json({'ok': bool(out_bytes), 'log': log_lines,
                    'nfs': nfs, 'linhas': linhas, 'erros': erros,
                    'token': token, 'avisos_ipi_bc': avisos_ipi_bc})

    def _send(self, code, ct, body):
        self.send_response(code)
        self.send_header('Content-Type', ct)
        self.send_header('Content-Length', len(body))
        self.end_headers()
        self.wfile.write(body)

    def _json(self, obj):
        body = json.dumps(obj, ensure_ascii=False).encode('utf-8')
        self._send(200, 'application/json; charset=utf-8', body)


PORT = 8765

class ThreadedServer(socketserver.ThreadingMixIn, socketserver.TCPServer):
    allow_reuse_address = True
    daemon_threads      = True
    # Sem timeout no socket — lotes grandes podem demorar vários segundos
    socket_timeout      = None

def main():
    print('=' * 50)
    print('  Fiscaut × Domínio — Interface Web v22g')
    print('=' * 50)
    print(f'\n  Acesse no navegador: http://localhost:{PORT}')
    print('  Para encerrar: pressione Ctrl+C\n')

    threading.Timer(1.0, lambda: webbrowser.open(f'http://localhost:{PORT}')).start()

    with ThreadedServer(('', PORT), Handler) as srv:
        try:
            srv.serve_forever()
        except KeyboardInterrupt:
            print('\n\n  Servidor encerrado.')

if __name__ == '__main__':
    main()
