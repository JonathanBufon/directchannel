Revise o planejamento de banco de dados já
gerado pelo Spec Kit.

Não reinicie a especificação e não altere
a arquitetura da aplicação mobile.

1. Corrija a divergência entre a entidade
   RESPONSABILIDADE no DER e o atributo
   is_responsible do modelo lógico.

2. Avalie a redundância de conversation_id
   na tabela messages e documente a escolha
   entre normalização estrita e desempenho.

3. Verifique as regras de integridade que
   exigem transações ou validações adicionais.

4. Analise os pontos pendentes P-01 a P-09.

5. Verifique o motivo do status ERROR
   registrado em research.md.

6. Confirme a compatibilidade do modelo
   proposto com o PostgreSQL e o Docker
   existentes na pasta infra/.

7. Revise a consistência entre data-model.md,
   physical-model.md e traceability.md.

RESTRIÇÕES:
- Não modificar frontend ou backend.
- Não alterar arquivos da pasta infra/.
- Não executar migrations.
- Não modificar o banco existente.
- Não criar tabelas nesta etapa.

Apresente os problemas identificados,
as correções propostas e as decisões
que ainda dependem de aprovação.# Revisão do planejamento de banco de dados

**Data**: 2026-11-12 · **Resultado**: proposta documental corrigida; aprovação integral continua bloqueada. Revisão do planejamento existente, sem reiniciar especificação ou modificar arquitetura mobile.

## 1. Problemas, correções e classificação

| Achado | Correção documental / proposta | Situação |
| --- | --- | --- |
| DER sugeria entidade `RESPONSABILIDADE`, mas lógico/físico usam booleano do membro | Removida entidade separada; DER representa `MEMBRO_GRUPO.is_responsible`, correspondente a `group_members.is_responsible` | Corrigido nos artefatos, sem criar tabela ou histórico |
| Redundância de `messages.conversation_id` viola 3FN estrita | Explicitada recomendação de manter variante candidata para histórico por conversa, custos e alternativa estrita; FK composta impede divergência, não valida temporalidade | Escolha técnica depende de aprovação; ganho sem medição |
| Transações citadas sem todas as condições de concorrência e mutação | Acrescentadas serialização comum, revalidação de dependentes, conta responsável desativada, recibos, tokens e ordem temporal em `physical-model.md`, seção 7.1 | Requisitos futuros de integridade, não implementação |
| Consistência de arquivo e SQL poderia parecer coberta por transação | Explicitados limites do rollback SQL, necessidade de idempotência, compensação/reconciliação de falhas e órfãos | Política P-03 pendente; sem nova infraestrutura |
| `ERROR` pode ser interpretado como falha operacional | Explicada origem no gate de fechamento por ambiguidades; não há evidência de erro PostgreSQL/Docker ou migration executada | Status preservado, significado esclarecido |
| Dicionário parcial pode parecer esquema pronto | Mantidos bloqueios de protocolo, upload, estados e backfill; cobertura de requisitos não significa completude física | P-01–P-09 permanecem abertos |

## 2. Normalização estrita × desempenho

**Variante A — estrita**: remover `messages.conversation_id`, usar FK simples `sender_participant_id` e derivar conversa por participação. Elimina dependência transitiva e custo de consistência do dado duplicado; leitura do histórico precisa de join e pode reunir/ordenar mensagens de várias participações. Índices e planos podem torná-la suficiente; não foi medida.

**Variante B — redundância controlada, recomendada para aprovação**: manter coluna de conversa e índice `(conversation_id,accepted_at,id)`, com FK composta para participação e UQ técnica `(id,conversation_id)`. Facilita acesso ordenado direto ao histórico da conversa, ao custo de coluna, índices e escrita adicional. `sender_participant_id → conversation_id` impede declarar 3FN estrita. A FK garante igualdade do contexto, não vigência do autor, permissão de leitura ou E2EE.

