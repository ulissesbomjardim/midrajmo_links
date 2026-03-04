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
