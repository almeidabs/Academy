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

Opcoes comuns:

- Hospedagem compartilhada com cPanel
- Hostinger, Locaweb, KingHost, HostGator ou similar
- VPS com Apache/Nginx + PHP + MySQL

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

O arquivo `data/.htaccess` bloqueia acesso direto aos dados em hospedagens Apache/cPanel.

## 5. Enviar arquivos para a hospedagem

Em hospedagem cPanel:

1. Acesse o painel da hospedagem.
2. Entre em `Gerenciador de Arquivos`.
3. Abra a pasta `public_html`.
4. Envie os arquivos do projeto para dentro dela.
5. Garanta que a pasta `data` exista.
6. Ajuste a permissao da pasta `data` para permitir escrita pelo PHP.

Se a hospedagem tiver Git Deploy, use o repositório:

```text
git@github.com:almeidabs/Academy.git
```