O planejamento continua inteiramente na **variante B candidata**, sem alternar versões entre lógico/físico/rastreabilidade. Aprovar explicitamente B ou atualizar os três documentos para A antes de qualquer implementação. Comparação futura deve considerar volume, retenção, distribuição de grupos/participações, cursores, latência de leitura e custo de escrita em ambiente autorizado. Não executar benchmark no banco existente nesta etapa.

## 3. Análise dos pontos P-01 a P-09

Nenhum ponto abaixo reabre as cinco decisões já aprovadas em `spec.md`; todos delimitam o que elas não decidiram. Prioridade indica dependência de fechamento, não autorização de implementação.

| Ponto | Diagnóstico / risco | Proposta e decisão necessária | Aprovação responsável / impacto |
| --- | --- | --- | --- |
| **P-01 — Identificador público** | Comparação sem distinção de caixa aprovada; `lower` não resolve toda equivalência Unicode. Faltam alteração, reutilização, collation e backfill; índice global reserva nomes, parcial permite reutilização | Preservar grafia/e-mail atuais. Aprovar equivalências aceitas, escopo de unicidade, alteração e atribuição de nomes a contas preexistentes; verificar colisões futuramente antes de NN/UQ | Produto + revisão técnica; bloqueia índice final e backfill de `users.username` |
| **P-02 — Grupo e reentrada** | Booleano suporta vários responsáveis, mas não aprova máximo. Não há ciclo final de grupo definido. Reentrada não pode sobrescrever período nem liberar automaticamente passado | Manter transferência escolhida/confirmada e saída impedida sem sucessor elegível. Aprovar máximo (ou ausência dele), desativação/exclusão, efeito da desativação do responsável e se reentrada permite acesso a períodos anteriores já autorizados; nunca liberar períodos sem participação por inferência | Produto; bloqueia ciclo de grupo e autorização temporal; sem histórico de cargos obrigatório |
| **P-03 — Upload e arquivo** | `message_id` obrigatório faz sentido no vínculo final, não fecha fase temporária. Falhas parciais e remoção não são resolvidas por FK/rollback | Aprovar ordem upload/mensagem, associação final, recuperação de falha, limites de quantidade/tamanho e tratamento de referência após remoção. Definir bytes medidos antes/depois da cifragem com P-07; especificar compensação/reconciliação sem escolher serviço novo | Produto + revisão técnica; bloqueia nulabilidade/ciclo de `attachments`; 0..N não significa ilimitado |
| **P-04 — Acesso e Sanctum** | Um contexto ativo aprovado não basta para provar um dispositivo autorizado; falta semântica de troca/renovação. Sanctum não consta das dependências declaradas; versão/schema são referência | Aprovar autorização da troca e distinção entre renovar no mesmo contexto e novo login de outro contexto; selecionar versão compatível, confirmar migration oficial, validar dono/tipo de token e revogação em cada uso. Não coletar fingerprint nem instalar pacote nesta etapa | Produto/segurança + revisão técnica; bloqueia grants/ponte/schema definitivo de tokens |
| **P-05 — Conversa e bloqueio administrativo** | Arquivamento/saída individual não tem escopo definido; FK de conta não impede ações de conta bloqueada/desativada | Aprovar visibilidade individual/global, efeito sobre histórico e participação, quais ações bloqueio/desativação impedem e efeito nas credenciais. Preservar par original de contas da individual; não introduzir booleano global arbitrário | Produto/segurança; bloqueia campos de vínculo, ciclo de conta e validações concorrentes |
| **P-06 — Denúncia e decisão** | Estados/transições e revisões indefinidos; campos opcionais da decisão não garantem preenchimento conjunto nem poderes de edição | Aprovar máquina de estados, autor/permissões, campos obrigatórios por transição, visibilidade ao denunciante, arquivamento e uma decisão corrente versus revisões. Definir vínculo operacional com eventual bloqueio de conta | Produto/moderação; bloqueia CHECKs/defaults e completude de `reports`; não criar tabela de revisões por suposição |
| **P-07 — E2EE** | `bytea` é tipo compatível, não protocolo. `public_key_material` mínimo não garante prekeys, rotação, versões ou integridade do anexo | Selecionar protocolo/biblioteca e aprovar composição remota, material público, envelopes, versionamento e vínculo ao contexto. Só depois completar dicionários e restrições; manter chaves privadas exclusivamente no dispositivo | Segurança + revisão técnica; bloqueador central de mensagens/anexos/material público, sem redesenhar mobile nesta revisão |
| **P-08 — Retenção e capacidade** | RESTRICT evita cascata acidental, mas não define descarte nem capacidade. Falta aprovação por classe/campo; índice não justifica promessa de desempenho | Aprovar finalidade/acesso, retenção, exclusão definitiva e volumetria de contas/mensagens/anexos/recibos/tokens. Separar logs operacionais de histórico cifrado; definir política antes de purga/dimensionamento. Sem novos volumes/serviços agora | Produto + responsável por privacidade/operação; bloqueia certificação do inventário, descarte e capacidade; 25 MB/30 dias não aprovados |
| **P-09 — Metadados, corpo e eventos** | Contrato HTTP não define editabilidade; corpo NN não resolve mensagem só com anexo. Timestamp/ID não equivalem a ordem de commit; eventos podem repetir/chegar fora de ordem | Aprovar metadados/permissões, representação cifrada da mensagem só com anexo junto a P-07, relógio de aceitação/entrega, reconciliação monotônica/idempotente de recibos e semântica de cursor. Não persistir leitura desativada | Produto + revisão técnica/segurança; bloqueia validações finais de corpo, recibos, metadados e ordenação |

