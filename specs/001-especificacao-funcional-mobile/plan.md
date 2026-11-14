# Implementation Plan: Modelagem SQL do DirectChannel — somente documentação

**Branch de feature (Spec Kit)**: `001-especificacao-funcional-mobile` | **Branch Git efetiva**: `main` | **Date**: 2026-11-12 | **Spec**: [spec.md](spec.md)

**Input**: `specs/001-especificacao-funcional-mobile/spec.md`, DB-001–DB-020 e cinco decisões de `/speckit.clarify`.

## Summary

Produzir proposta revisável de DER, modelo lógico/físico PostgreSQL 17, dicionário, rastreabilidade, comparação com a base e sequência futura de migrations Laravel. O banco é o `directchannel` já declarado, não um novo banco mobile ou outro serviço. Nenhum código de aplicação, SQL executável, migration ou alteração de infraestrutura integra esta entrega.

**Estado do gate de fechamento: ERROR — pendências de negócio/protocolo impedem aprovar o modelo lógico/físico como definitivo.** Os artefatos de design são **candidatos para revisão**, conforme pedido do usuário, não uma declaração de conclusão bem-sucedida da fase com todas as ambiguidades resolvidas. Pesquisa técnica não pode aprovar retenção, descarte, estados administrativos ou protocolo criptográfico pelo grupo. Ver [research.md](research.md), registro P-01–P-09. Implementação bloqueada até aprovação expressa da modelagem e resolução dos bloqueios aplicáveis.

Decisões já aprovadas: um dispositivo ativo por conta com revogação anterior; transferência confirmada na saída do único responsável; mensagem com vários anexos, cada anexo de uma mensagem; identificador público sem distinção de caixa; consulta, sem edição, de limite de anexos/retenção de logs. Não reabrir essas decisões como se ainda fossem desconhecidas.

## Technical Context

**Language/Version**: aplicação existente PHP `^8.3`, imagem PHP 8.4 CLI; Laravel `^13.17`. Mobile React Native/Expo/TypeScript, sem alterações.

**Primary Dependencies**: componentes atuais preservados. Sanctum e Reverb são planejados em #13/#21, não dependências já instaladas. Esta etapa não instala pacotes, Boost ou extensões SQL.

**Storage**: PostgreSQL `postgres:17`, serviço `postgres`, banco `directchannel`, volume `db_data:/var/lib/postgresql/data`; arquivos privados na árvore existente do backend. Perfil/preferências e chaves privadas continuam locais conforme responsabilidades atuais.

**Testing**: inspeção documental e revisão estática de consistência. Não executar PHPUnit, aplicação, Makefile, Docker, Artisan, SQL ou migrations: testes existentes não validam este novo esquema e podem escrever dados. Cenários futuros descritos em [quickstart.md](quickstart.md), sem implementação de testes.

**Target Platform**: infraestrutura Linux/Docker existente; aplicativo Android/iOS consome o backend, não se conecta diretamente ao SQL.

**Project Type**: aplicativo mobile com backend existente; entrega atual é documentação de modelagem relacional.

**Performance Goals**: consultas por participação, histórico paginável e recibos por destinatário orientam índices candidatos. Os 95% em 2 s/100 sessões e 99% mensal são propostas do produto, não SLA aprovado do banco ou medições.

**Constraints**: nenhuma alteração de frontend/backend/infra/contratos, nenhum segundo banco/Compose, nenhum dado ou volume tocado, nenhuma implementação. Preservar `users.id`, e-mail único e tabelas técnicas atuais. SQL físico apenas como especificação em Markdown.

**Scale/Scope**: 24 issues, 22 comentários e 20 requisitos DB conferidos. Volumetria e retenção não determinadas; registradas em P-08, sem inventar volume ou dimensionamento. Oito tabelas declaradas em migrations existentes; 14 tabelas novas candidatas, parte condicionada aos bloqueios. Não confundir tabelas declaradas com catálogo em execução.

## Constitution Check

*GATE: verificação pré-pesquisa e pós-design.*

A constituição em `.specify/memory/constitution.md` é um template não preenchido. Não há princípios ratificados para extrair, nem autorização para preenchê-la nesta etapa. Essa ausência é registrada, não substituída por princípios inventados.

| Gate | Antes da pesquisa | Após o design |
| --- | --- | --- |
| Apenas artefatos no diretório da feature | PASS | PASS — fontes preservadas por comparação de hashes |
| Stack, diretórios e infraestrutura preservados | PASS | PASS — mesma base PostgreSQL, sem serviços/extensões novas |
| Contratos atuais/documentados não redefinidos | PASS | PASS — inventário de compatibilidade, sem novo OpenAPI ou payload obrigatório |
| Cinco decisões do clarify respeitadas | PASS | PASS — refletidas nas relações e constraints candidatas |
| Nenhuma implantação, SQL ou migration executada | PASS | PASS — somente arquivos Markdown |
| Nenhum requisito/estado de negócio inventado | PASS | PASS — alternativas desconhecidas sinalizadas e não aprovadas |
| Ausência de esclarecimentos bloqueantes para fechamento físico | FAIL | **ERROR** — P-01–P-09 impedem fechamento integral; design apenas revisável |

Não há exceção que autorize ultrapassar o último gate ou iniciar implementação. O pedido de apresentar uma proposta para revisão permite documentar alternativas, não tratar ambiguidades como resolvidas. Revisão de aprovação deve distinguir decisões técnicas propostas e regras de negócio ainda abertas.

