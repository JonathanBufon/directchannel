# Colaboração no GitHub — 3 computadores

## Primeiro envio (responsável pelo repositório)

1. Criar repositório **vazio** no GitHub, por exemplo `DirectChannel` (sem README inicial).
2. No Linux, na raiz deste projeto:

```bash
git init -b main
git add .
git commit -m "chore: estrutura inicial DirectChannel"
git remote add origin https://github.com/ORGANIZACAO/DirectChannel.git
git push -u origin main
```

## Outros integrantes

```bash
git clone https://github.com/ORGANIZACAO/DirectChannel.git
cd DirectChannel
cp .env.example .env
./scripts/bootstrap.sh
# configure o IP da API em mobile/.env, as senhas e instale dependências
```

## Estratégia de branches

- `feat/users-crud` e `feat/conversations-crud` (computador 1)
- `feat/messages-crud` e `feat/groups-crud` (computador 2)
- `feat/attachments-crud` e `feat/reports-crud` (computador 3)

Abrir Pull Request para `main` por funcionalidade; exigir revisão e testes antes de mesclar. Não trabalhar diretamente na `main`. Se a estrutura do Expo/Laravel for gerada após o primeiro commit, um responsável deve versionar os arquivos gerados (exceto `.env`, `vendor`, `node_modules`) em PR de infraestrutura antes de iniciar os CRUDs.

Exemplo:

```bash
git checkout main
git pull --ff-only origin main
git switch -c feat/users-crud
# alterar arquivos
git add .
git commit -m "feat(users): iniciar CRUD de usuarios"
git push -u origin feat/users-crud
```

## Convenções

- `feat`, `fix`, `docs`, `test`, `chore` nos commits.
- Uma migration para cada mudança de esquema; nunca editar migration já aplicada por colegas sem coordenação.
- Não compartilhar senhas ou tokens no Git; `.env` fica local.
- Abrir Issues por requisito e associar os PRs.
