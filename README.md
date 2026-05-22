# Academy Control

Sistema inicial em PHP para controlar treinos de academia.

## Como acessar

1. Coloque o projeto dentro do `htdocs` do XAMPP.
2. Inicie o Apache no painel do XAMPP.
3. Acesse:

```text
http://localhost/Cursophp/Academycfor/
```

## Usuario de teste

```text
E-mail: aluno@academia.com
Senha: 123456
```

## Funcionalidades iniciais

- Login e logout de usuario.
- Cadastro e verificacao de usuarios pelo MySQL.
- Cadastro de treinos.
- Listagem de treinos.
- Selecao do treino ativo para o usuario logado.
- Usuarios salvos na tabela `academy_users` do banco MySQL.
- Treinos ainda salvos em arquivos JSON dentro da pasta `data`.

## Banco de dados

Por enquanto o sistema usa o banco `mysql` do XAMPP com uma tabela propria:

```text
academy_users
```

As configuracoes ficam em:

```text
app/config.php
```

O arquivo `database.sql` tambem tem o SQL da tabela e do usuario demo.

## Formato dos exercicios

No cadastro de treino, use uma linha por exercicio:

```text
Nome do exercicio;series;repeticoes
Agachamento livre;4;8-10
Leg press;3;12
```

## Proximos passos recomendados

- Criar cadastro de usuarios.
- Registrar cargas usadas em cada exercicio.
- Registrar historico de execucao do treino por data.
- Trocar os arquivos JSON por banco de dados MySQL.

## Publicacao

Veja o passo a passo em `DEPLOY.md`.
