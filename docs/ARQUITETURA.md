# Arquitetura — DirectChannel

- Mobile: React Native + Expo + TypeScript; React Navigation (Stack e Tabs); services para HTTP/JSON e eventos.
- API: PHP + Laravel; Sanctum para tokens; controllers finos, Form Requests para validação, Policies para autorização.
- Persistência: PostgreSQL em Docker Compose; migrations versionadas no repositório.
- Mensageria: REST para envio/listagem; Laravel Reverb + WebSocket para eventos em tempo real (etapa posterior); considere filas/Redis conforme necessidade.
- Arquivos: armazenamento privado do Laravel no desenvolvimento; object storage futuro. Nunca servir arquivos privados sem checagem de participante/autorização.
- Administração: endpoints protegidos e interface condicionada a papel/permissão. Administradores não têm acesso irrestrito a mensagens privadas.
- O Expo roda no host Linux. Para celular físico, use o IP LAN no `EXPO_PUBLIC_API_URL`, não `localhost`.
- HTTP local serve apenas para desenvolvimento; produção exige HTTPS/WSS.

## Modelo de dados inicial (proposta)

`users`, `conversations`, `conversation_participants`, `messages`, `groups`, `group_members`, `attachments`, `reports`, `blocks`.

`groups` pode referenciar `conversations` (tipo `group`) para reutilizar pipeline de mensagens: evitar duas fontes independentes de mensagens. Definir convenção antes de criar migrations.

## Responsabilidade compartilhada

Autenticação, navegação, UI base, integração WebSocket, autorização, testes de integração e migrations que cruzam entidades devem ser revisadas por todos. Mantenham contratos em `docs/CRUDS.md` sincronizados.
