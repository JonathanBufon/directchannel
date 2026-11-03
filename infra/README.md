# Infra

Ambiente de desenvolvimento do DirectChannel.

| Arquivo | Função |
|---|---|
| `compose.yaml` | API Laravel (`../backend`) + PostgreSQL 17 |
| `.env.example` | Variáveis do Compose — copie para `infra/.env` (não versionado) |
| `scripts/bootstrap.sh` | Gera Laravel e Expo sem sobrescrever projetos existentes |

```bash
cp infra/.env.example infra/.env        # defina DB_PASSWORD
docker compose -f infra/compose.yaml up -d --build
```

Ou, na raiz, simplesmente `make dev`. Passo a passo completo em [`docs/SETUP.md`](../docs/SETUP.md).
