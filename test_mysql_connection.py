import sys
from pathlib import Path


def parse_env_file(env_path: Path) -> dict:
    data = {}

    if not env_path.exists():
        raise FileNotFoundError(f"Arquivo não encontrado: {env_path}")

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

        key = key.strip()
        value = value.strip().strip('"').strip("'")
        data[key] = value

    return data


def test_mysql_connection(config: dict) -> int:
    try:
        import mysql.connector
    except ImportError:
        print("Dependência ausente: mysql-connector-python")
        print("Instale com: pip install mysql-connector-python")
        return 2

    required_keys = ["hostname", "port", "username", "password", "database"]
    missing = [k for k in required_keys if not config.get(k)]
    if missing:
        print(f"Configuração incompleta no .env. Campos faltando: {', '.join(missing)}")
        return 3

    try:
        connection = mysql.connector.connect(
            host=config["hostname"],
            port=int(config["port"]),
            user=config["username"],
            password=config["password"],
            database=config["database"],
            connection_timeout=5,
        )

        if connection.is_connected():
            cursor = connection.cursor()
            cursor.execute("SELECT VERSION()")
            version = cursor.fetchone()
            print("✅ Conexão MySQL OK")
            print(f"Servidor MySQL: {version[0] if version else 'desconhecido'}")
            cursor.close()
            connection.close()
            return 0

        print("❌ Não foi possível confirmar a conexão.")
        return 1

    except Exception as exc:
        print("❌ Falha na conexão MySQL")
        print(f"Erro: {exc}")
        return 1


def main() -> int:
    env_path = Path(__file__).with_name(".env")
    config = parse_env_file(env_path)
    return test_mysql_connection(config)


if __name__ == "__main__":
    raise SystemExit(main())
