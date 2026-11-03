# Instalação e ambiente — DirectChannel

## Tecnologias

- **Mobile:** React Native, Expo, TypeScript, React Navigation
- **Backend:** PHP 8.4, Laravel, Laravel Sanctum
- **Tempo real:** Laravel Reverb/WebSocket (planejado)
- **Persistência:** PostgreSQL 17
- **Ambiente:** Docker Compose para API e banco; Expo no Ubuntu/Linux

## Primeira instalação (Linux)

Requisitos: Docker com Compose V2, Node.js LTS e npm, internet.

```bash
cp infra/.env.example infra/.env
# Defina DB_PASSWORD em infra/.env (não versionar)
./infra/scripts/bootstrap.sh
# Gerar aplicativo e backend antes de iniciar os containers.
docker compose -f infra/compose.yaml up -d --build
docker compose -f infra/compose.yaml exec api php artisan key:generate
docker compose -f infra/compose.yaml exec api php artisan install:api --no-interaction
docker compose -f infra/compose.yaml exec api php artisan migrate
```

> Dica: `export COMPOSE_FILE=infra/compose.yaml` na raiz dispensa o `-f` nos comandos.

Criar rota `GET /api/status` em `backend/routes/api.php` após `install:api` (ou implementá-la via controller).

Ao gerar Laravel por `create-project`, verifique `backend/.env`: configure `DB_CONNECTION=pgsql`, `DB_HOST=postgres`, `DB_PORT=5432`, `DB_DATABASE=directchannel`, `DB_USERNAME=directchannel`. A senha vem do ambiente do container no Compose. Não faça commit de arquivos `.env`.

`bootstrap.sh` é para execução uma única vez por checkout. Após gerar os frameworks, faça commit do código gerado na branch de infraestrutura para que os colegas possam simplesmente clonar e instalar as dependências.

## Mobile

```bash
cd mobile
npm install
cp .env.example .env
# Substitua SEU_IP_LOCAL pelo IP do computador acessível pelo celular
npx expo start
```

Detalhes de execução e padrões de código em [`mobile/readme.md`](../mobile/readme.md).

## Segurança

- Senhas armazenadas com hash; tokens via Sanctum; checagem de acesso a cada conversa/arquivo.
- Endpoints de administração exigem autorização por papel/policy.
- Não incluir conteúdo das mensagens privadas em logs ou telas administrativas.
- Produção exige HTTPS/WSS, limites de upload, rate limit e backups.
