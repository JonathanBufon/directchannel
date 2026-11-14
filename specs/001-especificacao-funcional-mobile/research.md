# Pesquisa e registro de decisões — modelagem SQL

**Data**: 2026-11-12 · **Estado**: decisões técnicas propostas; fechamento físico bloqueado. Fontes de negócio: [spec.md](spec.md), [evidencias.md](evidencias.md), README, documentos e issues de `JonathanBufon/directchannel`.

## 1. Método e conclusões de origem

Inspeção estática e nova coleta somente de leitura com `gh issue list --state all --limit 1000 --json number,title,state,stateReason,body,comments,url`, seguida de `gh api --paginate` para issues `state=all&per_page=100` e comentários `per_page=100`. Conferidos 24 issues, 12 abertas, 12 fechadas COMPLETED e 22 comentários. Não há truncamento por limite de 1.000 ou comentários nesta coleta. #1–#12 documentam conclusão UX/UI, não implementação; #13–#24 continuam abertas e sem comentários decisórios novos. As cinco decisões locais do usuário prevalecem para esta proposta sem modificar o GitHub.

Dois agentes de pesquisa foram solicitados pelo workflow: um examinou integridade/concorrência e fontes oficiais PostgreSQL 17; outro inventariou base e contratos. Ambos somente leitura. Nenhum SQL, migration, instalação ou operação de Docker foi realizada. Não se consultaram dados, segredos, catálogo ou overrides de ambiente em execução.

Nenhuma tabela adicional foi encontrada além das oito declaradas. `sessions.user_id` é coluna indexada, não FK; JSON para `api/*` no bootstrap não cria API; Sanctum não consta de `composer.json`/lock. O lock registra Laravel `v13.40.0`; o Dockerfile usa PHP 8.4, embora a exigência do projeto seja `^8.3`. Isso não autoriza atualizar versões.

## 2. Decisões técnicas candidatas

### R-01 — Base e mecanismo de evolução

- **Decision**: PostgreSQL 17 existente, mesma conexão/banco/volume; evolução futura por novas migrations Laravel em `backend/database/migrations/`, sem editar as três já versionadas.
- **Rationale**: Compose e código já fixam responsabilidades e mecanismo. O fallback SQLite/configurações de Redis/S3 são genéricos, não serviços a adotar.
- **Alternatives considered**: SQL paralelo em `infra/`, outro Compose, outro banco, Redis e object storage — rejeitados por escopo. Não executar Makefile `dev`, `fresh`, Composer `setup` ou scripts de criação, que podem migrar/apagar dados.

### R-02 — Grupo estende conversa, sem duplicação de mensagens

- **Decision**: propor `groups.conversation_id` obrigatório/único; mensagens exclusivamente em `messages`, de uma conversa. Uma conversa de grupo tem um grupo, conversa individual não. A relação de extensão 1:1 é candidata para aprovação, seguindo a opção já descrita em `docs/ARQUITETURA.md`.
- **Rationale**: evita duas fontes de mensagens; não altera endpoints de grupos ou conversas, nem cria novos serviços. Nenhuma estrutura existente implementa essas relações hoje.
- **Alternatives considered**: mensagens separadas por grupo — rejeitadas por duplicação; eliminar conceito/tabela de grupo — inadequado ao domínio documentado. Não converter essa convenção técnica em regra aprovada sem revisão.

### R-03 — Uma fonte canônica de participação temporal

- **Decision**: `conversation_participants` guarda ocorrências com entrada/saída; `group_members` referencia a ocorrência e identifica responsabilidade atual no grupo, sem duplicar usuário ou período. Reentrada gera outra ocorrência. Propor períodos semiabertos `[joined_at, left_at)` e horário autoritativo do serviço, sujeitos à revisão.
- **Rationale**: sustenta RF15–RF18 e não troca o passado por um booleano. Índice único parcial impede duas ocorrências abertas do mesmo par, mas não prova ausência de sobreposição de períodos fechados.
- **Alternatives considered**: UNIQUE global usuário/contexto — impede reentrada; duas listas mutáveis com datas independentes — arriscam divergência; `EXCLUDE` com `btree_gist` — alternativa não escolhida, pois exigiria extensão não existente. Sobreposição será invariante de transação futura, sem extensão nova.

### R-04 — Integridade de autoria, temporalidade e concorrência

