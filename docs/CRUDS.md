# Divisão dos 6 CRUDs — DirectChannel

> Planejamento para três desenvolvedores/computadores. Cada integrante realiza **dois CRUDs completos**, incluindo API, migration/model, validação, testes e telas/serviços mobile. Atribuir nomes no GitHub Projects.

| Computador | CRUD | Entidade principal | Escopo CRUD |
|---|---|---|---|
| 1 | Usuários | `users` | Cadastrar; consultar/listar/pesquisar; atualizar perfil; desativar conta (exclusão lógica) |
| 1 | Conversas | `conversations` e `conversation_participants` | Criar conversa; listar/consultar; atualizar atributos permitidos; arquivar/sair |
| 2 | Mensagens | `messages` | Enviar; listar/histórico; editar (preparar para versão futura); excluir logicamente (versão futura) |
| 2 | Grupos | `groups` e membros | Criar; listar/detalhar; alterar dados/membros; excluir/desativar grupo |
| 3 | Arquivos | `attachments` | Fazer upload; listar/detalhar metadados; atualizar metadados autorizados; remover anexo |
| 3 | Denúncias | `reports` | Criar denúncia; listar/consultar com permissão; atualizar status administrativo; arquivar/excluir logicamente |

## Importante sobre CRUD completo vs MVP
- O documento do projeto **não exige editar/excluir mensagens** no MVP; são funções futuras. Os métodos PUT/PATCH/DELETE desse CRUD podem ser preparados na estrutura, mas sua implementação deve ficar em issue de próxima fase, sem alterar o escopo silenciosamente.
- Para usuários e denúncias, prefira exclusão lógica por segurança/auditoria. A exclusão efetiva de dados exige política de retenção.
- Conversas privadas não devem permitir alteração de participantes sem regras explícitas. O CRUD deve aplicar policies.
- Um CRUD corresponde a 4 operações (Create, Read, Update, Delete), **não a quatro módulos separados**.
- Operações de autenticação e bloqueios são transversais aos CRUDs e continuam previstas como requisitos do DirectChannel.

## Contratos iniciais REST (proposta)

| CRUD | POST | GET | PATCH | DELETE |
|---|---|---|---|---|
| Usuários | `/api/users` (cadastro público via `/api/register`) | `/api/users`, `/api/users/{id}` | `/api/users/{id}` | `/api/users/{id}` |
| Conversas | `/api/conversations` | `/api/conversations`, `/api/conversations/{id}` | `/api/conversations/{id}` | `/api/conversations/{id}` |
| Mensagens | `/api/conversations/{id}/messages` | `/api/conversations/{id}/messages` | `/api/messages/{id}` (futuro) | `/api/messages/{id}` (futuro) |
| Grupos | `/api/groups` | `/api/groups`, `/api/groups/{id}` | `/api/groups/{id}` | `/api/groups/{id}` |
| Arquivos | `/api/attachments` | `/api/attachments/{id}` | `/api/attachments/{id}` | `/api/attachments/{id}` |
| Denúncias | `/api/reports` | `/api/reports`, `/api/reports/{id}` | `/api/reports/{id}` | `/api/reports/{id}` |

**Atenção:** são contratos planejados, não rotas já implementadas. Proteger recursos privados com `auth:sanctum`, `FormRequest` e `Policy`; validação MIME/tamanho e download autenticado para anexos. Evitar rotas administrativas expostas a usuários comuns.
