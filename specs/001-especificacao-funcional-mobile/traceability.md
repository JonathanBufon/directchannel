# Rastreabilidade e comparação — persistência SQL

**Data**: 2026-11-12 · **Estado**: proposta para aprovação, sem alteração do esquema. Requisitos: [spec.md](spec.md); desenho: [data-model.md](data-model.md); dicionário: [physical-model.md](physical-model.md); bloqueios: [research.md](research.md).

Referências `#N` apontam para `https://github.com/JonathanBufon/directchannel/issues/N`. Uma decisão local do usuário é identificada como tal, não atribuída retroativamente a uma issue. A existência de tabela em migration não comprova funcionalidade disponível ou aplicação em catálogo ativo.

## 1. Matriz DB ↔ issues ↔ entidades e regras

| Requisito | Issues/fontes | Entidades/estruturas candidatas | Como o desenho responde | Limite ou bloqueio |
| --- | --- | --- | --- | --- |
| DB-001 | Restrição do usuário; README; Compose | Oito tabelas existentes, mesmo PostgreSQL | Preserva base e mecanismo Laravel; nenhuma nova infraestrutura | Somente documentação, sem conexão SQL |
| DB-002 | #13–#15; RF01/RF05; clarify D5 | `users` | E-mail atual preservado; username sem distinção de caixa; `users.id` inalterado | P-01: collation, reutilização, backfill |
| DB-003 | #15, #23 | `users.deleted_at`, `account_blocks` | Desativação lógica distinta de bloqueio administrativo; RESTRICT candidato nas referências | P-05/P-08: efeitos, descarte e autoridade |
| DB-004 | #13–#14, #24; clarify D2 | `access_grants`, tokens de pacote, `access_grant_tokens` | Conta com 0..1 autorização vigente, várias credenciais por contexto e revogação coordenada | P-04: versão Sanctum e troca; índice não invalida sozinho tokens |
| DB-005 | #13, #22, #24; RF27 | `password_reset_tokens` preservada | Não armazena chaves privadas ou histórico recuperável pelo operador | Recuperação ainda não implementada; não reestruturar tabela genérica |
| DB-006 | #16; RF06/RF07 | `conversations`, `conversation_participants` | N:N temporal; duas pessoas distintas na individual | Invariante transacional; unicidade do par não aprovada |
| DB-007 | #16 | Conversa e participação; campo de arquivamento não fechado | Não muda par de participantes nem adiciona arquivamento global arbitrário | P-05: visibilidade por usuário/global |
| DB-008 | #17, #22; RF08/RF10 | `messages`, participação de autoria | FK composta e conteúdo cifrado; histórico indexado sem segunda cópia legível | P-07/P-09; exceção 3FN explícita a aprovar |
| DB-009 | #21; RF11/RF26 | `message_receipts` | Recibos por ocorrência destinatária, sem leitura desativada | P-09: reconciliação e relógio; SQL não decide preferência local |
| DB-010 | #5, #18, #22, #24; clarify D1 | `groups`, `group_members.is_responsible`, `conversation_participants` | Uma fonte de períodos; responsabilidade como atributo no DER/lógico/físico; transferência escolhida/confirmada | P-02: máximo de responsáveis/reentrada; concorrência exige transação e serialização |
| DB-011 | #16–#18; arquitetura documentada | `groups.conversation_id` UQ; `messages` comum | Grupo como extensão de conversa, sem duas fontes de mensagens | Convenção técnica candidata R-02, não alteração de API |
| DB-012 | #19, #22; clarify D4 | `attachments.message_id` | Mensagem 1:N anexos; arquivo privado fora do SQL; contexto derivado | P-03/P-07: upload, nulabilidade temporária, integridade |
| DB-013 | #19 | `attachments` e referência privada | Não presume metadados editáveis, cascatas, remoção física ou reutilização | P-03/P-09: política ainda incompleta |
| DB-014 | #2–#4; RF19 | `blocks` | Par direcionado único; desbloqueio remove vínculo corrente | Não inferir efeito em grupos ou reciprocidade |
| DB-015 | #20; RF20/RF24 | `reports` | Motivo obrigatório, relato, estado e arquivamento lógico; nenhuma FK privada | P-06/P-08: estados, revisão, visibilidade, retenção |
| DB-016 | #20, #23; RF22–RF24 | `users.is_admin`, `reports`, `account_blocks` | Papel, autoria, motivo/decisão; sem chaves ou leitura de mensagens por papel | P-05/P-06: efeitos de bloqueio e fluxo de decisão |
| DB-017 | #2, #23–#24; clarify D3=B | Sem tabela nova de configuração | Limite de anexo/logs somente consultáveis; nenhuma edição/histórico desses parâmetros exigido | Valores efetivos e retenção não aprovados |
| DB-018 | #22; RNF04/RNF16–RNF20 | `public_key_material`, envelope cifrado de mensagens e arquivo | Separa material público de segredos locais; não cria algoritmo próprio | P-07: dicionário criptográfico deliberadamente incompleto |
| DB-019 | #2, #24; RNF09/RNF15/RNF18 | Todas as classes; seção de inventário físico | Finalidade/acesso/local inventariados; logs não se confundem com histórico | P-08: retenção por campo ainda sem aprovação |
| DB-020 | #14–#15; código mobile | Nenhuma nova tabela para dados locais | Cofre/perfil/preferências/GPS temporário/avisos locais permanecem em suas responsabilidades | Não aprova sincronização, avatar remoto ou novo banco embarcado |