- **Decision**: mensagem referencia a ocorrência de participação que enviou. Guardar também `conversation_id` para histórico indexável e usar FK composta `(sender_participant_id, conversation_id)` para `(id, conversation_id)` da participação. Autor é derivado dessa participação; não duplicar `sender_user_id`.
- **Rationale**: FK comprova o contexto da autoria, mas instante válido, membro ativo e proteção criptográfica exigem validação futura. A redundância controlada de conversa é a única exceção nova explícita à 3FN; ver modelo lógico.
- **Alternatives considered**: derivar conversa por join — opção estritamente normalizada, mantida para revisão; apenas FKs independentes para conta e conversa — não provam autoria por participação; `CHECK` consultando outras tabelas — não é garantia suportada de integridade contínua. Não criar triggers/código nesta entrega.

### R-05 — Responsabilidade e transferência

- **Decision**: responsabilidade atual em `group_members.is_responsible`, vinculada a participação do mesmo grupo; sem UNIQUE que limite a um responsável. Transferência e saída formam uma operação consistente futura serializada pelo contexto do grupo.
- **Correção documental da revisão**: o DER passa a representar `is_responsible` dentro de `MEMBRO_GRUPO`; foi removida a entidade separada `RESPONSABILIDADE`. Não há nova tabela, histórico de cargos ou alteração da regra D1.
- **Rationale**: usuário aprovou transferência do último responsável e, na nova rodada, vários responsáveis simultâneos sem máximo específico, preservando pelo menos um ativo no grupo ativo. Se restar outro responsável ativo, não é necessária transferência. Histórico de funções não foi exigido; sucessor do último precisa estar ativo e ser escolhido/confirmado.
- **Alternatives considered**: papel global em `users` — errado; sucessor automático — não aprovado; tabela de histórico de cargos — finalidade não confirmada. Trava somente no líder não impede remoção concorrente do sucessor; todos os caminhos de mudança de membro devem usar o mesmo ponto de serialização.

### R-06 — Um dispositivo ativo não significa um token

- **Decision**: propor `access_grants` como autorizações de contexto lógico, no máximo uma não revogada por conta. `access_grant_tokens` associa várias credenciais Sanctum à mesma autorização. Não adicionar `devices`, IMEI, endereço de rede ou fingerprint. A tabela padrão de tokens será mantida compatível com a versão futura do Sanctum, não inventada como dependência instalada.
- **Rationale**: respeita a decisão D2 e distingue sessão web de credencial de API. Histórico 0..N é possível; retenção permanece pendente. Revogar autorização requer rejeitar/inutilizar suas credenciais no fluxo de autenticação futuro; o índice por si não faz isso.
- **Alternatives considered**: UNIQUE token por usuário — rejeitado por limitar credenciais indevidamente; inventário de aparelhos — não requerido; ponteiro de autorização em `users` além de índice parcial — redundância desnecessária. A associação e sua política precisam de revisão com #13 antes de implementação.

### R-07 — Identificador sem distinção de caixa

- **Decision**: candidato é índice único global de expressão `lower(username)` preservando grafia, sem filtro por `deleted_at` e sem extensão. Escopo incluindo contas desativadas aprovado localmente na nova rodada, opção A. Caracteres internacionais, collation, alteração e backfill continuam P-01; não emitir índice final antes desse fechamento.
- **Rationale**: atende `@Maria`/`@maria` e à reserva do identificador após desativação. `lower` depende de collation e não é case folding Unicode universal. Índice parcial apenas para contas ativas permitiria reutilização e contraria a resposta aprovada. O índice global cobre a reserva enquanto a linha existir; renomeação/nome anterior e purga física ainda exigem políticas, sem inferir retenção eterna da conta.
- **Alternatives considered**: `citext` exige extensão e não resolve sozinho todos os casos; collation ICU não determinística pode equiparar mais do que aprovado e restringe pattern matching no PostgreSQL 17; retirar `@`, acentos, trim ou impor ASCII não foi autorizado. E-mail mantém sua unicidade atual.

### R-08 — Anexos em 1:N, arquivo fora do SQL

