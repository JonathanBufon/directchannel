A# Evidências da análise — DirectChannel

**Data**: 2026-11-12. **Repositório**: `JonathanBufon/directchannel`.
**Branch/revisão local**: `main`, `d2b38595c194284b8e7eda4607f02a4601181b19`.
**Especificação**: [spec.md](spec.md).

**Revisão de escopo em 12/11/2026**: a especificação foi restringida aos requisitos de persistência por solicitação expressa. O inventário funcional abaixo permanece como evidência de origem, não como autorização para implementar telas, API, criptografia ou serviços. Os FR da primeira versão foram substituídos pelos DB-001–DB-020 no mesmo `spec.md`; não foi criada especificação paralela. As notas narrativas do checklist da primeira versão foram preservadas conforme a regra de só alternar seus marcadores; o resultado atual deve ser lido contra a especificação revisada.

**Esclarecimento aceito nesta sessão**: usuário respondeu “Letra A” à questão D2. A especificação passa a exigir no máximo um dispositivo ativo por conta, revogação da autorização anterior mediante novo login autorizado e ausência de recuperação de chaves privadas pelo servidor. Conta–dispositivo ativo: 0..1; isso não limita o histórico de autorizações revogadas nem o número de chaves históricas. A aprovação foi registrada somente na especificação; #2/#24 não foram modificadas. O checklist passa de 11/16 para 12/16 pela remoção do marcador formal; demais lacunas de negócio continuam explícitas.

**Segundo esclarecimento aceito**: usuário respondeu “Letra A” à questão D1. A saída do único responsável exige escolha e confirmação de outro membro ativo do mesmo grupo; sem sucessor elegível, a saída é impedida. A conclusão deve preservar um responsável no grupo; cancelar mantém participação e responsabilidade anteriores. A regra não fixa o máximo de responsáveis simultâneos nem autoriza exclusão automática do grupo. Atualizados DB-010, cenários, caso concorrente e relações conceituais, sem modificar #18/#24 ou código. Checklist reavaliado: 12/16 → 12/16; permanecem lacunas de outras relações e políticas, sem mudanças adicionais de marcadores.

**Terceiro esclarecimento aceito**: usuário respondeu “Letra B” à questão D4: uma mensagem pode reunir vários anexos, com cada anexo pertencendo a uma mensagem. A relação final é mensagem 1:N anexos (0..N por mensagem e exatamente 1 mensagem por anexo associado). Não se atribui essa decisão às issues, que não definiam cardinalidade. Limite de quantidade, fase de associação no upload e tratamento de falhas parciais permanecem abertos, sem mudar contratos ou implementar seleção/upload múltiplos. Atualizados DB-012, cenários e cardinalidades. Checklist reavaliado: 12/16 → 12/16, sem alterações de marcadores; ainda existem lacunas de negócio que impedem fechamento integral.

**Quarto esclarecimento aceito**: usuário respondeu “Letra A” à questão de comparação do identificador público em D5: variantes apenas de maiúsculas/minúsculas são equivalentes e não identificam contas diferentes. Atualizados DB-002, I-001, cenário de duplicidade, caso concorrente, entidade Conta e SC-003. Comparação de e-mail, formato de exibição, outras equivalências de caracteres e reutilização após desativação não foram alterados por inferência. Checklist reavaliado: 12/16 → 12/16, sem mudança de marcadores; demais pendências permanecem registradas.

**Quinto esclarecimento aceito e encerramento da rodada**: usuário respondeu “B” à questão D3: limite de anexos e prazo de retenção de logs serão somente consultáveis nesta fase. Edição administrativa e histórico dessas edições ficam fora do escopo atual, sem exigir tabela nova ou migração de configuração para SQL. A decisão não elimina registros de autoria/motivo de bloqueios e decisões administrativas. Atualizados DB-017, cenário de preservação, perfis, conceito de configuração, D3, status e premissas. Checklist final: 11/16 no início → 12/16; único marcador alterado foi ausência de marcadores formais de esclarecimento; nenhuma regressão. Persistem quatro itens desmarcados: requisitos plenamente inequívocos, todos os cenários de aceite, aceite de todos os requisitos e resultados integrais de prontidão. As notas antigas do checklist foram mantidas, conforme instrução de alterar apenas marcadores.

