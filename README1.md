# DirectChannel — Aplicativo mobile de mensageria

Estrutura inicial para trabalho em equipe (3 computadores / 6 módulos CRUD). **Este repositório é um esqueleto de organização, não seis CRUDs já implementados nem uma aplicação pronta.**

## Tecnologias

- **Mobile:** React Native, Expo, TypeScript, React Navigation
- **Backend:** PHP 8.4, Laravel, Laravel Sanctum
- **Tempo real:** Laravel Reverb/WebSocket (planejado)
- **Persistência:** PostgreSQL 17
- **Ambiente:** Docker Compose para API e banco; Expo no Ubuntu/Linux

## Organização

```text
DirectChannel/
├── mobile/                   # Expo: src/components, screens, navigation, services, hooks
├── backend/                  # Laravel: app/Http, Models, Services, Policies, Events, database
├── infra/docker/             # notas/infra futura
├── docs/                     # arquitetura, contratos e colaboração
├── scripts/bootstrap.sh      # cria o Laravel e o Expo sem sobrescrever projetos existentes
├── compose.yaml
└── .env.example
```

> Git não versiona pastas vazias; `.gitkeep` garante que a estrutura proposta apareça no GitHub. Quando adicionarem os arquivos reais, podem remover os `.gitkeep` correspondentes.

## Primeira instalação (Linux)

Requisitos: Docker com Compose V2, Node.js LTS e npm, internet.

```bash
cp .env.example .env
# Defina DB_PASSWORD em .env (não versionar)
./scripts/bootstrap.sh
# Gerar aplicativo e backend antes de iniciar os containers.
docker compose up -d --build
docker compose exec api php artisan key:generate
docker compose exec api php artisan install:api --no-interaction
docker compose exec api php artisan migrate
```

Criar rota `GET /api/status` em `backend/routes/api.php` após `install:api` (ou implementá-la via controller). Para React Navigation:

```bash
cd mobile
npm install @react-navigation/native @react-navigation/native-stack @react-navigation/bottom-tabs
npx expo install react-native-screens react-native-safe-area-context
cp .env.example .env
# Substitua SEU_IP_LOCAL pelo IP do computador acessível pelo celular
npx expo start
```

Ao gerar Laravel por `create-project`, verifique `backend/.env`: configure `DB_CONNECTION=pgsql`, `DB_HOST=postgres`, `DB_PORT=5432`, `DB_DATABASE=directchannel`, `DB_USERNAME=directchannel`. A senha vem do ambiente do container no Compose. Não faça commit de arquivos `.env`.

**Observação:** `bootstrap.sh` é para execução uma única vez por checkout. Ele gera os frameworks reais quando você tiver internet; os arquivos base do Laravel e do Expo não estão incluídos no ZIP. Após gerar, faça commit do código gerado na branch de infraestrutura para que os colegas possam simplesmente clonar e instalar as dependências.

## Divisão da equipe

- Computador 1: CRUD **Usuários** + **Conversas**.
- Computador 2: CRUD **Mensagens** + **Grupos**.
- Computador 3: CRUD **Arquivos** + **Denúncias**.

Consulte [escopo e rotas de cada CRUD](docs/CRUDS.md), [arquitetura](docs/ARQUITETURA.md) e [instruções para GitHub](docs/GITHUB.md).

## Segurança

- Senhas armazenadas com hash; tokens via Sanctum; checagem de acesso a cada conversa/arquivo.
- Endpoints de administração exigem autorização por papel/policy.
- Não incluir conteúdo das mensagens privadas em logs ou telas administrativas.
- Produção exige HTTPS/WSS, limites de upload, rate limit e backups.