- **Decision**: relação final `messages` 1:N `attachments`, sem `conversation_id` duplicado no anexo; autorização decorre de mensagem/conversa. Binário cifrado permanece em armazenamento privado previsto; SQL guarda referência opaca e tamanho. A versão final tem `message_id NOT NULL`, mas nulabilidade/estado durante upload não serão fechados antes de P-03.
- **Rationale**: o usuário aprovou vários anexos por mensagem e, na nova rodada, upload privado antes da mensagem, com associação final ao confirmar envio e indisponibilidade aos destinatários durante a fase temporária. Isso não aprova upload em lote, mensagem só com anexo, representação física de staging ou exclusão automática. Descritor criptográfico e metadados sensíveis dependem de #22.
- **Alternatives considered**: anexo compartilhado N:N — contraria a decisão; binário no PostgreSQL/S3 novo — não requerido; `conversation_id` repetido no anexo — dependência transitiva evitável; tornar FK opcional definitivamente — não aprovado.

### R-09 — Recibos, administração e minimização

- **Decision**: recibos por ocorrência destinatária; bloqueio direcionado separado de bloqueio administrativo; denúncias sem FK para mensagem/anexo. Na nova rodada, usuário aprovou uma única decisão final por denúncia no MVP, na própria denúncia, com autoria/instante, sem reabertura ou substituição após conclusão; arquivamento preserva esses dados. Não há histórico de revisões exigido. Parâmetros administrativos são somente consulta e não geram tabelas de configuração.
- **Rationale**: dados de moderação são legíveis pelo administrador autorizado, mas não dão acesso ao conteúdo privado. Estados/transições/permissões da denúncia e partes do ciclo de bloqueios continuam P-05/P-06; cardinalidade da decisão final está resolvida. Não criar estado de leitura privada coletado com preferência desativada.
- **Alternatives considered**: JSON de destinatários/lista de membros — não normalizado; log genérico com payload privado — rejeitado; auditoria de edição de parâmetros — fora do escopo por decisão do usuário. Narrativa de denúncia é texto voluntário; não armazenar cópias automáticas de conversa ou anexos nela.

### R-10 — Tipos, exclusão e preservação

- **Decision**: PKs/FKs de domínio `bigint`, novas PKs com identidade PostgreSQL em documentação; novos instantes `timestamptz(6)` autoritativos, sem converter timestamps existentes. Referências novas com `ON DELETE RESTRICT`, `ON UPDATE RESTRICT` como candidato não destrutivo. Exclusão lógica de usuários/denúncias, sem purga automática.
- **Rationale**: preserva `users.id` e autoria. RESTRICT não substitui política de descarte: limita exclusão física enquanto ela está pendente. Novas datas com fuso não mudam retrospectivamente os tipos do framework; precisa haver conversão explícita na futura integração autorizada.
- **Alternatives considered**: UUID imposto à conta pelo mock `usr_1` — não fundamentado; CASCADE e `SET NULL` indiscriminados — perderiam contexto; ampliar enums e defaults de negócio desconhecidos — não permitido. Tipos e defaults técnicos propostos não alteram formato público de APIs.

## 3. Bloqueios não resolvíveis por pesquisa

| ID | Decisão pendente | Efeito no design e critério de desbloqueio |
| --- | --- | --- |
| P-01 | Parcialmente resolvido: identificador reservado após desativação; faltam alteração, comparação internacional/collation e backfill | Aprovado A na nova rodada: unicidade inclui contas desativadas, sem filtro de contas ativas. Fechar demais regras sem alterar e-mail; compatibilizar eventual purga com P-08 |
| P-02 | Parcialmente resolvido: vários responsáveis sem máximo específico, com pelo menos um ativo; faltam ciclo final, gestão de responsabilidade fora da saída e acesso após reentrada | Aprovado B: não impor UQ de responsável por grupo; transferir na saída somente do último. Aprovar demais regras sem presumir liberação de histórico ou descarte |
| P-03 | Parcialmente resolvido: upload privado antes da mensagem e associação ao confirmar envio; faltam representação temporária, limpeza, falhas, limites e remoção | Aprovado A na nova rodada: não disponibilizar arquivo aos destinatários antes da associação. Ainda não fecha nulabilidade/ciclo físico de `attachments`; preservar contratos sem presumir novos endpoints |
| P-04 | Autorizar/identificar troca, renovação no mesmo contexto e versão Sanctum | Valida vínculo grant/token e schema de pacote; comprovar integração de revogação sem token único por conta |
| P-05 | Parcialmente resolvido: bloqueio administrativo impede novos logins sem revogar acesso anterior; faltam arquivamento/saída individual e efeitos da desativação lógica | Decisão local B confirmada nesta nova rodada: contexto/credenciais anteriores continuam até logout ou expiração. Não adicionar revogação automática. Fechar campos/visibilidade por participante e desativação antes das migrations; renovação/reemissão durante bloqueio é P-04 |
| P-06 | Parcialmente resolvido: uma decisão final, sem reabertura/substituição no MVP; faltam estados/transições e permissões | Aprovado A: decisão final com administrador/instante em `reports`, preservados no arquivamento. Não criar relação de revisões; fechar estados e validações por transição antes do CHECK/default final |
| P-07 | Protocolo/biblioteca E2EE e material público/versionamento | Envelope opaco é posição conceitual, não schema criptográfico concluído; aprovar #22 antes de fixar chaves, tamanhos e integridade |
| P-08 | Finalidade/acesso/retenção por campo, volumetria, descarte definitivo | Bloqueia política de purga, reserva de armazenamento e certificação de inventário; 25 MB/30 dias não são aprovados |
| P-09 | Metadados editáveis/permissões, mensagens exclusivamente com anexo e relógio/ordenação de eventos | Fechar validações, nulabilidade de corpo e fronteira de leitura; não inferir do contrato somente pelo verbo HTTP |

