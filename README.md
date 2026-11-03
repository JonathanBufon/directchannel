# DirectChannel

Aplicativo mobile de mensageria para pessoas e pequenos grupos que priorizam a privacidade
de suas conversas e arquivos, com **criptografia de ponta a ponta desde o MVP**. O conteúdo
é cifrado antes de sair do dispositivo; servidor e administrador não possuem chaves de leitura.

| | |
|---|---|
| Disciplina | Desenvolvimento para dispositivos móveis |
| Turma | AX |
| Integrantes | Jonathan Bufon · Rafael Merisio Neto · Jaider Armando Marquina Miranda |

## Documentação

| Documento | Conteúdo |
|---|---|
| [Projeto UX/UI (PDF)](docs/directchannel-projeto-ux-ui.pdf) | Escopo, perfis, mapa de navegação, wireframes, protótipo, requisitos RF01–RF27 / RNF01–RNF20 |
| [Instalação e ambiente](docs/SETUP.md) | Tecnologias, primeira instalação, Docker, Expo e segurança |
| [Arquitetura](docs/ARQUITETURA.md) | Camadas mobile/API, persistência, tempo real e modelo de dados |
| [CRUDs](docs/CRUDS.md) | Divisão dos 6 CRUDs entre a equipe e contratos REST |
| [Colaboração no GitHub](docs/GITHUB.md) | Clonagem, branches e Pull Requests |
| [Mobile](mobile/readme.md) | Execução do app Expo e padrões (Atomic Design) |
| [Infra](infra/README.md) | Compose, variáveis de ambiente e scripts |

## Estrutura

```text
directchannel/
├── backend/   # API Laravel: app/Http, Models, Services, Policies, Events, database
├── mobile/    # App Expo + TypeScript: src/components, screens, navigation, services
├── infra/     # compose.yaml, .env.example e scripts/bootstrap.sh
└── docs/      # documento UX/UI, arquitetura, CRUDs, setup e colaboração
```

## Início rápido

```bash
cp infra/.env.example infra/.env          # defina DB_PASSWORD
./infra/scripts/bootstrap.sh              # gera Laravel/Expo se ainda não existirem
docker compose -f infra/compose.yaml up -d --build
cd mobile && npm install && npx expo start
```

Passo a passo completo em [docs/SETUP.md](docs/SETUP.md).

## Perfis

| Perfil | O que faz | Limite de acesso |
|---|---|---|
| Usuário comum | Conta, pesquisa, conversas, arquivos, grupos, privacidade, bloqueio e denúncia | Somente conversas e arquivos autorizados |
| Responsável pelo grupo | Cria grupo, altera nome, adiciona e remove participantes | Somente seu grupo |
| Administrador | Contas, bloqueios administrativos, denúncias e configurações | Não lê mensagens, anexos ou histórico |

**MVP:** cadastro e acesso, recuperação de senha, perfil, pesquisa, conversas individuais
e em grupo, mensagens em tempo real, histórico, entrega e leitura opcional, anexos,
verificação de contato, bloqueio, denúncia e administração.

**Telas:** T01–T24 (fluxos do usuário) e A01–A06 (administração) — ver o
[documento UX/UI](docs/directchannel-projeto-ux-ui.pdf).

## Identidade visual

| Papel | Cor |
|---|---|
| Ação | `#087C80` |
| Texto | `#172E3B` |
| Proteção | `#E9F4F2` |
| Superfície | `#F8FAFA` |

## Organização do trabalho

Quadro: **[DirectChannel — GitHub Projects](https://github.com/users/JonathanBufon/projects/2)**

| Coluna | Significado |
|---|---|
| A Fazer | Cards descritos e prontos para começar |
| Em Andamento | Uma pessoa responsável executa o card |
| Em Revisão | Outro integrante verifica os critérios |
| Concluído | Entrega validada e evidência anexada |

Divisão dos CRUDs por integrante em [docs/CRUDS.md](docs/CRUDS.md); fluxo de branches e PRs em
[docs/GITHUB.md](docs/GITHUB.md).
