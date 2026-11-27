# API DirectChannel no Postman

Importe `DirectChannel.postman_collection.json` (v2.1) e
`DirectChannel.local.postman_environment.json` em **Import** no Postman. Selecione o
ambiente **DirectChannel local**. Sua `baseUrl` é `http://localhost:8010/api`:
a porta local vem de `API_PORT` em `infra/.env`, com padrão **8010**. Ajuste ao seu
ambiente. A collection isolada tem o padrão solicitado `http://localhost:8020/api`;
o ambiente selecionado sobrescreve esse valor. Não inclua `/api` novamente nas requests.

## Executar no Collection Runner

1. Na pasta **Arquivos**, abra **Criar upload descartável** e **Upload para a mensagem**.
   Em Body → form-data → `file`, selecione um arquivo local não vazio nos dois requests.
   Pode ser um `.txt` pequeno de teste, enviado como `application/octet-stream`.
   O arquivo é apenas uma amostra de bytes; a aplicação real cifra o conteúdo no cliente.
   Autorize o acesso ao arquivo no diretório de trabalho do Postman/desktop agent.
2. Execute a **collection inteira**, com todos os requests selecionados, uma iteração,
   começando em **Registrar usuário A**. Não ordene os requests manualmente.
   Os scripts `pm.execution.setNextRequest` usam IDs estáveis para atravessar as pastas
   por dependência e encerrar em **Logout de A**. Esses saltos só valem no Runner;
   o botão Send executa apenas o request aberto.
3. O cenário registra A e B com e-mail/username gerados por `{{$timestamp}}`, salva os
   IDs/tokens e faz login como A. Depois consulta perfil, altera privacidade/senha,
   busca B, publica chaves, cria conversa, testa remoção de upload descartável,
   faz outro upload e envia mensagem com anexo. Consulta histórico/anexo/download,
   registra entrega/leitura como B, cria/renomeia grupo, remove/reinsere B,
   promove/rebaixa B, sai transferindo a responsabilidade e encerra o grupo como B.
   Bloqueia/desbloqueia B, cria/consulta denúncia, arquiva a conversa e faz logout.
4. Veja **Test Results**: todos os requests verificam o status. Operações futuras
   (PATCH/DELETE de mensagens/anexos) esperam **501**. As consultas incluem HEAD,
   também registrado automaticamente pelo Laravel. As variáveis ficam na collection;
   não crie variáveis de ambiente com os mesmos nomes dos IDs/tokens.

O fluxo termina antes das operações manuais e administrativas, independentemente da
posição visual das pastas. Uma nova execução cria novas contas; aguarde pelo menos
um segundo entre execuções para evitar colisão de timestamp. Os testes de status
não substituem os testes de autorização do backend. Os payloads `aGVsbG8=` são
base64 válido ilustrativo, sem representar um envelope criptográfico real.

**Operações manuais:** em **Senha**, envie a solicitação e leia o código no transporte
de e-mail configurado em `backend/.env` (por exemplo, logs quando `MAIL_MAILER=log`).
Defina `resetCode` com os seis dígitos reais, ainda válidos, antes de redefinir.
`123456` é somente exemplo de formato. O código nunca é devolvido pela API.
A redefinição revoga as sessões, portanto faça-a após o fluxo principal.
**Desativar conta (manual)** exige novo login após o logout e remove logicamente A.
O fluxo altera a senha de A de `Postman-local-123!` para `Postman-nova-123!`;
use a nova senha no login manual. B mantém `Postman-local-123!`. Não execute a
pasta Auth isolada no Runner: os saltos dependem da seleção da collection inteira.

## Obter adminToken e executar Admin

`backend/database/seeders/DatabaseSeeder.php` cria `admin@example.com`, username
`admin`, via `User::factory()->admin()`. A senha vem de
`backend/database/factories/UserFactory.php`: **password** apenas em `APP_ENV=local`;
fora de local é aleatória. O seeder não declara outra senha fixa.

Em um banco local ainda sem seed, execute `php artisan db:seed --force` no container
com `APP_ENV=local` e o banco correto. O seeder não é idempotente: repetir pode
causar conflito de e-mail/username. Não é necessário apagar o banco para usar a
collection. Para esta unidade, todo comando usa exclusivamente `dc_test_fix`.

Envie manualmente `POST {{baseUrl}}/login` com **No Auth** e JSON:

```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "Postman Admin"
}
```

Copie `data.token` para a variável **de collection** `adminToken`, sem substituir
`token`. Pode duplicar o login em uma aba temporária sem os scripts de A. Execute
somente a pasta **Admin**, depois do fluxo principal, que preenche `otherUserId` e
`reportId`. A pasta usa Bearer `{{adminToken}}`, consulta painel/contas/configurações,
bloqueia/desbloqueia B, consulta/conclui/arquiva a denúncia. Para repetir Admin,
crie outra denúncia: concluir uma já concluída retorna 409. O administrador não
obtém acesso ao conteúdo cifrado de conversas/anexos.

## Verificação no Docker

A partir da raiz do worktree, sem PHP/Composer no host:

```bash
DC="docker run --rm -u 1000:1000 --network directchannel_default \
  -v $PWD/backend:/var/www/html -v $PWD:/repo -w /repo/backend \
  -v /home/jonathan/.cache/composer-dc:/tmp/composer \
  -e COMPOSER_HOME=/tmp/composer -e DB_CONNECTION=pgsql \
  -e DB_HOST=postgres -e DB_PORT=5432 -e DB_USERNAME=dc_test \
  -e DB_PASSWORD=dc_test -e DB_DATABASE=dc_test_fix directchannel-api"
$DC php artisan route:list --path=api --json
$DC php -r 'foreach (glob("../docs/postman/*.json") as $file) { json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR); echo "$file OK\n"; }'
$DC php artisan test --filter=PostmanCollectionTest
$DC php artisan test
$DC vendor/bin/pint --test
```

O teste lê `base_path('../docs/postman/DirectChannel.postman_collection.json')`.
Com `-v "$PWD:/repo" -w /repo/backend`, `base_path()` é `/repo/backend` e o JSON
fica em `/repo/docs/postman`. Ele percorre pastas recursivamente, compara cada
**método + URI** registrado sob `api/` (incluindo HEAD) e normaliza parâmetros
Laravel `{param}`, variáveis Postman `{{var}}` e segmentos `:param` para o mesmo
marcador. Remove query strings, preservando segmentos estáticos e o método.

O `make test` padrão continua descobrindo o teste em `tests/Feature/Docs`, mas o
compose monta apenas `backend` em `/var/www/html`. Como `../docs` não fica
acessível, o teste usa `markTestSkipped` com instrução de montagem. O comando
acima com a raiz montada executa a verificação **sem skip**. Nenhuma alteração
no Makefile ou no compose é necessária.
