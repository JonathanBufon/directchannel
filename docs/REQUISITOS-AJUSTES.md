# Proposta de ajustes nos requisitos

> **Origem:** anexo de @Rafaelmerisioneto na issue [#2 — P02 Revisar RF e RNF](https://github.com/JonathanBufon/directchannel/issues/2). Revisor previsto: Jaider Marquina.
> **Status:** proposta de revisão — ainda não incorporada ao [documento UX/UI](directchannel-projeto-ux-ui.pdf).


**Documento de referência:** *Trabalho Moretto Direct Channel.pdf*, 44 páginas. As páginas abaixo correspondem à numeração impressa no próprio trabalho.  
**Objetivo:** registrar alterações sugeridas para alinhar problema, requisitos, critérios de aceitação e telas. Este arquivo é uma proposta de revisão, não uma transcrição nem uma substituição automática da especificação original.

## 1. Problema e critério de revisão

O DirectChannel se propõe a permitir troca de mensagens e arquivos em tempo real sem dar ao operador acesso ao conteúdo privado. O trabalho apresenta criptografia de ponta a ponta, ausência de chaves de leitura no servidor, administração limitada e coleta mínima de dados como partes centrais da proposta. **Referência:** seção **1. Descrição do aplicativo**, p. 3.

Os RF01–RF27 e RNF01–RNF20 já cobrem o núcleo do produto. As sugestões abaixo formalizam regras que aparecem nas telas, tornam algumas verificações mais precisas e evitam sobreposição entre requisitos. Na redação sugerida, **RF** descreve a ação observável; **RNF** estabelece a propriedade de segurança, desempenho ou qualidade aplicável a ela. Um RF pode mencionar proteção no critério de aceitação quando isso é necessário para que a função seja segura.

## 2. Alterações prioritárias

### 2.1. Formalizar a transferência de responsabilidade do grupo — novo RF28

**Situação no trabalho.** A tela T24 diz que o único responsável deve definir outra pessoa antes de sair; RF18 cobre saída e atualização das chaves, enquanto RF15 trata de participantes e nome, sem oferecer transferência. T15 não tem ação visível para designar responsável. **Referências:** protótipo **T14/T15**, p. 26; protótipo **T24**, p. 31; **RF15 e RF18**, p. 36; fluxo de grupos, p. 40.

> **RF28 — Transferir responsabilidade na saída do líder.** Quando o líder do grupo solicitar a saída e sua saída deixaria o grupo sem responsável, o sistema deve exigir que ele escolha outro participante ativo para assumir a responsabilidade antes de concluir a saída.

**Critérios de aceitação propostos:**

1. A saída de um participante comum é concluída conforme RF18, sem exigir nem mostrar a escolha de novo líder.
2. Ao solicitar a saída do único líder, o sistema exige que ele escolha um participante ativo do mesmo grupo e confirme a transferência antes de concluir a saída.
3. Após a confirmação, o participante escolhido assume as permissões de líder e o líder anterior deixa o grupo; a saída não pode deixar o grupo sem responsável.
4. Cancelar preserva a participação e as permissões anteriores. Se não houver outro participante elegível, a saída fica bloqueada até que exista alguém a quem transferir a responsabilidade; a exclusão do grupo exigiria uma regra separada.

**Ajustes relacionados:** mostrar a escolha do sucessor somente no fluxo de saída do líder, em **T24**, aproveitando a lista de participantes de **T15** se necessário. Não oferecer a participantes comuns a ação “Tornar responsável”. Relacionar o novo RF a T14, T15 (se reutilizada), T24 e RF18, e incluir RF28 na linha **“Grupo e permissões”** da seção 5.2, p. 40. A proteção das mensagens após a mudança continua sujeita a RNF04 e RNF16.

### 2.2. Formalizar a edição das configurações operacionais — novo RF29

**Situação no trabalho.** A06 mostra campos para tamanho máximo de arquivo e retenção de logs, além de “Salvar configuração”. RF21 apenas disponibiliza o painel e as configurações ao administrador. O texto de A06 exige registro das mudanças. **Referências:** wireframe **A06**, p. 17; protótipo **A06**, p. 34; **RF21**, p. 37; **RNF13 e RNF15**, p. 39.

> **RF29 — Alterar parâmetros operacionais.** O sistema deve permitir ao administrador consultar e alterar os parâmetros operacionais autorizados, incluindo limite de anexos e período de retenção de logs.

**Critérios de aceitação propostos:**

1. Apenas o administrador autenticado pode salvar alterações.
2. Valores fora dos intervalos definidos na especificação técnica são recusados com mensagem clara; valores válidos persistem e passam a ser aplicados.
3. Cada mudança registra autor, data e parâmetros alterados, sem conteúdo de conversas, senhas, tokens ou chaves privadas.
4. Não existe ação capaz de desligar a criptografia de ponta a ponta.

**Ajustes relacionados:** associar A06 a RF21 **e RF29**; relacionar RF29 a RNF13–RNF15; incluir **A01 → A06 → A01** e RF29 na linha **“Administração”** da seção 5.2, p. 40. Enquanto o limite for configurável, os **25 MB** mostrados na tela são o valor inicial proposto, sujeito à validação do grupo, e a interface do usuário deve sempre mostrar o limite efetivamente vigente.

### 2.3. Especificar a premissa de um dispositivo ativo — novo RF30, após decisão do grupo

**Situação no trabalho.** A seção de escopo chama “um dispositivo ativo por conta no MVP” de *premissa a validar* e coloca múltiplos dispositivos fora do escopo inicial. Isso afeta login, sessões, chaves e recuperação de acesso. **Referência:** seção **1.1 Perfis e escopo desta fase**, p. 4; **RF02, RF03**, p. 35; **RF27**, p. 37; **RNF16 e RNF19**, p. 39.

**Se a premissa for confirmada**, adicionar:

> **RF30 — Controlar dispositivo ativo.** No MVP, o sistema deve manter no máximo um dispositivo ativo por conta. Ao autorizar um novo dispositivo, deve invalidar a sessão anterior e informar ao usuário que a troca não recupera automaticamente as chaves e o histórico existentes apenas no dispositivo anterior.

**Critérios de aceitação propostos:** após o novo login autorizado, a sessão anterior não realiza novas operações autenticadas; o aviso sobre histórico/chaves aparece antes da conclusão da troca; o novo dispositivo não recebe chaves privadas do servidor.

**Decisão pendente:** estabelecer como o usuário autoriza a troca e o que ocorre caso ainda tenha acesso ao dispositivo anterior. Se a premissa for rejeitada, não incluir RF30; documentar a política de dispositivos escolhida. Atualizar telas de acesso/recuperação e a rastreabilidade da seção 5.2 conforme a decisão. A capacidade de revogar sessões é um comportamento funcional; a guarda local de chaves continua regulada por RNF16 e RNF19.

## 3. Consistência entre requisitos e interface

### 3.1. Resolver a divergência da busca em T04

**Situação no trabalho.** T04 diz “Buscar pessoa ou conversa”; T05 procura usuários por identificador público e RF05 especifica apenas essa busca. **Referências:** wireframe **T04/T05**, p. 8; protótipo **T04**, p. 20; **RF05**, p. 35.

**Opção recomendada para manter o escopo atual:** mudar o texto de T04 para **“Buscar pessoa”** ou **“Buscar usuário”**, levando a T05. Não há necessidade de novo requisito.

**Se o grupo quiser pesquisar conversas existentes:** manter o texto e adicionar um RF separado, após decidir o comportamento da busca:

> **RF31 — Pesquisar conversas.** O sistema deve permitir ao usuário localizar conversas individuais e grupos dos quais participa por nome ou identificador visível na lista de conversas.

**Critério de aceitação:** o resultado inclui apenas conversas do próprio usuário; buscas sem resultado exibem estado vazio; o conteúdo das mensagens não é indexado no servidor como consequência desse RF. Relacionar RF31 a T04 e à linha **“Conversa privada e anexo”** da seção 5.2, p. 40. Se RF30 não for incluído, renumerar os novos requisitos sequencialmente.

### 3.2. Exibir o limite de anexos nas telas do usuário

**Situação no trabalho.** RNF13 propõe 25 MB, rejeição antes do envio e exibição do limite ao usuário; A06 exibe 25 MB, mas T07/T07G não mostram o limite vigente. O comportamento do protótipo já menciona a recusa do arquivo acima do limite. **Referências:** protótipo **T07/T07G**, p. 22; protótipo **A06**, p. 34; **RF12**, p. 36; **RNF13**, p. 39.

**Alteração sugerida em T07/T07G:** mostrar junto à seleção **“Tamanho máximo: 25 MB”** enquanto esse for o valor configurado; se o administrador alterar o limite, exibir o novo valor. Acrescentar um estado de erro, por exemplo: **“Este arquivo tem 31 MB. O limite atual é 25 MB.”**

**Critério de aceitação complementar a RF12/RNF13:** o aplicativo informa o limite aplicado antes da seleção/envio e rejeita localmente um arquivo que o exceda, sem iniciar o envio.

## 4. Melhorar a redação e a verificação de requisitos existentes

### 4.1. Distinguir ação e proteção criptográfica

**Situação no trabalho.** RF08, RF10, RF12, RF16, RF17 e RF18 misturam ações de usuário com termos de cifragem ou atualização de chaves. Essas garantias já aparecem em RNF04, RNF07, RNF16 e RNF17. **Referências:** requisitos funcionais, pp. 35–36; requisitos não funcionais, pp. 38–39; descrição do benefício, p. 3.

| Requisito | Redação funcional sugerida | Proteção mantida no critério e/ou RNF |
| --- | --- | --- |
| RF08 — Enviar mensagem | Permitir ao usuário enviar mensagens de texto aos participantes autorizados. | Não transmitir texto em claro; RNF01, RNF04 e RNF07. |
| RF10 — Histórico | Permitir ao participante consultar o histórico de conversas para as quais dispõe das chaves necessárias. | Histórico remoto permanece cifrado; RNF04, RNF09 e RNF19. |
| RF12 — Envio de arquivos | Permitir selecionar e enviar anexos aos participantes autorizados, exibindo o progresso. | Cifrar antes do upload, verificar integridade e respeitar o limite; RNF04, RNF07 e RNF13. |
| RF16 — Remover participantes | Permitir ao responsável remover participantes do grupo. | A remoção impede o acesso às mensagens posteriores; RNF03, RNF04 e RNF16. |
| RF17 — Conversar em grupo | Permitir o envio e o recebimento de mensagens entre os membros atuais do grupo. | Entrega protegida de ponta a ponta; RNF04 e RNF16. |
| RF18 — Sair do grupo | Permitir ao membro solicitar, confirmar ou cancelar sua saída do grupo. | Após a saída, impedir acesso às mensagens futuras; RNF03 e RNF16; observar RF28 para o único responsável. |

**Complemento sugerido para RNF04:** “Mensagens, anexos e histórico devem ser protegidos de ponta a ponta: o conteúdo é cifrado antes de deixar o dispositivo de origem e decifrado somente em dispositivos participantes autorizados; servidor e administrador não recebem as chaves privadas necessárias à leitura.” **Verificação planejada:** inspecionar os fluxos de envio, armazenamento e recebimento para confirmar que conteúdo e chaves privadas não aparecem em claro no servidor. Manter RNF07 para integridade e RNF16 para o ciclo de vida das chaves. A redação depende da solução criptográfica selecionada na fase técnica.

### 4.2. Tornar a meta de desempenho reproduzível — RNF05

**Situação no trabalho.** RNF05 **já** define 95% das mensagens online em até 2 s com 100 sessões simultâneas e solicita registrar carga, rede, tamanho e percentil. Falta apenas fechar a definição dos dois extremos do intervalo e as condições da rede de teste. **Referência:** **RNF05**, p. 38.

**Complemento sugerido à verificação planejada:** medir o intervalo entre a ação de enviar no dispositivo remetente e a apresentação da mensagem no dispositivo destinatário conectado; registrar quantidade e tamanho de mensagens, perfis dos dispositivos, RTT/perda de rede, configuração do servidor, duração do ensaio e percentil 95. Manter os valores como **metas propostas**, a validar antes da implementação.

### 4.3. Definir demonstração para escalabilidade — RNF08

**Situação no trabalho.** RNF08 prevê expansão independente de mensageria e armazenamento, mas sua verificação é apenas uma revisão futura do desenho técnico. **Referência:** **RNF08**, p. 38.

> **RNF08 — Escalabilidade, redação sugerida.** A arquitetura deve permitir dimensionar os serviços de mensageria e de armazenamento separadamente, sem exigir alteração do aplicativo cliente.

**Verificação planejada sugerida:** documentar a separação entre serviços e demonstrar, na fase técnica, que adicionar uma instância de mensageria não exige modificar o cliente ou aumentar a capacidade do armazenamento na mesma proporção. Nesta entrega de UX/UI, registrar esse teste como futuro.

### 4.4. Separar persistência de histórico e logs — RNF09/RNF15

**Situação no trabalho.** RNF09 reúne histórico cifrado e retenção de dados operacionais; RNF15 já define registros operacionais, exclusões de conteúdo sensível e retenção proposta de 30 dias. **Referências:** **RNF09**, p. 38; **RNF15**, p. 39.

> **RNF09 — Persistência do histórico, redação sugerida.** O histórico remoto deve permanecer cifrado; um participante que detenha as chaves necessárias deve poder consultá-lo após reabrir a sessão, respeitada a política de retenção do próprio histórico, se definida.

**Verificação de RNF09:** reabrir a sessão com as chaves disponíveis e consultar o histórico; sem essas chaves, confirmar que a cópia remota não permite leitura. **RNF15** permanece responsável pelos eventos operacionais, pela retenção inicial proposta de 30 dias e pelo descarte. Não presumir que o histórico cifrado tenha o mesmo prazo dos logs.

### 4.5. Tornar explícito o tratamento dos metadados — ampliar RNF18

**Situação no trabalho.** A descrição promete coleta mínima de dados, e RNF18 **já exige** inventário de finalidade, acesso e retenção de cada dado. Ele pode esclarecer que esse inventário também abrange metadados de funcionamento. Isso evita criar um requisito repetido. **Referências:** proposta de privacidade, p. 3; **RNF18**, p. 39; fluxos de denúncia e administração, pp. 37 e 40.

> **RNF18 — Dados mínimos, complemento sugerido.** O sistema deve coletar e persistir somente dados pessoais e metadados operacionais necessários às funções declaradas. O inventário deve registrar, para cada campo, finalidade, origem, local de armazenamento, quem pode acessá-lo e prazo de retenção. O conteúdo privado não deve ser usado para publicidade ou treinamento de modelos.

**Verificação planejada complementar:** confrontar inventário, esquema de armazenamento, registros de eventos e telemetria; remover ou justificar campos sem finalidade documentada. Explicitar, quando aplicável, identificadores, horários, endereços de rede, participantes e tamanho dos arquivos. **E2EE protege o conteúdo, mas essa redação não pressupõe anonimato**, coerentemente com o limite declarado na p. 3.

### 4.6. Precisar o efeito da redefinição de senha — RF27

**Situação no trabalho.** A premissa da p. 4 e a tela T03 informam que recuperar a senha sem as chaves originais não restaura o histórico. RF27 não permite recuperação das chaves pelo servidor, mas sua redação pode diferenciar a perda da senha da perda efetiva das chaves no dispositivo. **Referências:** seção 1.1, p. 4; **T03**, p. 20; **RF27**, p. 37; **RNF16 e RNF19**, p. 39.

> **RF27 — Recuperar acesso, redação sugerida.** O sistema deve permitir redefinir a credencial de acesso sem que o servidor recupere, reconstrua ou entregue as chaves privadas anteriormente pertencentes ao usuário.

**Critérios de aceitação propostos:** o fluxo não revela se o e-mail está cadastrado; quando as chaves originais continuam disponíveis em um dispositivo autorizado, a troca da credencial não implica automaticamente a perda do histórico; quando as chaves se perderam, o fluxo avisa que a redefinição não restaura o conteúdo cifrado. A regra final sobre chaves vinculadas à credencial ou ao dispositivo deve ser definida na fase técnica. Ajustar T03/T22 para refletir essa distinção.

## 5. Quadro de rastreabilidade a atualizar no trabalho

| Mudança | Local do trabalho | Atualização proposta |
| --- | --- | --- |
| RF28 — responsabilidade do grupo | T15 p. 26; T24 p. 31; RF15/RF18 p. 36 | Exigir escolha e confirmação do sucessor apenas na saída do único líder em T24; saída comum permanece no RF18. Incluir RF28 no quadro de RF e em “Grupo e permissões” (p. 40). |
| RF29 — parâmetros operacionais | A06 pp. 17 e 34; RF21 p. 37 | Associar A06 a RF29; incluir percurso A01 → A06 → A01 e RF29 em “Administração” (p. 40). |
| RF30 — dispositivo ativo, **condicional** | Escopo p. 4; RF02/RF03 p. 35; RF27 p. 37 | Validar premissa; se aprovada, adicionar requisito, fluxo de troca de dispositivo e vínculos aos RNF16/RNF19. |
| RF31 — pesquisa de conversas, **opcional** | T04 pp. 8 e 20; RF05 p. 35 | Preferencialmente ajustar rótulo de T04; se a funcionalidade for mantida, adicionar RF específico e rastreá-lo na p. 40. |
| Limite de anexos | T07/T07G p. 22; A06 p. 34; RNF13 p. 39 | Mostrar limite vigente, estado de erro e critério de recusa antes do envio. |
| RF08/RF10/RF12/RF16–RF18 | Tabelas de RF pp. 35–36; RNF04/RNF07/RNF16 pp. 38–39 | Revisar redações sem perder critérios de segurança nem relações entre RF e RNF. |
| RNF05/RNF08/RNF09/RNF15/RNF18 | Tabelas de RNF pp. 38–39 | Precisar verificações; separar retenção de logs e histórico; detalhar metadados dentro do RNF18. |
| RF27 | T03 p. 20; RF27 p. 37 | Distinguir senha redefinida de chaves perdidas no aviso e nos critérios. |

**Revisão final de consistência:** ao incorporar os requisitos aprovados, atualizar a contagem e os intervalos “RF01–RF27 / RNF01–RNF20” onde aparecerem, especialmente o card **P02** da seção 6, p. 42, e rever os vínculos e percursos da seção 5.2, p. 40. Não renumerar os RNF se apenas seus textos forem aperfeiçoados. Os testes técnicos propostos são critérios para a futura implementação; o PDF atual entrega protótipo e rastreabilidade, não evidência de execução criptográfica ou teste de carga.