**Revalidação para nova rodada de esclarecimentos — 2026-11-12**: executado uma vez o verificador de caminhos do Spec Kit; mantida a especificação existente. `.specify/extensions.yml` ausente na verificação prévia; constituição ainda é template sem princípios ratificados. Nova consulta somente leitura de 24 issues, incluindo corpos e comentários, confirmou #13–#24 abertas e sem comentários; não foi encontrada aprovação nova das políticas abaixo. Os agentes mencionados na seção 1 pertencem ao levantamento anterior; esta revalidação não delegou trabalho nem executou aplicação, SQL ou Docker. As cinco respostas anteriores continuam válidas e não consomem a quota de até cinco perguntas desta nova rodada.

| Ponto | Fontes reconferidas e decisão já fundamentada | O que continua sem decisão / implementação |
| --- | --- | --- |
| P-01 | #15; `User.php`, migration de usuários; `LoginScreen.tsx` e `storage.ts`: identidade real preservada e comparação sem distinção de caixa aprovada. Nova resposta A: não reutilizar identificador de conta desativada | Mock não define backfill/comparação internacional. Não há coluna SQL atual de username; alteração/destino de nomes anteriores, comparação internacional e atribuição às contas existentes continuam abertos; purga depende de P-08 |
| P-02 | #5/#18/#24; `CRUDS.md`, `REQUISITOS-AJUSTES.md`: permissão/saída fundamentadas. Nova resposta B: vários responsáveis sem máximo específico, pelo menos um ativo | Nenhuma implementação de grupos; ciclo final, gestão de responsabilidade fora da saída e alcance do histórico em reentrada ainda não decididos |
| P-03 | #19; `CRUDS.md`; `config/filesystems.php`: storage privado e download autorizado previstos, cardinalidade 1:N aprovada localmente. Nova resposta A: upload privado antes da mensagem e associação ao confirmar envio | Configuração de storage não implementa upload/autorização; não há fluxo de anexos no código atual. Representação temporária no banco, limpeza, falhas parciais, limites e remoção continuam pendentes |
| P-04 | #13/#14/#24; `ARQUITETURA.md`, `REQUISITOS-AJUSTES.md`; `composer.json`/lock e login mobile: Sanctum é o mecanismo planejado, política de um dispositivo aprovada | Sanctum não declarado nas dependências; token mobile é simulado. Renovação, autorização da troca e versão do pacote não foram resolvidas. Suporte genérico de encryption do Laravel não é E2EE do produto |
| P-05 | #16/#23; `CRUDS.md`, `FLUXO-TELAS.md`: conversa privada preserva participantes, administração prevê bloqueio. Nova resposta local B: impedir somente novos logins, sem interromper credenciais/contexto existentes até logout ou expiração | Não há implementação. Efeito do bloqueio sobre acesso existente foi esclarecido pelo usuário, não pelas issues; arquivamento/saída e efeitos da desativação continuam pendentes. Renovação/reemissão durante bloqueio permanece P-04 |
| P-06 | #20/#23; `CRUDS.md`, `FLUXO-TELAS.md`: status administrativo, análise/arquivamento sem conteúdo privado previstos. Nova resposta A: uma decisão final com autoria/instante, sem reabertura/substituição | Nenhuma implementação de moderação; fontes não definem estados/transições/permissões. Cardinalidade e preservação da decisão final foram aprovadas localmente, não pelas issues |
| P-07 | #22; `ARQUITETURA.md`, `REQUISITOS-AJUSTES.md`; `mobile/package.json`, serviços e lock backend: chaves privadas locais e conteúdo remoto cifrado são requisitos | Nenhuma decisão de protocolo/biblioteca ou implementação E2EE localizada. SecureStore atualmente guarda token; aviso de identidade ou dependência genérica não prova chaves/protocolo. Esquema criptográfico permanece bloqueado |
| P-08 | #2/#19/#24; `REQUISITOS-AJUSTES.md`: histórico e logs têm finalidades distintas; inventário mínimo requerido | 25 MB/30 dias continuam propostas. Expiração de cache/sessão do framework não decide retenção de mensagens/anexos/moderação; volumetria, descarte e prazos por classe continuam abertos |
| P-09 | #17/#19/#21/#22; `CRUDS.md`, `storage.ts`: edição/exclusão de mensagens fora do MVP e preferências locais preservadas | Não há pipeline de mensagens/recibos/anexos implementado. Metadados editáveis, mensagem só com anexo, relógio, idempotência e reconciliação de eventos continuam abertos |

