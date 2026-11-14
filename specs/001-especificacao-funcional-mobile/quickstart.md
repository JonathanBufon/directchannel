# Guia de revisão e validação documental — sem executar o banco

**Objetivo**: validar a coerência da proposta, não provar uma implementação inexistente. Pré-requisitos: acesso de leitura ao repositório, leitor Markdown com Mermaid opcional e Python 3 somente para verificações de arquivos. Não instalar pacotes, iniciar aplicação, acessar banco ou subir serviços.

## 1. Preparação segura

Trabalhar na raiz do repositório, conservando alterações locais já existentes. Não fazer reset, checkout destrutivo ou limpeza. Os comandos abaixo somente leem arquivos/estado Git:

```bash
git status --short
git diff --check
git diff --name-only
```

Arquivos da feature podem estar não rastreados; `git diff` não os valida integralmente. Abrir diretamente [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [physical-model.md](physical-model.md), [traceability.md](traceability.md) e [contracts/persistence-boundaries.md](contracts/persistence-boundaries.md). A especificação e seus cinco esclarecimentos continuam em [spec.md](spec.md).

**Não executar** nesta revisão: `make dev`, `make fresh`, `make migrate`, Composer `setup`/instalação, `artisan`, Docker/Compose, SQL, migrations, testes que gravem dados, seeds ou comandos de exclusão. Mecanismos de validação futura não são autorização para usá-los agora.

## 2. Verificação mínima reproduzível de documentos

Comando de leitura, sem geração de arquivos e sem acessar dependências da aplicação:

```bash
python3 - <<'PY'
from pathlib import Path
import re
p = Path('specs/001-especificacao-funcional-mobile')
names = ['plan.md', 'research.md', 'data-model.md', 'physical-model.md',
         'traceability.md', 'contracts/persistence-boundaries.md', 'quickstart.md']
for name in names:
    text = (p / name).read_text()
    assert text.strip(), name
    assert text.count('```') % 2 == 0, name
model = (p / 'data-model.md').read_text()
assert '```mermaid\nerDiagram' in model
trace = (p / 'traceability.md').read_text()
for n in range(1, 21):
    assert f'| DB-{n:03} |' in trace, n
print('7 artefatos presentes; blocos fechados; DER presente; 20 requisitos mapeados.')
PY
```

Resultado esperado: mensagem acima, sem arquivos novos, SQL ou alterações no Git. Isso não valida sintaxe completa Mermaid, constraints em PostgreSQL, compatibilidade do pacote ou desempenho. Renderização visual pode ser feita no leitor já disponível, sem instalar ferramentas ou enviar dados do projeto a serviços externos por inferência.

## 3. Cenários de validação por revisão e exemplos

Os exemplos são casos conceituais a percorrer nas tabelas e invariantes, **não INSERTs, fixtures ou testes executados**. Para futura implementação autorizada, serão convertidos em testes em ambiente aprovado; não criar agora uma suíte completa.

| Caso | Revisão a realizar | Resultado esperado e limite |
| --- | --- | --- |
| V01 — nomes equivalentes | Comparar contas hipotéticas `@Maria` e `@maria` | Não podem coexistir como contas diferentes; P-01 impede fingir resolvidas reutilização e equivalências internacionais |
| V02 — conta desativada | Seguir FKs de usuário referenciado por mensagem/denúncia | Desativação lógica não elimina autoria; RESTRICT candidato não autoriza purga |
| V03 — duas credenciais no mesmo contexto | Associar dois tokens ao mesmo grant em papel | Permitido; não há UNIQUE token ativo por usuário |
| V04 — dois contextos concorrentes | Comparar grants da mesma conta com `revoked_at` ausente | Índice parcial impede dois atuais; protocolo transacional deve revogar anterior e tornar seus tokens inutilizáveis |
| V05 — reentrada | Considerar dois períodos separados do mesmo par conta/conversa | Nova ocorrência permitida; índice parcial não é UNIQUE global; regra de períodos fechados exige coordenação |
| V06 — autoria em conversa errada | Tentar relacionar mensagem ao participante de outro contexto | FK composta candidata rejeita divergência; não basta FK separada do usuário |
| V07 — envio após saída | Usar participação encerrada no instante posterior | FK sozinha permite a referência histórica; invariante temporal precisa rejeitar nova escrita no uso futuro |
| V08 — saída do único responsável | Escolher membro ativo; comparar confirmação e cancelamento | Confirmar transfere e encerra vínculo anterior sem grupo sem responsável; sem elegível impedir; cancelar preserva ambos |
| V09 — sucessor removido concorrentemente | Intercalar saída e remoção do sucessor em papel | Mesmo protocolo de serialização por contexto evita decisão com membro já inelegível |
| V10 — mensagem e dois anexos | Relacionar dois anexos distintos a uma mensagem | 1:N; cada registro de anexo tem uma mensagem; nenhuma API de lote foi aprovada |
| V11 — upload ainda sem mensagem | Procurar regra de nulabilidade temporária | Identificar bloqueio P-03; não considerar o modelo físico dessa parte pronto |
| V12 — recibos de grupo | Examinar dois destinatários e preferência de leitura desativada | Recibos separados; nenhuma leitura desativada coletada para compartilhamento; reconciliar eventos é P-09 |
| V13 — denúncia e administração | Examinar campos e vínculos de `reports` | Sem FK de mensagem/anexo; motivo obrigatório; sem enum inventado; P-06 explícito |
| V14 — bloqueios | Comparar par pessoal A→B e ocorrência administrativa | Não são recíprocos nem confundidos com exclusão lógica; autor/motivo do administrativo identificáveis |
| V15 — parâmetros | Procurar tabelas de configuração/histórico de alteração dos dois parâmetros | Não existem na proposta; somente consulta; valores 25 MB/30 dias continuam propostas |
| V16 — protocolo privado | Procurar coluna de chave privada, texto de mensagem legível ou recuperação de segredo | Nenhuma; `public_key_material` mínimo não é schema criptográfico concluído e P-07 bloqueia implementação |
| V17 — contratos | Confrontar nomes/caminhos com inventário em contracts/ | Nenhuma nova rota, payload ou mudança de formato exigida pelo desenho |
| V18 — preservação | Comparar fontes, infraestrutura e artefatos antes/depois | Somente documentação da feature modificada; nenhuma alegação de schema aplicado |

## 4. Revisão de normalização e SQL físico

1. Seguir cada FK no dicionário; destino e tipo devem existir no mesmo modelo candidato, com ON DELETE/UPDATE documentados.
2. Conferir chaves compostas/associativas, referências internas de grupo e limites das FKs de tokens do pacote.
3. Verificar se as listas de campos do lógico e físico coincidem. Dados de protocolo/estado desconhecidos não podem aparecer como enum/default inventado.
4. Revisar a exceção 3FN de `messages.conversation_id`; aceitar explicitamente ou optar por derivação em join antes de migration.
5. Conferir propósito dos índices, inclusive índices de FKs, índices parciais e o que eles não garantem: período histórico, responsabilidade e revogação funcional não decorrem somente do índice.
6. Verificar que nenhuma ação CASCADE/SET NULL foi usada para resolver descarte sem política aprovada; as tabelas técnicas herdadas continuam intocadas.
7. Reconciliar P-01–P-09 com o responsável de negócio e com a decisão criptográfica/de pacote; somente então o gate de fechamento pode ser reavaliado.

## 5. Critério de aprovação e ponto de parada

A revisão documental conclui quando os 20 requisitos estão rastreados, o DER e o dicionário são coerentes e cada decisão candidata/bloqueio é visível. **Isso não equivale a aprovação integral ou implementação pronta.** O gate atual é ERROR para fechamento lógico/físico por pendências que pesquisa técnica não resolve.

Solicitar aprovação expressa da modelagem, distinguindo aprovação das convenções técnicas e resolução das regras de negócio pendentes. Não avançar automaticamente para implementação, geração de tasks, instalação de Sanctum, migrations ou execução do banco. Caso seja necessária outra rodada, usar `/speckit.clarify` para políticas e revisar estes artefatos; não reabrir silenciosamente as cinco respostas já aceitas.