As cinco respostas aprovam somente o que foi perguntado. Permanecem adiados por limite desta rodada: máximo de responsáveis, autorização detalhada de troca de dispositivo, limite de anexos/ciclo de upload, reutilização e outras equivalências do identificador, descarte/retenção, estados de denúncia e efeitos de bloqueios/arquivamento. Tipos, índices, FKs, representação de recibos e associação física grupo/conversa são decisões de planejamento, dentro da arquitetura existente. É possível iniciar `/speckit.plan` para modelagem conceitual documentando essas pendências; não declarar modelo lógico/físico definitivo sem resolvê-las, nem implementar mudanças.

Validação final: cinco respostas distintas na seção `Clarifications`, 20 requisitos DB preservados, referências locais válidas e nenhuma contradição remanescente das alternativas substituídas. Hashes de `infra/`, `backend/`, `mobile/` e `.specify/` continuam idênticos ao início da revisão. Verificação pós-execução: `.specify/extensions.yml` continua ausente; nenhum hook `after_clarify` registrado, seção ignorada conforme o fluxo. Nenhum SQL, migration ou comando de contêiner foi executado.

## Método e limites

Análise estática de README, documentação textual, ambos os PDFs, navegação, telas, serviços locais, validações, modelo de usuário, migrations, rotas, configuração de inicialização, dependências e testes existentes. O PDF UX/UI declara na p. 1 que não contém aplicativo implementado e na p. 2 que os formulários do protótipo não processam informações.

Foram consultadas as issues por GitHub CLI, sem escrita no GitHub:

```sh
gh issue list --state all --limit 1000 --json number,title,state,stateReason,body,comments,labels,createdAt,updatedAt,closedAt,url
gh api --paginate 'repos/JonathanBufon/directchannel/issues?state=all&per_page=100'
gh api --paginate 'repos/JonathanBufon/directchannel/issues/comments?per_page=100'
gh api --paginate 'repos/JonathanBufon/directchannel/issues/events?per_page=100'
gh api graphql -f query='query { repository(owner:"JonathanBufon", name:"directchannel") { issues { totalCount } } }'
```

Resultado cruzado: **24 issues**, **12 abertas**, **12 fechadas com motivo COMPLETED**, nenhuma fechada com outro motivo e **22 comentários**. A contagem da consulta geral coincide com o total informado pelo GitHub. A consulta paginada foi executada mesmo sem atingir 1.000; retornou uma página de issues. Foram consideradas descrições e todas as listas de critérios de conclusão, inclusive os itens ainda desmarcados das issues encerradas. Os 22 comentários da listagem coincidem com os 22 da consulta independente, sem truncamento de comentários nesta coleta. Pull requests não foram contados como issues.

As respostas integrais das consultas foram mantidas temporariamente em `/tmp/opencode/directchannel-{issues,issues-api,comments,events}.json`; os dados essenciais e conclusões duráveis estão neste documento. Esses arquivos temporários não são dependência da especificação.

Não foram instaladas dependências da aplicação, executadas migrations ou alteradas issues. Não foram executados testes de backend/mobile nem iniciado o aplicativo. As conclusões de presença/ausência referem-se ao código da revisão local, não a ambientes externos ou branches não consultadas.

## Estrutura examinada

- `README.md`: proposta, perfis, índice documental e roadmap; a descrição inicial comunica a intenção de privacidade, enquanto o roadmap reconhece a proteção de ponta a ponta como pendente.
- `mobile/`: aplicativo com duas telas reais, componentes visuais, validações e serviços de persistência/hardware; `Main` é o perfil, não uma lista de conversas.
- `backend/`: base Laravel com um modelo de usuário genérico, migrations padrão e testes de exemplo; não possui módulos de domínio ou rotas funcionais de negócio.
- `infra/`: ambiente e automação de desenvolvimento; presença de infraestrutura não comprova autenticação, CRUDs ou mensageria.
- `docs/`: UX/UI, relatório de implementação, evidências visuais, fluxo, arquitetura proposta, CRUDs, ajustes de requisitos e organização do trabalho.
- `.specify/`: numeração sequencial; template ativo resolvido por `specify preset resolve spec-template` para `.specify/templates/spec-template.md`, camada `core`. A constituição é um modelo sem preenchimento. Nenhum diretório anterior em `specs/`; prefixo escolhido: `001`.
- `.specify/extensions.yml`: ausente na verificação prévia; portanto nenhum pre-hook executável ou criação de branch. Verificação posterior registrada no checklist.

