# Publicar o projeto

## 1. Subir no GitHub

Instale o Git ou use o GitHub Desktop.

Com Git pelo terminal:

```bash
git init
git add .
git commit -m "Primeira versao do sistema de academia"
git branch -M main
git remote add origin https://github.com/SEU-USUARIO/NOME-DO-REPOSITORIO.git
git push -u origin main
```

## 2. Hospedagem

Este projeto usa PHP, entao nao funciona no GitHub Pages.

Use uma hospedagem com:

- PHP 8+
- MySQL
- permissao de escrita na pasta `data`

## 3. Configurar banco

No servidor, edite:

```text
app/config.php
```

Preencha com os dados do banco da hospedagem:

```php
const DB_HOST = 'host_do_servidor';
const DB_NAME = 'nome_do_banco';
const DB_USER = 'usuario_do_banco';
const DB_PASS = 'senha_do_banco';
```

## 4. Pasta data

O sistema salva treinos, progresso, peso e corridas em arquivos JSON dentro de `data`.

No servidor, garanta que essa pasta exista e tenha permissao de escrita.
