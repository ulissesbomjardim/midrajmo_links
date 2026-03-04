# Gerenciador de Links Favoritos (PHP 7.2)

Projeto em PHP nativo 7.2 com estrutura organizada e frontend moderno:
- cadastro de categorias;
- cadastro de tags;
- cadastro de links com categoria e múltiplas tags;
- listagem de links em cards com animação;
- sidebar recolhível.

## Estrutura

- `app/Config`: leitura do `.env` e configuração
- `app/Core`: conexão PDO e resposta JSON
- `app/Controllers`: controlador da API
- `app/Repositories`: acesso a dados
- `database/schema.sql`: schema do MySQL
- `public/index.php`: frontend
- `public/api.php`: endpoints
- `public/assets`: CSS e JavaScript

## Requisitos

- PHP 7.2+
- Extensão PDO MySQL habilitada
- MySQL/MariaDB

## Banco de dados

1. Crie o banco `midrajmo_links` (ou ajuste no `.env`).
2. Execute o schema:

```sql
SOURCE database/schema.sql;
```

## Configuração `.env`

O projeto aceita tanto `chave:valor` quanto `chave=valor`.

Exemplo:

```dotenv
hostname:midrajmob.com
port:3306
username:"midrajmo_li"
password:"sua_senha"
database:"midrajmo_links"
```

## Produção (Superdomínios)

- O arquivo `app/Config/config.php` está no `.gitignore` para não versionar credenciais.
- Em produção, crie `app/Config/config.php` no servidor (ou use um caminho externo com `APP_PRIVATE_CONFIG`).
- O carregamento segue esta ordem:
	1. caminho em `APP_PRIVATE_CONFIG` (recomendado para arquivo fora de `public_html`)
	2. `app/Config/config.php`
	3. `app/Config/config.default.php` (fallback local via `.env`)

Exemplo de `app/Config/config.php` para produção:

```php
<?php

return [
		'db' => [
				'host' => 'SEU_HOST_MYSQL',
				'port' => '3306',
				'name' => 'SEU_BANCO',
				'user' => 'SEU_USUARIO',
				'pass' => 'SUA_SENHA',
				'charset' => 'utf8mb4',
		],
];
```

## Executar localmente

Na raiz do projeto:

```bash
php -S localhost:8000 -t public
```

Abra no navegador:

- http://localhost:8000

A API fica em:

- http://localhost:8000/api.php?action=dashboard_data

## Teste de Conexão MySQL (app.py)

Para testar a conexão com MySQL antes de usar o aplicativo:

```bash
python3 app.py
```

Abra no navegador:

- http://127.0.0.1:8001/run_app.htm

Clique em **Executar teste** para validar a conexão usando credenciais do `.env`.

Requer: `pip install mysql-connector-python`

## No Superdomínios

1. **Suba os arquivos** (via SFTP ou painel) para `/home2/midrajmo/links`.
2. **Crie o arquivo `.env`** no servidor com suas credenciais reais:
   ```
   hostname:seu_host_mysql
   port:3306
   username:seu_usuario
   password:sua_senha
   database:midrajmo_links
   ```
3. **Execute o schema** no MySQLman ou via CLI:
   ```sql
   SOURCE database/schema.sql;
   ```
4. **Teste a conexão** (opcional):
   ```bash
   source /home2/midrajmo/virtualenv/links/3.11/bin/activate
   cd /home2/midrajmo/links
   python3 app.py
   # Abra http://seu_dominio:8001/run_app.htm
   ```
5. **Acesse o aplicativo** via browser no domínio configurado.