**Sequência de decisão sugerida**: P-07 e P-04 resolvem contratos técnicos centrais; P-01 resolve identidade/backfill; P-02/P-05/P-06 fecham ciclos e autorização; P-03/P-09 fecham arquivos/eventos em conjunto com P-07; P-08 deve acompanhar todos e concluir inventário/capacidade. Aprovação parcial de um domínio não torna o esquema integral implementável nem autoriza execução.

## 4. Integridade que não cabe em FK/CHECK simples

O dicionário já propõe PK/FK, UQ parciais e CHECKs locais válidos. Permanecem exigências externas:

1. Duas contas distintas e par estável na individual; tipo de conversa coerente com extensão de grupo e membros.
2. Períodos sem sobreposição, autoria válida no instante de aceitação e destinatário elegível no mesmo contexto.
3. Grupo ativo com responsável, transferência confirmada e sucessor ainda elegível sob concorrência, inclusive remoção/desativação de conta responsável.
4. Associação obrigatória de credenciais mobile, coincidência de dono/tipo e rejeição de grant revogado/expirado conforme contrato de acesso.
5. Papel administrativo, transições de denúncia e efeito de bloqueio/desativação validados pela operação autorizada.
6. Arquivo privado e registro SQL reconciliados quando uma etapa falha; limites e associação de anexos protegidos de concorrência.
7. Mudanças posteriores em contexto, par, tipo ou período não podem invalidar dependentes silenciosamente. Preferir identidades/contextos imutáveis e retificações controladas, sujeito à aprovação.

**Correção proposta**: protocolo comum de transação e serialização por conta/conversa, validações sob a mesma coordenação e política explícita de falhas. Apenas abrir uma transação e consultar antes de escrever em `READ COMMITTED` não evita todas as anomalias. `CHECK` não deve consultar outras tabelas; triggers/isolamento/retries são decisões futuras, não código entregue. Detalhes e casos conceituais: `physical-model.md`, seção 7.1.

## 5. Compatibilidade PostgreSQL e Docker

