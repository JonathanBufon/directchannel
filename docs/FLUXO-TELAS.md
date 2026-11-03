# Fluxo das telas — DirectChannel

Mapa de navegação validado na issue [#3 — P03 Validar mapa de navegação](https://github.com/JonathanBufon/directchannel/issues/3)
(responsável: Jaider Marquina · revisor: Jonathan Bufon). Wireframes e protótipo completos no
[documento UX/UI](directchannel-projeto-ux-ui.pdf).

## Índice de telas

### Usuário (T01–T24)

| Tela | Nome | Grupo de fluxo |
|---|---|---|
| T01 | Entrar | Acesso |
| T02 | Criar conta | Acesso |
| T03 | Recuperar acesso | Acesso |
| T04 | Conversas | Conversas |
| T05 | Buscar usuários | Conversas |
| T06 | Conversa (ex.: *Marina Costa*) | Conversas |
| T07 | Arquivo | Arquivos |
| T07G | Arquivo do grupo | Arquivos |
| T08 | Informações do contato | Contato |
| T09 | Verificar contato | Contato |
| T10 | Denunciar usuário | Contato |
| T11 | Grupos | Grupos |
| T12 | Criar grupo | Grupos |
| T13 | Conversa do grupo (ex.: *Equipe do projeto*) | Grupos |
| T14 | Informações do grupo | Grupos |
| T15 | Gerenciar grupo | Grupos |
| T16 | Perfil | Perfil |
| T17 | Editar perfil | Perfil |
| T18 | Privacidade | Perfil |
| T19 | Contatos bloqueados | Perfil |
| T20 | Código alterado | Contato |
| T21 | Bloquear contato | Contato |
| T22 | Redefinir senha | Acesso |
| T23 | Denúncia enviada | Contato |
| T24 | Sair do grupo | Grupos |

### Administração (A01–A06)

| Tela | Nome |
|---|---|
| A01 | Administração (painel) |
| A02 | Usuários |
| A03 | Gerenciar conta |
| A04 | Denúncias |
| A05 | Analisar denúncia |
| A06 | Configurações |

## Fluxos principais

| Fluxo | Caminho |
|---|---|
| Acesso e conversa | T01 → T04 → T05 → T06 → T07 |
| Recuperação de senha | T01 → T03 → T22 → T01 |
| Confiança no contato | T06 → T09 → T20 → T09 |
| Bloqueio e denúncia | T08 → T21 → T19 · T08 → T10 → T23 |
| Grupos | T11 → T12 → T13 → T14 → T15 / T24 |
| Perfil | T16 → T17 / T18 / T19 |
| Administração | A01 → A02 → A03 · A01 → A04 → A05 |

## Diagramas

### Acesso, conversas e contato

```mermaid
flowchart LR
  T01[T01 Entrar] -->|Criar conta| T02[T02 Criar conta]
  T01 -->|Esqueci minha senha| T03[T03 Recuperar acesso]
  T01 -->|Entrar| T04[T04 Conversas]
  T01 -->|Entrar como admin| A01[A01 Administração]
  T02 -->|Criar conta| T04
  T03 -->|Enviar instruções| T22[T22 Redefinir senha]
  T22 -->|Salvar nova senha| T01

  T04 -->|Buscar / Nova conversa| T05[T05 Buscar usuários]
  T04 -->|Contato| T06[T06 Conversa]
  T04 -->|Perfil| T16[T16 Perfil]
  T04 -->|Grupos| T11[T11 Grupos]
  T05 -->|Iniciar conversa| T06
  T05 -->|Contato| T08[T08 Informações do contato]

  T06 -->|Abrir arquivo| T07[T07 Arquivo]
  T07 -->|Enviar / Baixar| T06
  T06 -->|Verificar contato| T09[T09 Verificar contato]
  T06 -->|Info do contato| T08
  T09 -->|Código conferido| T06
  T20[T20 Código alterado] -.->|Verificar novo código| T09

  T08 -->|Enviar mensagem| T06
  T08 -->|Verificar código| T09
  T08 -->|Bloquear| T21[T21 Bloquear contato]
  T08 -->|Denunciar| T10[T10 Denunciar usuário]
  T21 -->|Confirmar| T19[T19 Contatos bloqueados]
  T21 -->|Cancelar| T08
  T10 -->|Enviar denúncia| T23[T23 Denúncia enviada]
  T10 -->|Cancelar| T08
  T23 -->|Voltar ao contato| T08
  T23 -->|Bloquear| T21
```

### Grupos

```mermaid
flowchart LR
  T11[T11 Grupos] -->|Grupo| T13[T13 Conversa do grupo]
  T11 -->|Criar grupo| T12[T12 Criar grupo]
  T12 -->|Criar grupo| T13
  T13 -->|Informações| T14[T14 Informações do grupo]
  T13 -->|Abrir arquivo| T07G[T07G Arquivo do grupo]
  T07G -->|Enviar / Baixar| T13
  T14 -->|Alterar nome e participantes| T15[T15 Gerenciar grupo]
  T15 -->|Salvar alterações| T14
  T14 -->|Sair do grupo| T24[T24 Sair do grupo]
  T24 -->|Confirmar saída| T11
  T24 -->|Continuar no grupo| T14
```

### Perfil

```mermaid
flowchart LR
  T16[T16 Perfil] -->|Editar perfil| T17[T17 Editar perfil]
  T16 -->|Privacidade| T18[T18 Privacidade]
  T16 -->|Contatos bloqueados| T19[T19 Contatos bloqueados]
  T16 -->|Sair da conta| T01[T01 Entrar]
  T17 -->|Salvar / Cancelar| T16
  T18 -->|Contatos bloqueados| T19
```

### Administração

```mermaid
flowchart LR
  A01[A01 Administração] -->|Gerenciar usuários| A02[A02 Usuários]
  A01 -->|Analisar denúncias| A04[A04 Denúncias]
  A01 -->|Configurações| A06[A06 Configurações]
  A01 -->|Sair| T01[T01 Entrar]
  A02 -->|Conta| A03[A03 Gerenciar conta]
  A03 -->|Confirmar bloqueio| A02
  A04 -->|Relato| A05[A05 Analisar denúncia]
  A05 -->|Concluir análise| A04
  A05 -->|Gerenciar conta denunciada| A03
  A06 -->|Salvar configuração| A01
```

> O administrador não acessa conteúdo de mensagens, anexos ou histórico em nenhuma tela A01–A06.

## Pendências encontradas na validação

| Item | Situação registrada na P03 | Encaminhamento sugerido |
|---|---|---|
| T20 Código alterado | Mesma navegação da T09 e **sem entrada** a partir de outra tela | É um estado disparado pelo sistema quando o código do contato muda (fluxo T06 → T09 → T20 → T09); documentar o gatilho no protótipo |
| T17 → Cancelar | Registrado como destino “T6” | Provável retorno a **T16 Perfil**; confirmar no protótipo |
| T19 → Desbloquear | Registrado como destino “T09*” | Definir se desbloquear permanece em T19 ou abre T09 para nova verificação |
| T14 / T24 (líder) | Saída do único responsável não tem transferência | Ver proposta **RF28** em [REQUISITOS-AJUSTES.md](REQUISITOS-AJUSTES.md) |

## Status de implementação

| Tela | Status | Evidência |
|---|---|---|
| T01 Entrar | Implementada (validação, sessão persistente) | [IMPLEMENTACAO.md](IMPLEMENTACAO.md#persistência-e-credenciais) |
| T16 Perfil | Implementada (avatar, cofre, GPS, notificação, logout) | [IMPLEMENTACAO.md](IMPLEMENTACAO.md#recursos-nativos) |
| Demais telas | Planejadas | [CRUDS.md](CRUDS.md) |