Nenhum P-01–P-09 foi encerrado integralmente pela pesquisa desta rodada. Cada resposta aceita será registrada em `spec.md` e refletida nos modelos somente no alcance aprovado; um ponto com várias subdecisões permanecerá parcialmente pendente até fechar todas. Não transformar recomendação ou opção apresentada em aprovação. Os modelos lógico/físico existentes continuam candidatos durante a entrevista.

**Resposta 1 da nova rodada — P-05 parcialmente resolvido**: após desambiguar uma mensagem contraditória, o usuário confirmou **B da confirmação**: impedir apenas novos logins, manter contexto e credenciais já emitidos até logout ou expiração. Portanto, o bloqueio não grava `access_grants.revoked_at` nem invalida tokens por si. Consultar `account_blocks` vigente na autorização de novo login e coordenar bloqueio/login concorrentes. Não alterar permissões de operações com credenciais anteriores apenas pelo bloqueio. Prazo/renovação de credenciais, arquivamento e desativação não foram aprovados por essa resposta. Nenhuma issue foi modificada ou tabela acrescentada.

**Resposta 2 da nova rodada — P-03 parcialmente resolvido**: usuário aprovou **A**: upload privado antes da criação da mensagem, temporariamente indisponível aos destinatários, com associação final ao confirmar envio. A alternativa de criar a mensagem antes do upload não é a escolhida. Persistir temporários em SQL ou inserir `attachments` somente na associação final continua decisão técnica aberta, assim como autorização da fase temporária, limpeza/prazos, limites, idempotência, remoção e falhas parciais. A FK final NN e a cardinalidade 1:N permanecem; não criar tabela/estado/endpoint por inferência. Implementação continua não autorizada.

**Resposta 3 da nova rodada — P-01 parcialmente resolvido**: usuário aprovou **A**: manter o identificador público reservado à conta desativada, incluindo variantes apenas de caixa; outra conta não pode cadastrá-lo. O índice candidato passa a ter escopo global, não parcial por `deleted_at`. Não exige relação adicional enquanto a linha da conta for mantida. Não aprova política de renomeação, reserva de nomes anteriores, comparação Unicode, backfill ou purga; eventual descarte não pode liberar nomes por acidente. Implementação e DDL permanecem não autorizados.

**Resposta 4 da nova rodada — P-06 parcialmente resolvido**: usuário aprovou **A**: no MVP, denúncia concluída tem uma única decisão final com administrador e instante na própria `reports`; não reabrir nem substituir decisão/autoria/instante; arquivamento lógico preserva esses dados. Nenhuma relação de histórico de decisões é necessária por essa regra. Escritas concorrentes de conclusão precisam impedir sobrescrita da primeira decisão; CHECK local não prova imutabilidade entre versões. Estados/transições, acesso do denunciante e poderes antes da conclusão ainda precisam de aprovação. Não inventar enum, excluir registros ou executar DDL.