## Project Structure

### Documentation (this feature)

```text
specs/001-especificacao-funcional-mobile/
├── spec.md                    # requisitos e esclarecimentos — preservado nesta etapa
├── evidencias.md              # inventário anterior — preservado
├── checklists/requirements.md # checklist anterior — preservado
├── plan.md                    # plano, gates e limitações
├── research.md                # decisões, alternativas, fontes e bloqueios
├── data-model.md              # DER Mermaid, modelo lógico e normalização
├── physical-model.md          # dicionário SQL, constraints, índices e migrações planejadas
├── traceability.md            # DB ↔ issues ↔ tabelas; atual × candidato
├── review.md                  # revisão: achados, P-01–P-09 e aprovações pendentes
├── contracts/
│   └── persistence-boundaries.md # contratos existentes e limites da persistência
└── quickstart.md              # roteiro seguro de revisão/validação documental
```

Não gerar `tasks.md`, código, migrations, `.sql` ou configuração de agentes nesta etapa.

### Source Code (repository root)

```text
backend/
├── app/Models/User.php
├── config/
├── database/migrations/       # mecanismo atual; arquivos existentes intocados
├── routes/
└── Dockerfile
mobile/src/
├── navigation/
├── screens/
└── services/                  # persistência local atual intacta
infra/
├── compose.yaml
├── README.md
├── .env.example
└── scripts/bootstrap.sh
docs/
README.md
Makefile
```

**Structure Decision**: preservar a organização real. Ao contrário da premissa inicial do pedido, o Compose está em `infra/`, mas migrations estão em `backend/database/migrations/`; não movê-las nem gerar scripts SQL paralelos em `infra/`.

## Complexity Tracking

Nenhuma mudança arquitetural proposta. Tabelas associativas representam relações já exigidas, não novos módulos de software. Integridade temporal e revogação exigirão coordenação transacional na futura implementação autorizada; o design identifica esse requisito sem escrever serviços ou triggers. Manter o gate de aprovação falhando é obrigatório enquanto persistirem bloqueios, não uma exceção a justificar.

## Phase 0 — Pesquisa

- Consultadas novamente todas as issues por `gh issue list --state all --limit 1000 --json ...`; paginação da API confirmou 24 issues (12 abertas, 12 concluídas) e 22 comentários.
- Dois agentes de pesquisa somente leitura usados para conferir integridade PostgreSQL e base/contratos, conforme o fluxo solicitado. Nenhuma escrita delegada.
- Decisões técnicas e alternativas consolidadas em [research.md](research.md). Dúvidas de negócio não resolvíveis por pesquisa permanecem como bloqueios explícitos, não respostas presumidas.

## Phase 1 — Proposta de design para revisão

- [data-model.md](data-model.md): conceitos, DER, relações N:N, cardinalidades, 3FN e estados conhecidos.
- [physical-model.md](physical-model.md): tipos PostgreSQL, nulabilidade, PK/FK, UNIQUE/CHECK, índices, ON DELETE/UPDATE e sequência documental de migrations.
- [traceability.md](traceability.md): 20 requisitos e todas as issues com impacto na persistência; diferenças entre atual/candidato.
- [contracts/persistence-boundaries.md](contracts/persistence-boundaries.md): contratos externos inalterados, sem impor atributos internos aos payloads.
- [quickstart.md](quickstart.md): revisão por exemplos e validação sem tocar banco/infra.

**Ponto de parada**: apresentar a modelagem e aguardar aprovação; não gerar tarefas de implementação nem executar migrations. Revisar P-01–P-09 antes de aprovar o modelo integral. Nenhuma promessa de prontidão física completa ou de gate verde acompanha esta proposta.

## Validação final e hooks

Sete artefatos de planejamento presentes; links locais, blocos Markdown e correspondência das 14 relações novas entre lógico/dicionário conferidos. Matriz cobre 20/20 requisitos e as cinco respostas da especificação permanecem preservadas. DER Mermaid conferido estruturalmente, não renderizado; constraints, queries e migrações não foram executadas. Essa validação não certifica uma implementação ou resolve o gate ERROR.

Comparação de hashes confirmou preservação integral de `infra/` (4 arquivos), `backend/` (62), `mobile/` (49) e `.specify/` (28), em relação ao início da revisão. Nenhum arquivo `.sql`, migration, tasks ou código foi criado. A branch Git permanece `main`; o identificador da feature retornado pelo Spec Kit é independente dela.

Pré/pós-execução: `.specify/extensions.yml` ausente; nenhum hook `before_plan`/`after_plan` registrado ou executável, seções ignoradas conforme as regras. Entrega encerrada em proposta documental para revisão, aguardando aprovação e esclarecimentos pendentes, sem início de implementação.

## Revisão posterior do planejamento existente

Revisão solicitada sem reiniciar especificação: [review.md](review.md) consolida os sete eixos pedidos, os problemas e as decisões pendentes. DER alinhado a `group_members.is_responsible`; recomendação de redundância controlada em mensagens continua candidata; estratégia de integridade e significado do ERROR esclarecidos. Inspeção estática confirma compatibilidade com o PostgreSQL/Docker declarados, não com catálogo runtime. Nenhum pacote/Boost é necessário ou instalado para esta revisão exclusivamente documental; não há alteração de aplicação. O gate continua ERROR para fechamento integral.