Cobertura documental: 20/20 requisitos mapeados, sem alegar que todos estão prontos para implementação. Bloqueios impedem aprovação integral, não a rastreabilidade da proposta.

## 2. Todas as issues consultadas e sua consequência

Nova coleta geral e paginada: 24 issues, 22 comentários, 12 abertas, 12 encerradas COMPLETED. Descrições, critérios e comentários foram considerados. A análise histórica detalhada das encerradas permanece em [evidencias.md](evidencias.md).

| Issue | Estado GitHub | Consequência de persistência / evidência |
| --- | --- | --- |
| #1 — P01 | Concluída | Escopo/perfis no PDF, não criação de tabelas |
| #2 — P02 | Concluída | RF/RNF e proposta de ajustes; comentário versiona proposta, não aprova RF28–RF31 |
| #3 — P03 | Concluída | Mapa de navegação com pendências; estado de identidade alterada não comprova chaves persistidas |
| #4 — P04 | Concluída | Acesso/conversa/anexos fundamentam domínio, não implementação |
| #5 — P05 | Concluída | Responsável restrito ao grupo; papel não global |
| #6 — P06 | Concluída | Administração não recebe conteúdo privado |
| #7 — P07 | Concluída | Identidade visual/componentes: sem tabela nova requerida |
| #8 — P08 | Concluída | Protótipo navegável não é fonte de schema aplicado |
| #9 — P09 | Concluída | Revisão de usabilidade não aprova volumetria/desempenho SQL |
| #10 — P10 | Concluída | Participação acadêmica e evidências, sem entidade de produto adicional |
| #11 — P11 | Concluída | Entrega do PDF, não banco implementado |
| #12 — P12 | Concluída | Apresentação/arguição, sem requisito de tabela |
| #13 — I01 | Aberta | Autenticação e cadastro; Sanctum planejado, não confundir `sessions` com tokens |
| #14 — I02 | Aberta | Integração futura; mock/local não define chave SQL ou JWT definitivo |
| #15 — I03 | Aberta | Conta, busca pública e exclusão lógica; modelo genérico atual é parcial |
| #16 — I04 | Aberta | Conversa/participação; atributos de arquivamento ainda sem regra completa |
| #17 — I05 | Aberta | Mensagem/histórico paginado; edição/exclusão fora do MVP |
| #18 — I06 | Aberta | Grupo/membro; saída do único responsável esclarecida localmente, sem mudar issue |
| #19 — I07 | Aberta | Metadados e arquivo privado; cardinalidade múltipla aprovada localmente, upload ainda aberto |
| #20 — I08 | Aberta | Denúncia/estado/arquivamento lógico; estados e revisões ainda não enumerados |
| #21 — I09 | Aberta | Entrega/leitura por participantes; adicionar Reverb ao Compose não faz parte desta etapa |
| #22 — I10 | Aberta | Conteúdo cifrado/material público; ausência de protocolo impede fechar seu esquema |
| #23 — I11 | Aberta | Administração/autoria/decisão; edição dos dois parâmetros foi retirada desta fase por resposta local B |
| #24 — I12 | Aberta | Não há comentário decisório novo; respostas de clarify estão somente em `spec.md` |

## 3. Atual × candidato