## Inventário confrontado com código

| Área | Evidência concreta | Conclusão |
| --- | --- | --- |
| Navegação | `mobile/src/navigation/RootNavigator.tsx:22–29` registra somente `Login` e `Main`; `Main` aponta para `ProfileScreen` | T04 e demais telas, inclusive A01–A06, não implementadas |
| Login | `mobile/src/screens/auth/LoginScreen.tsx:28–51`: espera artificial, credencial constante e perfil derivado do e-mail; a senha não é verificada | Demonstração, não autenticação |
| Cadastro/recuperação | `LoginScreen.tsx:81–82`: apenas alertas com os nomes T03 e T02 | Não existem fluxos funcionais de cadastro ou recuperação |
| Retomada de sessão | `LoginScreen.tsx:18–23`: verifica apenas se há credencial armazenada | A expressão documental “token válido” excede o que o código comprova |
| Formulário | `mobile/src/components/organisms/LoginForm.tsx:42–65`, `mobile/src/utils/validation.ts` | Validação de formato de e-mail e senha não vazia; bloqueia repetição durante carregamento; utilitários de nome/confirmação existem, mas não comprovam cadastro |
| Persistência local | `mobile/src/services/storage.ts:11–17,19–53,60–89` | Credencial local protegida, perfil local e utilitários genéricos de preferências; logout remove credencial e perfil, preserva preferências |
| Perfil | `mobile/src/screens/profile/ProfileScreen.tsx:34–115` | Lê dados locais; câmera/galeria atualizam referência de avatar; GPS fica no estado da tela; logout apenas local |
| Exposição demonstrativa | `ProfileScreen.tsx:38,99–103,156–164` | Exibe prefixo de credencial e afirma “chaves estão ativas” sem verificação criptográfica; não confundir texto da interface com garantia de segurança |
| Hardware | `mobile/src/services/natives.ts`, `mobile/App.tsx:10–12` | Câmera, galeria, GPS em primeiro plano e notificação local com solicitação de permissão; não há notificação de mensagem remota |
| Usuário remoto | `backend/app/Models/User.php:13–30` | Nome, e-mail e senha com transformação para hash; sem identificador público, papel, bloqueio, exclusão lógica ou relações de domínio |
| Estrutura persistente | `backend/database/migrations/0001_01_01_000000_create_users_table.php` | Apenas usuários e estruturas padrão de recuperação/sessões; não comprova que foram criadas num banco em execução |
| Outros dados | Demais migrations criam cache e jobs; não há migrations/modelos de conversas, membros, grupos, mensagens, anexos, denúncias ou bloqueios | Modelo de domínio em `docs/ARQUITETURA.md` continua proposta |
| Rotas | `backend/routes/web.php`, `backend/bootstrap/app.php:9–13`; ausência de `backend/routes/api.php` | Apenas página inicial e rota de saúde configuradas; tratamento de erro com caminho `api/*` não cria rotas de API |
| Dependências | `backend/composer.json`, `mobile/package.json` | Sem Sanctum/Reverb declarados; não há serviços HTTP, autenticação real ou tempo real no mobile |
| Testes | `backend/tests/Feature/ExampleTest.php`, `backend/tests/Unit/ExampleTest.php`; script `test` do mobile apenas retorna erro | Testes genéricos não comprovam os critérios de aceitação das issues |

`docs/IMPLEMENTACAO.md` e o relatório PDF corroboram a implementação local, mas chamam a retomada de verificação de “token válido” e o logout paralelo de “atômico”. O código não verifica validade remota nem garante transação de limpeza: executar duas remoções em paralelo não comprova atomicidade. A especificação não incorpora essas alegações como resultado implementado.

## Cobertura de todas as issues e respectivos históricos

Cada link leva à descrição original, critérios e comentários. “Concluída” nesta tabela é o estado no GitHub, não a conclusão de implementação do produto.

