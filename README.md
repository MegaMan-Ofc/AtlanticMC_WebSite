# Atlantic SMP Store

Website oficial da **Atlantic SMP**, desenvolvido para apresentar e gerir os produtos da loja do servidor Minecraft.

O projeto inclui uma área pública para jogadores e um painel de administração para gestão do conteúdo da loja.

## Funcionalidades

- Suporte para jogadores Java e Bedrock
- Categorias e produtos dinâmicos
- Descontos por produto
- Produtos recomendados
- Organização das categorias da homepage pelo admin
- FAQ
- Páginas legais
- Carrinho de compras
- Painel de administração
- Preparação para integração com Tebex

## Tecnologias

- PHP
- JavaScript
- HTML / CSS
- SQLite para desenvolvimento local
- MySQL para produção

## Estrutura principal

```text
AtlanticStore/
├── bin/
├── controllers/
├── database/
├── includes/
├── public_html/
├── storage/
├── templates/
├── translations/
├── tests/
├── .env.example
├── compose.yaml
└── router.php
```

## Instalação local

Cria o ficheiro `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Aplica as migrations:

```bash
php bin/migrate.php
```

Inicia o servidor local:

```bash
php -S localhost:8000 -t public_html router.php
```

Depois abre:

```text
http://localhost:8000
```

## Testes

```bash
php tests/run.php
php bin/quality.php
```

## Configuração

As configurações privadas devem ficar no ficheiro `.env`.

Nunca devem ser publicados valores reais como:

- `APP_KEY`
- passwords da base de dados
- credenciais de administrador
- secrets do Tebex

O ficheiro `.env.example` contém apenas a estrutura necessária para configurar o projeto.

## Produção

Em produção, o conteúdo público deve ser servido através da pasta:

```text
public_html/
```

A integração de pagamentos está preparada para **Tebex** e deve ser ativada apenas depois de configuradas as credenciais e os package IDs reais.

## Projeto

Desenvolvido para a **Atlantic SMP**.