| Área | Estrutura atual declarada | Alteração candidata | O que não muda |
| --- | --- | --- | --- |
| Identidade | `users`: nome/e-mail/senha e campos padrão | Adicionar username, papel administrativo e desativação lógica com transição/backfill aprovados | `id`, e-mail único, tipos e datas atuais |
| Recuperação | `password_reset_tokens` | Nenhuma alteração automática | PK por e-mail e responsabilidades do framework |
| Sessão web | `sessions`, sem FK de usuário | Nenhuma FK retroativa por efeito colateral; API terá contexto separado | Campos técnicos e driver configurado |
| Cache/travas | `cache`, `cache_locks` | Nenhuma mudança | Mesma base/configuração; não introduzir Redis |
| Filas/falhas | `jobs`, `job_batches`, `failed_jobs` | Nenhuma mudança estrutural | Não reinterpretar jobs como mensagens do produto |
| Autorização API | Ausente | Grants, esquema oficial de tokens e ponte, condicionados a P-04 | Sessões web não substituídas; nenhuma dependência instalada agora |
| Conversa/histórico | Ausente | Conversas, participações, mensagens e recibos | Contratos públicos planejados preservados |
| Grupo | Ausente | Extensão de conversa e membros referenciando a participação canônica | Sem nova fonte de mensagens ou lista paralela de períodos |
| Anexo | Ausente | Registro por arquivo com referência a mensagem e storage privado | Sem BLOB SQL obrigatório, S3 novo ou alteração de volume |
| Moderação | Ausente | Bloqueios pessoais, denúncias e bloqueios administrativos | Administração sem conteúdo de conversas/anexos |
| Chaves públicas | Ausente | Posição conceitual, esquema bloqueado pelo protocolo | Chaves privadas só no dispositivo |
| Parâmetros | Proposta documental A06; valores efetivos não verificados | Nenhuma tabela/edição nova nesta fase | Consulta não força migração de configuração |
| Dados locais | Credencial no cofre; perfil/preferências locais; GPS temporário | Nenhuma tabela remota adicional | Mobile, URI de avatar e notificações de teste intactos |
| Infraestrutura | PostgreSQL 17, `db_data`, API e bind mount existentes | Nenhuma alteração | Compose, serviços, portas, banco, Dockerfile e arquivos intactos |

## 4. Decisões a revisar antes de aprovação

1. Aprovar ou substituir R-02: grupo como extensão 1:1 de conversa, mantendo autoria e membros coerentes.
2. Aprovar a exceção controlada à 3FN em mensagens ou optar por derivar conversa da participação; não há benchmark que imponha a exceção.
3. Resolver P-01–P-09 nas partes que afetam constraints, nulabilidade, ciclo de vida, retenção, protocolo e pacote de autenticação. Nenhuma política pendente está aprovada pelo formato de uma coluna candidata.
4. Confirmar que futuras migrations serão aditivas em `backend/database/migrations/`, não scripts paralelos em `infra/`, e que implementação só começa após autorização expressa.

Esta comparação não afirma existência de dados reais ou catálogo aplicado. A revisão futura de implantação deverá verificar isso em ambiente autorizado, sem uso de `fresh`, exclusão de volumes ou backfill arbitrário.

## 5. Resultado da revisão cruzada

- **DB-010 / R-05**: removida a entidade separada `RESPONSABILIDADE` do DER; `MEMBRO_GRUPO.is_responsible` corresponde ao atributo lógico/físico, NN/default `false`. Nenhuma 15ª relação nova ou histórico de cargos é implicado.
- **DB-008 / R-04**: os três modelos mantêm a mesma variante candidata com `messages.conversation_id`, FK composta e índices de histórico/autoria. Recomendação de redundância controlada não é aprovação nem benchmark; a alternativa estrita e os impactos conjuntos estão em `data-model.md`, seção 4.
- **DB-004/006/009/010/012/016**: FKs, índices parciais e CHECKs têm garantias limitadas; coordenação concorrente, mutações de contexto/período, autorização e consistência SQL/storage detalhadas em `physical-model.md`, seção 7.1.
- **DB-001/020**: compatibilidade estática com Compose/PostgreSQL 17 e `pdo_pgsql` confirmada; nenhum serviço novo, mudança mobile ou verificação de catálogo aplicada. Evidências e limites em [review.md](review.md).
- **Cobertura × prontidão**: seguem 14 relações novas candidatas, extensão de `users`, oito tabelas declaradas preservadas e 20/20 requisitos mapeados. Isso não fecha atributos faltantes de P-01–P-09 nem autoriza migrations. `ERROR` permanece gate de aprovação integral, não falha operacional.
