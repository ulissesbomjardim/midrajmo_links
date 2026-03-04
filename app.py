import json
import importlib
import sys
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path


def get_project_root():
    """Detecta a raiz do projeto independente de onde app.py é chamado."""
    current = Path(__file__).resolve().parent
    
    # Se estamos em um diretório com .env ou .github, esse é a raiz
    if (current / ".env").exists() or (current / ".github").exists():
        return current
    
    # Se estamos em um subdiretório (ex: /home2/midrajmo/links/links/),
    # sobe até encontrar .env ou .github
    for parent in current.parents:
        if (parent / ".env").exists() or (parent / ".github").exists():
            return parent
    
    # Fallback: usa o diretório do script
    return current


def parse_env_file(env_path: Path) -> dict:
    config = {}

    if not env_path.exists():
        print(f"Aviso: arquivo .env não encontrado em {env_path}", file=sys.stderr)
        return config

    for raw_line in env_path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()

        if not line or line.startswith("#"):
            continue

        if ":" in line:
            key, value = line.split(":", 1)
        elif "=" in line:
            key, value = line.split("=", 1)
        else:
            continue

        config[key.strip()] = value.strip().strip('"').strip("'")

    return config


def test_mysql_connection(config: dict) -> dict:
    try:
        mysql_connector = importlib.import_module("mysql.connector")
    except ImportError:
        return {
            "ok": False,
            "message": "Pacote ausente: mysql-connector-python. Instale com: pip install mysql-connector-python",
        }

    required = ["hostname", "port", "username", "password", "database"]
    missing = [key for key in required if not config.get(key)]
    if missing:
        return {
            "ok": False,
            "message": "Campos faltando no .env: " + ", ".join(missing),
        }

    connection = None
    cursor = None

    try:
        connection = mysql_connector.connect(
            host=config["hostname"],
            port=int(config["port"]),
            user=config["username"],
            password=config["password"],
            database=config["database"],
            connection_timeout=5,
        )

        cursor = connection.cursor()
        cursor.execute("SELECT VERSION()")
        version_row = cursor.fetchone()

        return {
            "ok": True,
            "message": "Conexão com MySQL realizada com sucesso.",
            "server": version_row[0] if version_row else "desconhecido",
        }
    except Exception as exc:
        return {
            "ok": False,
            "message": str(exc),
        }
    finally:
        if cursor is not None:
            cursor.close()
        if connection is not None and connection.is_connected():
            connection.close()


class AppHandler(BaseHTTPRequestHandler):
    def log_message(self, format, *args):
        """Suprime logs padrão de requisição."""
        pass

    def do_OPTIONS(self):
        self.send_response(204)
        self.send_cors_headers()
        self.end_headers()

    def do_GET(self):
        # Normaliza a rota removendo prefixo `/links` se presente
        path = self.path.split('?')[0]  # Remove query string
        
        # Remove prefixo /links se estiver presente
        if path.startswith('/links/'):
            normalized_path = '/' + path[7:]  # Remove `/links`
        else:
            normalized_path = path
        
        if normalized_path in ["/", "/run_app.htm"]:
            self.serve_html()
            return

        if normalized_path == "/health":
            self.send_json({"ok": True, "message": "App rodando"}, 200)
            return

        if normalized_path == "/api/test-connection":
            self.serve_connection_test()
            return

        self.send_json({"ok": False, "message": "Rota não encontrada: " + normalized_path}, 404)

    def send_cors_headers(self):
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")

    def send_json(self, data, status_code=200):
        """Envia resposta JSON com CORS headers."""
        payload = json.dumps(data, ensure_ascii=False).encode("utf-8")
        self.send_response(status_code)
        self.send_cors_headers()
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(payload)))
        self.end_headers()
        self.wfile.write(payload)

    def serve_html(self):
        project_root = get_project_root()
        html_path = project_root / "run_app.htm"
        if not html_path.exists():
            body = b"Arquivo run_app.htm nao encontrado."
            self.send_response(404)
            self.send_cors_headers()
            self.send_header("Content-Type", "text/plain; charset=utf-8")
            self.send_header("Content-Length", str(len(body)))
            self.end_headers()
            self.wfile.write(body)
            return

        content = html_path.read_bytes()
        self.send_response(200)
        self.send_cors_headers()
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.send_header("Content-Length", str(len(content)))
        self.end_headers()
        self.wfile.write(content)

    def serve_connection_test(self):
        project_root = get_project_root()
        env_path = project_root / ".env"
        config = parse_env_file(env_path)
        result = test_mysql_connection(config)
        self.send_json(result, 200 if result.get("ok") else 500)


def main():
    project_root = get_project_root()
    print(f"Raiz do projeto: {project_root}")
    
    host = "0.0.0.0"  # Escuta em todas as interfaces, necessário para hospedagem compartilhada
    port = 8001
    server = HTTPServer((host, port), AppHandler)
    print(f"Servidor iniciado em http://{host}:{port}")
    print(f"HTML disponível em http://127.0.0.1:{port}/run_app.htm")
    
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nServidor parado.")
        sys.exit(0)


if __name__ == "__main__":
    main()
