# 📡 API REST - Sistema de Notícias

Esta é uma API RESTful desenvolvida com CodeIgniter para gerenciamento de usuários e notícias. A API permite autenticação, registro, CRUD de usuários e gerenciamento completo de publicações jornalísticas.

---

## 🚀 Endpoints Disponíveis

### 🔐 Autenticação

| Método | Endpoint             | Descrição                   |
|--------|----------------------|-----------------------------|
| POST   | `/api/auth/login`    | Login do usuário            |
| POST   | `/api/auth/register` | Registro de novo usuário    |
| POST   | `/api/auth/logout`   | Logout do usuário autenticado |
| GET    | `/api/auth/me`       | Dados do usuário autenticado |

---

### 👥 Usuários (Requer Autenticação)

| Método | Endpoint               | Descrição                         |
|--------|------------------------|-----------------------------------|
| GET    | `/api/users`           | Lista todos os usuários           |
| GET    | `/api/users/{id}`      | Retorna um usuário específico     |
| POST   | `/api/users`           | Cria um novo usuário              |
| PUT    | `/api/users/{id}`      | Atualiza dados de um usuário      |
| DELETE | `/api/users/{id}`      | Remove um usuário                 |

---

### 📰 Notícias

| Método | Endpoint                       | Descrição                          |
|--------|--------------------------------|------------------------------------|
| GET    | `/api/news`                    | Lista todas as notícias            |
| GET    | `/api/news/categories`         | Lista categorias disponíveis       |
| GET    | `/api/news/my`                 | Lista notícias do usuário logado   |
| GET    | `/api/news/{id}`               | Retorna uma notícia específica     |
| POST   | `/api/news`                    | Cria uma nova notícia              |
| PUT    | `/api/news/{id}`               | Atualiza uma notícia existente     |
| DELETE | `/api/news/{id}`               | Exclui uma notícia                 |

---

## 🧩 Estrutura do Banco de Dados

### 📍 Tabela `users`

| Campo       | Tipo           | Descrição                      |
|-------------|----------------|--------------------------------|
| id          | INT, PK        | Identificador do usuário       |
| name        | VARCHAR(100)   | Nome completo                  |
| email       | VARCHAR(100)   | E-mail (único)                 |
| password    | VARCHAR(255)   | Senha criptografada            |
| phone       | VARCHAR(20)    | Telefone                       |
| last_login  | DATETIME       | Último login                   |
| created_at  | DATETIME       | Data de criação do registro    |
| updated_at  | DATETIME       | Data de atualização            |

---

### 📰 Tabela `news`

| Campo             | Tipo                  | Descrição                          |
|------------------|-----------------------|------------------------------------|
| id               | INT, PK               | Identificador da notícia           |
| title            | VARCHAR(255)          | Título da notícia                  |
| slug             | VARCHAR(255), Único   | Slug para URL amigável             |
| summary          | TEXT                  | Resumo da notícia                  |
| content          | LONGTEXT              | Conteúdo completo                  |
| category         | VARCHAR(100)          | Categoria (ex: política, esporte)  |
| status           | ENUM                  | Status: `draft`, `published`, `archived` |
| author_id        | INT (FK)              | ID do autor (relacionado a `users`) |
| views            | INT                   | Visualizações                      |
| featured_image   | VARCHAR(500)          | Imagem de destaque (URL/caminho)   |
| meta_title       | VARCHAR(255)          | Meta title para SEO                |
| meta_description | TEXT                  | Meta description para SEO          |
| created_at       | DATETIME              | Data de criação                    |
| updated_at       | DATETIME              | Data de atualização                |

---

## 🔐 Autenticação

A API utiliza autenticação baseada em **tokens (Bearer Token)**. Após realizar o login, o token de autenticação deve ser incluído no cabeçalho das requisições protegidas:

Authorization: Bearer <seu_token>

---

## 🛠 Tecnologias Utilizadas

- PHP com CodeIgniter
- MySQL
- JSON
- Autenticação com token
- Docker (ddev)

---

## 📌 Observações

- Apenas usuários autenticados podem criar, atualizar ou excluir notícias e usuários.
- As rotas `news/my` e `auth/me` utilizam o token para identificar o autor/autenticado.
- O campo `status` das notícias deve conter um dos valores: `draft`, `published`, `archived`.

---

## SQL

```bash
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text,
  `content` longtext NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `author_id` int(11) NOT NULL,
  `views` int(11) DEFAULT 0,
  `featured_image` varchar(500) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