**Resposta 5 da nova rodada — P-02 parcialmente resolvido**: usuário aprovou **B**: grupo ativo pode ter vários responsáveis simultâneos, sem máximo específico; deve manter pelo menos um responsável ativo. Manter `group_members.is_responsible` sem UQ de responsável por grupo. A saída exige transferência apenas se for a do último responsável, com sucessor ativo escolhido/confirmado. Participações encerradas não contam; mudanças concorrentes devem compartilhar coordenação por contexto para não zerar responsáveis. Não aprova novos fluxos de promoção, ciclo final, histórico de cargos ou acesso após reentrada.

**Encerramento desta rodada**: cinco perguntas respondidas e integradas, além das cinco respostas históricas; a desambiguação de P-05 não conta como pergunta nova. P-01/P-02/P-03/P-05/P-06 estão parcialmente resolvidos, não fechados; P-04/P-07/P-08/P-09 continuam pendentes conforme matriz. A quota impede novas perguntas nesta rodada; nenhum ponto foi encerrado por inferência. Protocolo E2EE exige validação técnica/segurança e retenção exige aprovação por classe, não valores arbitrários. Continuar esclarecimentos antes da aprovação física integral; não liberar implementação ou tarefas do esquema integral. Checklist deve ser reportado pelo estado real dos marcadores, não pelos números históricos das notas.

**Validação final da rodada**: 5/5 respostas novas e cinco históricas sem duplicação, links locais/blocos Markdown e correspondência de campos das 14 relações novas candidatas conferidos por script somente de leitura. Checklist reavaliado: **12/16 → 12/16**, sem mudanças de marcadores; permanecem incompletos requisitos integralmente inequívocos/testáveis, todos os cenários, critérios de aceite de todos os requisitos e atendimento integral aos resultados mensuráveis. Notas históricas do checklist foram preservadas conforme regra de alterar somente marcadores que mudem. Hashes de `infra/`, `backend/` e `mobile/` coincidem com o snapshot disponível. `.specify/extensions.yml` ausente também na verificação pós-esclarecimento: nenhum hook `after_clarify` registrado/executado. Nenhuma aplicação, SQL, migration, pacote ou operação Docker executada.

**Resultado do gate: ERROR para aprovação integral.** Não há como afirmar honestamente “todas as clarificações resolvidas”. Esta pesquisa encerra o levantamento técnico e delimita uma proposta revisável, sem autorizar a execução da fase de implementação. Nenhum bloqueio foi escondido em um valor padrão.

**Diagnóstico do status na revisão**: `ERROR` é o resultado documental do gate de ausência de ambiguidades bloqueantes registrado em `plan.md` (Constitution Check), não erro SQL, falha de Docker, migration mal executada ou incompatibilidade detectada com PostgreSQL. Não há log de execução associado que comprove esses incidentes. `spec.md` permite avançar conceitualmente com pendências, não fechar o físico; o limite de cinco esclarecimentos explica por que P-01–P-09 ficaram abertos. Corrigir o DER não resolve essas políticas, portanto o gate continua ERROR para aprovação integral, sem impedir revisão documental. Para reavaliá-lo, registrar respostas dos responsáveis, aprovar a variante de normalização/R-02/R-03 e atualizar constraints, dicionário e rastreabilidade dos domínios afetados. Ver análise ponto a ponto em [review.md](review.md).

## 4. Referências técnicas oficiais

- PostgreSQL 17: [constraints e FKs compostas](https://www.postgresql.org/docs/17/ddl-constraints.html), [índices parciais](https://www.postgresql.org/docs/17/indexes-partial.html), [índices de expressão](https://www.postgresql.org/docs/17/indexes-expressional.html).
- PostgreSQL 17: [ranges/exclusão](https://www.postgresql.org/docs/17/rangetypes.html), [collations](https://www.postgresql.org/docs/17/collation.html), [citext e limitações](https://www.postgresql.org/docs/17/citext.html).
- Evidência normativa do projeto: `backend/database/migrations/`, `backend/config/`, `backend/composer.json`, `backend/composer.lock`, `infra/compose.yaml`, `backend/Dockerfile`, `docs/ARQUITETURA.md`, `docs/CRUDS.md`. Fontes de fora não aprovam novas funcionalidades do projeto.