| Evidência estática | Conclusão e limite |
| --- | --- |
| `infra/compose.yaml`: `postgres:17`, serviço `postgres`, banco/usuário `directchannel`, volume `db_data:/var/lib/postgresql/data` | Modelo alvo coincide com a infraestrutura declarada; não exige outro banco, volume, serviço ou extensão |
| API no Compose: `DB_CONNECTION=pgsql`, `DB_HOST=postgres`, porta interna `5432` | Conexão prevista correta na rede do Compose. PostgreSQL não publica porta de host; a proposta não exige publicação |
| `backend/Dockerfile`: PHP 8.4 e instalação de `pdo_pgsql` | Driver necessário declarado na imagem; não foi feito build nem verificada imagem em execução |
| `backend/config/database.php`: conexão `pgsql`, schema `public` | Fallback SQLite não muda o alvo informado pelo Compose. `DB_URL`, ambiente/config cache efetivos não foram inspecionados |
| Três migrations existentes | Oito tabelas declaradas, `users.id` numérico, e-mail único e ausência de FKs preservados; não se comprovou aplicação no catálogo |
| Tipos e constraints candidatos | PostgreSQL 17 suporta `bigint`, identity, `boolean`, `text`, `bytea`, `timestamptz(6)`, FKs compostas, índices parciais/de expressão e RESTRICT. A UQ técnica candidata sustenta a FK composta |
| Colunas antigas sem fuso e novas com fuso | Combinação suportada; futura integração precisa conversão temporal explícita e sem alterar datas existentes por inferência |
| Sem `citext`/`btree_gist`/ICU obrigatório | Nenhuma extensão adicional exigida; collation real continua pendente de verificação autorizada e P-01 |

**Conclusão**: compatível por inspeção estática com a infraestrutura declarada, não certificado em runtime. Sem conexão SQL, inspeção de catálogo/dados, validação de DDL, renderização Mermaid, benchmark ou execução Docker. `.env` real/segredos e overrides não foram lidos. Compatibilidade do esquema final Sanctum depende da versão ainda não selecionada. Nenhuma incompatibilidade encontrada justifica mudança em `infra/` ou na aplicação.

## 6. Consistência entre artefatos e aprovação

- `data-model.md`: DER corrigido, papel como atributo, 14 relações novas candidatas mais extensão de `users`; normalização e alternativas explícitas.
- `physical-model.md`: mesmos nomes/relações e variante de mensagens; `is_responsible` NN/default `false`; FK composta, UQ técnica e índices correspondentes; novas ressalvas de integridade. Campos não fechados continuam declaradamente parciais.
- `traceability.md`: DB-010 aponta ao atributo, DB-008 mantém exceção candidata; requisitos e limites dos demais domínios preservados. 20/20 mapeados não significa 20/20 implementáveis.
- `research.md`/`plan.md`: status continua ERROR **somente para fechamento integral**, por P-01–P-09 e decisões técnicas ainda candidatas; revisão documental não o converte em PASS.
- `spec.md` e contratos existentes preservados; não houve nova rodada de especificação nem mudança da arquitetura mobile.

**Ainda requer aprovação**: R-02/R-03 (grupo/conversa e participação temporal), variante A/B de mensagens, regras P-01–P-09 e estratégia futura de integridade/migração. A correção representacional do DER não cria nova regra de negócio. Mesmo após aprovação documental, implementação, migrations, instalação de dependências e acesso ao banco exigem autorização expressa separada.

**Escopo respeitado**: somente Markdown nesta pasta; nenhum frontend/backend/infra alterado, pacote instalado, migration/SQL executado, banco acessado, tabela criada ou serviço iniciado. O trabalho pré-existente não rastreado foi preservado. Validação local de links, campos, contagem e preservação de fontes não equivale a teste do banco.

**Verificação executada nesta revisão**: script Python somente de leitura confirmou links locais e blocos Markdown fechados, ausência da entidade separada no DER, correspondência nominal das 14 relações novas entre lógico/físico, cobertura DB-001–DB-020 e análise P-01–P-09. Hashes SHA-256 antes/depois confirmaram preservação de `infra/` (4 arquivos), `backend/` (62) e `mobile/` (49), excluídos diretórios de dependências/artefatos (`vendor`, `node_modules`, `.git`, `.expo`). Nenhum teste de aplicação ou banco foi executado.
