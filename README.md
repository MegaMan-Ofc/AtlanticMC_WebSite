# Atlantic SMP Store

Website oficial da **Atlantic SMP** para apresentar e gerir os produtos da loja do servidor Minecraft.

O projeto inclui uma área pública para jogadores e um painel de administração para gestão do conteúdo da loja.

## Funcionalidades

- Suporte para jogadores Java e Bedrock
- Categorias e produtos dinâmicos
- Descontos por produto
- Produtos recomendados
- Organização das categorias da homepage pelo admin
- Pesquisa inteligente e filtros de produtos
- FAQ
- Páginas legais
- Carrinho de compras
- Painel de administração
- Modo de manutenção protegido
- Preparação para integração com Tebex

## Tecnologias

- PHP
- JavaScript
- HTML / CSS
- SQLite para desenvolvimento local
- MySQL para produção

## Arquitetura

```text
AtlanticStore/
├── app/
│   ├── Admin/
│   ├── Core/
│   ├── Integrations/
│   ├── Site/
│   ├── Store/
│   ├── bootstrap.php
│   └── modules.php
├── bin/
├── controllers/
│   ├── Admin/
│   ├── Site/
│   └── Store/
├── database/
├── public_html/
│   ├── actions/
│   ├── ajax/
│   ├── api/
│   ├── assets/
│   ├── css/
│   └── js/
├── storage/
├── templates/
│   ├── admin/
│   ├── components/
│   ├── errors/
│   ├── home/
│   ├── layout/
│   ├── site/
│   └── store/
├── tests/
│   ├── integration/
│   └── unit/
├── translations/
├── .env.example
├── compose.yaml
└── router.php
```

`app/` contém a lógica da aplicação organizada por domínio. `controllers/` prepara os dados das páginas. `templates/` contém apenas apresentação. `public_html/` mantém os entry points públicos e os assets servidos pelo servidor web.

O carregamento dos módulos internos é centralizado em `app/modules.php`, usado pelo bootstrap e pelos testes para evitar listas de dependências duplicadas.

## Instalação local

Cria o ficheiro `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Aplica as migrations:

```bash
php bin/migrate.php
```

Opcionalmente, cria o catálogo mock de desenvolvimento:

```bash
php bin/seed.php
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

As configurações privadas devem ficar no ficheiro `.env` e nunca devem ser publicadas no repositório.

O `.env.example` contém apenas valores de exemplo e os nomes das variáveis suportadas pela aplicação.

## Produção

Em produção, o document root deve apontar para:

```text
public_html/
```

A integração de pagamentos está preparada para **Tebex** e deve ser ativada apenas depois de configuradas as credenciais e os package IDs reais.

## Projeto

Desenvolvido para a **Atlantic SMP**.