| Issue | Estado | Descrição/aceite e histórico relevante | Resultado do confronto |
| --- | --- | --- | --- |
| [#1](https://github.com/JonathanBufon/directchannel/issues/1) P01 | Concluída | Consolidar problema, objetivo, benefício e perfis; comentário associa conclusão ao PDF entregue em 10/09 | Fonte documental de escopo; não entrega funções executáveis |
| [#2](https://github.com/JonathanBufon/directchannel/issues/2) P02 | Concluída | Revisar RF/RNF e rastreabilidade; três comentários: atribuição/revisão, anexo de proposta e posterior versionamento em `REQUISITOS-AJUSTES.md` | RF28–RF31 e revisões não se tornam aprovados pelo encerramento; #24 exige decisão |
| [#3](https://github.com/JonathanBufon/directchannel/issues/3) P03 | Concluída | Mapa de navegação; comentário extenso registra todos os destinos, “T6”, “T09*” e falta de entrada de T20; último comentário remete ao fluxo versionado | Pendências persistem, apesar do aceite de entradas/retornos marcado; só duas telas existem |
| [#4](https://github.com/JonathanBufon/directchannel/issues/4) P04 | Concluída | Revisão de wireframes de acesso, busca, conversa e anexos; aceite pede estados vazios/erro; comentário cita PDF | Especificação visual, não entrega mensageria ou cadastro |
| [#5](https://github.com/JonathanBufon/directchannel/issues/5) P05 | Concluída | Revisar grupos/perfil respeitando responsável; comentários de atribuição e conclusão documental | Permissões planejadas; perfil local parcial, grupos ausentes |
| [#6](https://github.com/JonathanBufon/directchannel/issues/6) P06 | Concluída | Revisar administração sem acesso privado; comentários de atribuição e entrega do PDF | Limite administrativo fundamentado; painel ausente |
| [#7](https://github.com/JonathanBufon/directchannel/issues/7) P07 | Concluída | Identidade e componentes consistentes; comentário aponta PDF e planejamento | Tema/componentes existem; não comprova avaliação completa de acessibilidade |
| [#8](https://github.com/JonathanBufon/directchannel/issues/8) P08 | Concluída | Protótipo sem destinos quebrados e limites da simulação; comentários citam entrega documental | PDF navegável não equivale à navegação implementada; pendências de #3/#24 preservadas |
| [#9](https://github.com/JonathanBufon/directchannel/issues/9) P09 | Concluída | Revisão de usabilidade com observações e correções; comentários de atribuição e entrega | Não há resultados medidos que comprovem quatro de cinco usuários bem-sucedidos; não declarar meta alcançada |
| [#10](https://github.com/JonathanBufon/directchannel/issues/10) P10 | Concluída | Participação/histórico; comentário remete aos responsáveis e prazos consolidados | Organização do trabalho, sem nova função do aplicativo |
| [#11](https://github.com/JonathanBufon/directchannel/issues/11) P11 | Concluída | Consolidar PDF e links; comentários de atribuição e entrega | Artefato existe; o PDF conserva avisos de evidências incompletas, sem comprovar todos os itens administrativos |
| [#12](https://github.com/JonathanBufon/directchannel/issues/12) P12 | Concluída | Apresentação e explicação das contribuições; comentários de atribuição e entrega | Não há evidência de execução de funcionalidades remotas |
| [#13](https://github.com/JonathanBufon/directchannel/issues/13) I01 | Aberta | Cadastro/login/logout/consulta da conta; rejeição sem credencial; senhas protegidas, limite de tentativas e ausência de segredos em logs; sem comentários | Não implementada; estrutura genérica de usuário não atende ao aceite |
| [#14](https://github.com/JonathanBufon/directchannel/issues/14) I02 | Aberta | Trocar login simulado por real, cadastrar, persistir sessão, exibir erro de credencial/rede e encerrar sessão remotamente; depende de #13; sem comentários | Somente armazenamento/formulário local existem; integração e T02 ausentes |
| [#15](https://github.com/JonathanBufon/directchannel/issues/15) I03 | Aberta | CRUD de usuários com autorização, telas T05/T16/T17, desativação lógica e perfil público; sem comentários | Modelo inicial e perfil local não atendem ao CRUD |
| [#16](https://github.com/JonathanBufon/directchannel/issues/16) I04 | Aberta | CRUD de conversas, somente participantes; arquivar/sair sem mudar participantes de conversa privada; sem comentários | Ausente; atributos e significado da saída individual ainda sem regra completa |
| [#17](https://github.com/JonathanBufon/directchannel/issues/17) I05 | Aberta | Envio e histórico paginado; edição/exclusão só preparação documentada para futuro; sem comentários | Ausente; não exigir CRUD de mensagens completo no MVP |
| [#18](https://github.com/JonathanBufon/directchannel/issues/18) I06 | Aberta | CRUD de grupos e telas de membros/saída; só responsável altera nome/membros; saída condicionada a RF28; sem comentários | Ausente; política de saída ainda não decidida |
| [#19](https://github.com/JonathanBufon/directchannel/issues/19) I07 | Aberta | CRUD de anexos, armazenamento privado, download por participação e tamanho limitado; sem comentários | Ausente; não confundir avatar local com anexos de conversa |
| [#20](https://github.com/JonathanBufon/directchannel/issues/20) I08 | Aberta | CRUD de denúncias, estado administrativo, arquivamento lógico e ausência de mensagens privadas; sem comentários | Ausente; permissões detalhadas e estados não enumerados |
| [#21](https://github.com/JonathanBufon/directchannel/issues/21) I09 | Aberta | Mensagem no outro dispositivo sem recarregar, eventos de entrega/leitura e autorização por participação; depende de #16/#17; sem comentários | Ausente; notificação local não satisfaz tempo real |
| [#22](https://github.com/JonathanBufon/directchannel/issues/22) I10 | Aberta | Escolha criptográfica, proteção de mensagens/anexos, chaves privadas só locais e gatilho T20; sem comentários | Ausente; cofre de credencial não implementa proteção de mensagens ou gestão de chaves |
| [#23](https://github.com/JonathanBufon/directchannel/issues/23) I11 | Aberta | Papel administrativo, A01–A06, restrição de acesso e registro de configurações; depende de #15/#20; sem comentários | Ausente; campos genéricos de usuário não definem administrador |
| [#24](https://github.com/JonathanBufon/directchannel/issues/24) I12 | Aberta | Decidir T20/T17/T19, aceitar/rejeitar RF28–RF30 e ajustes RNF; exige decisão em comentário e atualização documental; sem comentários | Não existe decisão registrada; propostas permanecem propostas |

Eventos de fechamento consultados: #2 e #3 foram fechadas em 16/10/2026; as demais P01–P12, em 03/11/2026. Comentários de 03/11 relacionam a conclusão à entrega documental de 10/09, não a entrega de funcionalidades. O evento de referência de #2/#3 aponta ao commit local `d2b3859`, que organiza a documentação. Histórico de código inclui `1db7d65` (formulário/login inicial), `903ef43` (persistência, hardware e perfil), `9492b22` (reorganização e recriação do backend) e `d2b3859` (documentação/automação). Nada disso demonstra os critérios das issues remotas implementados na revisão inspecionada.

## Fontes documentais e prioridades de interpretação

1. `docs/directchannel-projeto-ux-ui.pdf`, pp. 3–4: finalidade, perfis, escopo e premissa de dispositivo. Pp. 35–37: RF01–RF27; pp. 38–39: RNF01–RNF20; p. 40: rastreabilidade. Interfaces T01–T24/A01–A06 e estados nos wireframes/protótipo são referências de comportamento, não telas existentes no código.
2. `docs/FLUXO-TELAS.md`: índice e destinos, limitações T20/T17/T19 e responsável de grupo; confirma apenas T01/T16 como implementação parcial, a qual foi confrontada com o código.
3. `docs/REQUISITOS-AJUSTES.md`: marca explicitamente o caráter proposto de RF28/RF29, a condicionalidade de RF30 e a opção de RF31; não substitui automaticamente o PDF. Proposta RNF05/08/09/15/18 não é teste concluído.
4. `docs/CRUDS.md`: seis domínios, operações, exclusão lógica e exceção explícita de mensagens; contratos apresentados como proposta, não rotas reais.
5. `docs/ARQUITETURA.md`: entidades propostas e associação opcional grupo/conversa. Relações físicas e cardinalidades de anexos não foram fechadas.
6. `docs/IMPLEMENTACAO.md`, `docs/relatorio-tecnico-implementacao.pdf`, imagens em `docs/img/evidencias/`: corroboram demonstração local, sem comprovar autenticação real, mensageria ou criptografia.
7. `docs/PLANEJAMENTO.md`, `docs/GITHUB.md`, `docs/SETUP.md`, `mobile/readme.md`, `infra/README.md`, `README.md`: organização e execução planejadas não devem ser confundidas com produto funcional. `SETUP.md` menciona autenticação planejada, mas dependência e rotas não existem no código inspecionado.

Em divergências de implementação, prevalece a evidência do código para dizer o que existe. Para o comportamento desejado, preservam-se requisitos documentais e critérios específicos das issues, registrando propostas e conflitos sem resolvê-los por suposição.

## Lacunas a resolver antes de fechar a modelagem dos dados afetados

- D1/D2/D3 em `spec.md`: responsabilidade de grupo, dispositivos/autorizações e configuração/retenção; são decisões não aprovadas, agora consideradas apenas pelos efeitos nos dados.
- Navegação T17/T19 e caminho visual de T20 ficam fora do escopo de persistência; mudança de identidade de #22 tem efeitos nos dados públicos necessários. Busca de conversas permanece opcional, sem aprovar indexação de conteúdo privado.
- Autoridade para desativar conta, excluir/desativar grupo, editar/remover anexo e consultar denúncias; atributos editáveis da conversa, estados da denúncia e efeito de bloqueio administrativo em sessões.
- Regra de arquivar/sair de conversa individual, tratamento de histórico na exclusão de grupo, retenção final de conteúdo e registros; 25 MB, 30 dias e metas de qualidade continuam propostas.
- Inventário de dados persistentes e sua finalidade/acesso/retenção. A sessão padrão prevê endereço de rede e identificação do cliente; isso é estrutura genérica encontrada, não confirmação de coleta real nem aprovação de sua necessidade.
- Representação de entrega/leitura, identidade e ciclo de vida de chaves; decisão técnica criptográfica em #22 e vínculo grupo/conversa antes de qualquer futura implementação de persistência.

Essas lacunas não autorizam criar funções adicionais, dar acesso por padrão ou preencher política de dados com valores arbitrários.

## Verificação da infraestrutura e do esquema nesta revisão

Nova consulta somente de leitura por `gh issue list --state all --limit 1000 --json number,title,state,stateReason,body,comments,closedAt,url` e API paginada de issues/comentários confirmou 24 issues e 22 comentários. Os identificadores dos comentários correspondem aos já inventariados; #24 permanece sem decisão em comentário. Não houve escrita no GitHub. Não foram criados novos arquivos de coleta fora desta pasta nesta revisão.

| Componente | Constatação nos arquivos | Implicação para persistência |
| --- | --- | --- |
| `infra/compose.yaml` | Projeto `directchannel`; serviços `api` e `postgres`; PostgreSQL `postgres:17`; banco/usuário `directchannel`; host interno `postgres:5432`; sem publicação de porta PostgreSQL no host | Alvo SQL único declarado; não criar outro banco/Compose ou alterar serviços |
| Volumes | `db_data:/var/lib/postgresql/data` e bind mount `../backend:/var/www/html` | Dados PostgreSQL têm volume nomeado; arquivos locais do backend ficam na árvore montada, não automaticamente no volume SQL |
| API | Porta de host `API_PORT` (padrão 8010), porta interna 8000, depende da saúde de `postgres` | Nenhuma necessidade de mudar redes/portas para modelar dados |
| `backend/Dockerfile` | PHP 8.4 CLI, extensão `pdo_pgsql`, Composer 2, diretório `/var/www/html` | Compatibilidade com a stack existente; não instalar Boost, trocar framework ou reconstruir imagens para análise |
| `infra/scripts/bootstrap.sh` | Geração condicional de Laravel/Expo, preservando projetos já presentes | Script lido, não executado; não é script de carga SQL |
| Scripts SQL | Nenhum `.sql` encontrado no repositório examinado | A base declarada deriva das migrations; nenhum SQL foi executado |
| `backend/config/database.php` | Fallback `sqlite`; conexão `pgsql` configurável; Compose define `DB_CONNECTION=pgsql` | Configuração genérica não comprova SQLite em uso nem justifica segundo banco; não foram inspecionados segredos/ambiente em execução |
| Sessão/cache/fila | Padrão `database` em `config/session.php`, `cache.php` e `queue.php` | Preservar tabelas de suporte; não propor Redis por existir configuração genérica; overrides efetivos não comprovados |
| `backend/config/filesystems.php` | Disco local privado em `storage/app/private`; suporte S3 genérico | Requisitos SQL de anexo devem respeitar arquivos privados já previstos; não mover binário para SQL/S3 por inferência |
| Modelo `User` | Atributos nome/e-mail/senha, transformação da senha para hash; sem relações de domínio | Identificador público, estado, papel, exclusão lógica e vínculos são lacunas documentadas, não implementações existentes |

As três migrations declaram oito tabelas: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`. O framework também configura a tabela de controle `migrations`, cuja existência aplicada não foi verificada.

**Chaves e relacionamentos existentes**: `users.id`, `sessions.id`, `jobs.id`, `job_batches.id` e `failed_jobs.id` são chaves primárias; `password_reset_tokens.email` e `cache.key`/`cache_locks.key` também. `users.email` e `failed_jobs.uuid` têm unicidade. `sessions.user_id` é opcional e indexado, mas **não possui FK declarada**: o código usa `foreignId(...)->nullable()->index()`, sem `constrained()` ou declaração explícita de chave estrangeira. As três migrations não declaram outras FKs. Não confundir a estrutura de sessão web com a credencial de API prevista em #13.

**Lacunas em relação aos requisitos**: não existem tabelas/modelos de conversa/participação, mensagem, grupo/membro, anexo, denúncia, bloqueio ou credencial de API. A representação de estados de entrega/leitura, papel administrativo, responsabilidade e material criptográfico público também não está definida. Configuração e auditoria têm necessidades documentadas, mas sua persistência em SQL não deve ser presumida. Nome físico, desdobramento em tabelas e associação grupo/conversa ficam para o plano compatível com o projeto.

**Fronteira mobile/servidor**: `storage.ts` mantém credencial no cofre e perfil/preferências no armazenamento local; `ProfileScreen` conserva coordenadas em memória. `LoginScreen` usa identidade fixa `usr_1` e credencial simulada. Esses dados não fundamentam novo banco embarcado, migração de URI local para servidor, tabela GPS ou sincronização. A diferença entre identificador textual simulado no mobile e chave numérica do modelo SQL não autoriza mudar contratos ou trocar o tipo de `users.id`; compatibilidade de representação será analisada no plano sem assumir que o valor simulado é um identificador de produção.

**Questões não comprovadamente obsoletas**: não foi encontrado cancelamento formal de RF01–RF27. #1–#12 são trabalho documental encerrado; RF28–RF31 continuam propostas; edição/exclusão de mensagens permanece futura por #17. Adicionar Reverb/Redis, criar armazenamento externo ou modificar Compose está fora desta entrega, ainda que fontes mencionem essas evoluções. A restrição de preservação do usuário prevalece sobre executar ações de infraestrutura descritas nas issues.

**Proteção de arquivos nesta revisão**: a verificação de integridade usa hashes dos arquivos fora da especificação, sem acesso a contêineres, volumes ou dados. Referências coletadas antes da edição:

| Diretório preservado | Arquivos | SHA-256 agregado dos caminhos e conteúdos |
| --- | --- | --- |
| `infra/` | 4 | `6b612e0ea9abae0adb5d5469fc78b9d5f072246f49828354d5ba924508a68410` |
| `backend/` | 62 | `e796c6fec9a988841666d3f4525319a61a4af81da68472f831856d42382179f2` |
| `mobile/` | 49 | `bf9fe8dde8c57ab7b2454a8341f8b926039dff061697b7e778ccd908b6f6f6cb` |
| `.specify/` | 28 | `a79490b7e9fd42126c59fc058b2b919e43f8aa03f2aa5e542a430a939f41d5a5` |

Não foram executados Makefile, Docker/Compose, scripts de bootstrap, Artisan, SQL ou migrations. O verificador de caminhos do Spec Kit foi chamado uma única vez, em modo `--json --paths-only`, e apontou a especificação existente. Os hashes não são evidência do estado do banco em execução, apenas da preservação dos arquivos examinados.
