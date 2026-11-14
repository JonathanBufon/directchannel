# Contratos existentes e fronteiras da persistência

**Estado**: inventário de compatibilidade, não novo contrato de API. Nenhum endpoint, método, payload, código de resposta ou interface de usuário foi criado/alterado nesta etapa. Nomes internos do modelo não obrigam nomes públicos de atributos. Referências: `docs/CRUDS.md`, issues #13–#23 e código de rotas da revisão inspecionada.

## 1. Implementado × documentado

O código registra página web `GET /` e healthcheck `/up`; não há `backend/routes/api.php`, registro `api:` no bootstrap ou controllers dos seis CRUDs. A condição de erro JSON para `api/*` não implementa esses endpoints. Assim, a tabela abaixo preserva **contratos documentados/planejados**, não os apresenta como disponíveis.

| Contexto | Caminhos/métodos já documentados | Persistência que deve acomodá-los |
| --- | --- | --- |
| Autenticação (#13–#14) | `/api/register`, `/api/login`, `/api/logout`; `GET /api/me` | Conta, credenciais oficiais do mecanismo e contexto de autorização; não exigir campos físicos novos no cliente |
| Usuários (#15) | `GET /api/users`; `GET/PATCH/DELETE /api/users/{id}`; cadastro pela autenticação. `POST /api/users` também aparece na proposta de CRUDs vinculado ao cadastro público | Mesmo usuário; busca pública não expõe e-mail ou segredo; desativação lógica |
| Conversas (#16) | `POST/GET /api/conversations`; `GET/PATCH/DELETE /api/conversations/{id}` | Conversa e vínculos canônicos; sem interpretar DELETE como destruição global aprovada |
| Mensagens (#17) | `POST/GET /api/conversations/{id}/messages` | Histórico comum, autor/contexto coerentes; PATCH/DELETE de `/api/messages/{id}` são futuros, não MVP |
| Grupos (#18) | `POST/GET /api/groups`; `GET/PATCH/DELETE /api/groups/{id}` | Grupo e membros do mesmo contexto, sem exigir pipeline novo de mensagens |
| Arquivos (#19) | `POST /api/attachments`; `GET/PATCH/DELETE /api/attachments/{id}` | Registro de arquivo privado; vários anexos por mensagem é decisão local, não autorização para mudar requisição existente |
| Denúncias (#20) | `POST/GET /api/reports`; `GET/PATCH/DELETE /api/reports/{id}` | Relato, decisão e arquivamento lógico, sem consulta a mensagem/anexo como prova privada |
| Tempo real (#21) | Eventos de nova mensagem/entrega/leitura e autorização por participação; formato ainda não definido | Recibos/contexto precisam sustentar comportamento, sem definir canal/evento/payload ou adicionar serviço |
| Identidade pública (#22) | Publicação de material público e verificação de segurança; protocolo/contrato detalhado pendentes | Não impor formato de chave ou schema criptográfico por inferência |
| Administração (#23) | Papel/telas/rotas protegidas previstos, sem catálogo de rotas completas | Papel, bloqueios e decisões; dois parâmetros somente consultáveis por decisão do usuário |

Não inventar endpoint GET de coleção/download de anexos para preencher lacuna documental. Nem `POST /api/attachments` nem a existência de telas T07/T07G especifica a ordem de criação mensagem/upload, logo P-03 bloqueia o vínculo temporal/nulabilidade de anexo. A coexistência de dois caminhos de cadastro propostos é registrada, sem removê-los ou criar resolução por conta desta modelagem.

## 2. Limites de compatibilidade interna

- `users.id` numérico existente é preservado; `usr_1` do mock mobile não fundamenta mudança de chave ou formato de resposta. Se houver adaptação de representação no futuro, depende dos contratos do projeto, não de alteração nesta fase.
- O nome `username` é candidato físico compatível com o conceito local; armazenar sem distinção de caixa não exige mudar a grafia exibida, remover `@` ou introduzir campo normalizado obrigatório no payload.
- `sender_participant_id`, `access_grant_id`, timestamps e a ponte de tokens são mecanismos internos candidatos; não se acrescentam como campos obrigatórios de requisição externa. Identidade/autoria devem continuar obtidas do contexto autorizado do serviço, não de dados confiados ao cliente por escolha do banco.
- Mensagem com 0..N anexos não impõe seleção múltipla, upload em lote, novos endpoints, staging ou reutilização do mesmo registro por várias mensagens.
- Uma autorização vigente por conta não significa um token; renovar/emitir credenciais no mesmo contexto e trocar de contexto dependem de P-04. Nenhum identificador físico do aparelho é imposto ao cliente.
- O banco representa relações que as permissões existentes/planejadas devem verificar; não substitui Policies/Gates por concessão de papéis SQL nem dá acesso de operador ao conteúdo.
- Campos de pacote permanecem sob contrato do Sanctum a ser selecionado em #13; não gerar autenticação paralela ou schema incompatível com o framework.

## 3. Dados que não atravessam a fronteira por inferência

Chaves privadas nunca são enviadas para SQL/servidor. Credencial original continua no cofre local; servidor guarda representação protegida do mecanismo, sem log de segredo. URI local de avatar não se torna URL pública. Coordenadas temporárias, preferências sem sincronização e avisos locais não viram tabelas remotas. Conteúdo privado não vai a denúncia, log de erro ou índice de pesquisa em claro. A fonte atual de configuração permanece onde a aplicação a definir, sem migração para tabela por conta de consulta administrativa.

## 4. Critérios de revisão

Para cada tabela/campo candidato, verificar: há necessidade em DB-001–DB-020? Pode ser mantido interno sem mudar contrato? Há exposição indevida? Algum valor de cliente é indevidamente usado como prova de autoria/participação? Se houver incompatibilidade, parar a proposta afetada e obter decisão; não alterar API, mobile, backend ou infraestrutura para fazer o modelo caber.
