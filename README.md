# DirectChannel

Projeto de experiência e interface (UX/UI) de um aplicativo mobile de mensageria com
**criptografia de ponta a ponta desde o MVP**.

> **Escopo desta fase:** especificação e prototipação. Este repositório não contém um
> aplicativo implementado — o entregável é o documento de UX/UI, requisitos e o
> planejamento do trabalho.

## Entrega

| | |
|---|---|
| Disciplina | Desenvolvimento para dispositivos móveis |
| Turma | AX |
| Data de entrega | 10 de setembro de 2026 |

**Integrantes:** Jonathan Bufon · Rafael Merisio Neto · Jaider Armando Marquina Miranda

## Documento

[`docs/directchannel-projeto-ux-ui.pdf`](docs/directchannel-projeto-ux-ui.pdf) — 44 páginas,
com protótipo navegável por links internos.

1. Descrição do aplicativo, perfis e escopo
2. Mapa de navegação (usuário, grupos e administração)
3. Wireframes em baixa fidelidade
4. Interface, identidade visual e protótipo de alta fidelidade
5. Requisitos rastreáveis (RF01–RF27 / RNF01–RNF20) e avaliação dos fluxos
6. Organização do trabalho

## Visão geral

DirectChannel é um aplicativo de mensageria para pessoas e pequenos grupos que priorizam
a privacidade de suas conversas e arquivos. O conteúdo é cifrado antes de sair do
dispositivo; servidor e administrador não possuem chaves de leitura.

**Perfis**

| Perfil | O que faz | Limite de acesso |
|---|---|---|
| Usuário comum | Conta, pesquisa, conversas, arquivos, grupos, privacidade, bloqueio e denúncia | Somente conversas e arquivos autorizados |
| Responsável pelo grupo | Cria grupo, altera nome, adiciona e remove participantes | Somente seu grupo |
| Administrador | Contas, bloqueios administrativos, denúncias e configurações | Não lê mensagens, anexos ou histórico |

**MVP:** cadastro e acesso, recuperação de senha, perfil, pesquisa, conversas individuais
e em grupo, mensagens em tempo real, histórico, entrega e leitura opcional, anexos,
verificação de contato, bloqueio, denúncia e administração.

**Fora desta entrega:** implementação de código, backend, banco de dados, envio real de
mensagens e execução da criptografia. Chamadas, bots, integrações e múltiplos
dispositivos permanecem como evolução futura.

## Telas

- **T01–T24** — fluxos do usuário: acesso, conversas, arquivos, contato, grupos, perfil e preferências
- **A01–A06** — administração: painel, usuários, contas, denúncias e configurações

## Identidade visual

| Papel | Cor |
|---|---|
| Ação | `#087C80` |
| Texto | `#172E3B` |
| Proteção | `#E9F4F2` |
| Superfície | `#F8FAFA` |

## Organização do trabalho

Quadro: **[DirectChannel — UX/UI Turma AX](https://github.com/users/JonathanBufon/projects/2)**

Cada card P01–P12 corresponde a uma etapa do planejamento da seção 6 do documento e
carrega os campos obrigatórios: responsável, prazo, estado, critério de conclusão,
referência à tela ou requisito, e evidência do trabalho e da revisão.

| Coluna | Significado |
|---|---|
| A Fazer | Cards descritos e prontos para começar |
| Em Andamento | Uma pessoa responsável executa o card |
| Em Revisão | Outro integrante verifica os critérios |
| Concluído | Entrega validada e evidência anexada |

Cada integrante assume seus cards e registra autoria, comentários e revisões na própria
ferramenta — cargos genéricos e capturas isoladas não substituem nomes e histórico de
participação.
