# ADR-005: cPanel shared + PWA + polling HTTP (multi-sala, admin token)

*Status · Contexto · Decisão · Consequências*

## Status
Accepted

## Contexto
O app precisa ser instalável em todos os dispositivos das crianças (iOS e Android) e ter deploy definitivo. As alternativas avaliadas em ADR-004 (superseded) esbarraram em:
- iOS nativo: exige Apple Developer ($99/ano); indisponível.
- Android nativo: viável, mas exigiria refazer toda a UI do servidor.

Restrições e recursos atuais:
- **cPanel shared hosting** é o que está disponível, **apenas PHP** (sem Node.js nem Python).
- **HTTPS** é obrigatório para PWA instalável; cPanel entrega via Let's Encrypt.
- O **PWA é instalado uma vez** por um adulto em cada dispositivo e não muda depois — não pode depender de parâmetros variáveis por sessão.
- Mila é a **host/admin** do jogo; o restante das crianças são clientes.
- Velocidade não é crítica; tolerância a latência (timer de turno é 10s).
- cPanel shared impõe limites (processos longos, WebSocket pode não estar disponível).

Iteração anterior propôs **P2P via WebRTC DataChannel**. O usuário explicitou a alternativa: *"se achar melhor, podemos fazer os dispositivos se comunicarem a cada segundo com o servidor para se atualizar"*. Para este caso (turnos de 10s, ~5 clientes por sala, cPanel shared, PHP), polling HTTP é objetivamente mais simples e robusto que P2P.

O jogo é uma **forca clássica**: cada jogador, na sua vez, sugere **uma única letra**.

## Decisão

### 1. Host público
cPanel shared em `https://joguinhodamila.kataoka.com.br`:
- serve a PWA estática sobre HTTPS (Let's Encrypt);
- expõe uma **API HTTP em PHP** que é o **servidor de jogo** (mantém o estado autoritativo das salas e partidas);
- persiste ranking e histórico de partidas (SQLite via PDO);
- **Stack do backend:** **PHP** (única opção disponível no cPanel do projeto).

### 2. Transporte: polling HTTP (sem WebSocket, sem P2P)
- A cada **1 segundo**, cada cliente faz `GET /state?sala=…` para receber o estado atual (palavra mascarada, tentativas restantes, de quem é a vez, timer, lista de jogadores).
- **1s é o limite máximo de latência aceitável** para a jogabilidade parecer responsiva (regra do usuário: *"mais que isso, o jogo não será aceitavelmente responsivo"*). Não aumentar o intervalo.
- Ações do cliente (sugerir letra, desistir) e do admin (mover, banir, criar sala, iniciar) são `POST /action` com o token e o payload.
- O timer de turno de 10s dá margem se a polling oscilar; ainda assim, manter 1s como meta firme.

### 3. Modelo de papel (única PWA)
Mesma PWA para admin e clientes. O papel é detectado pela presença e validade do **parâmetro `?mila=<token>`**:
- `?mila=<token_válido>` → UI **admin** (Mila): multi-sala com abas, ações de mover/banir/criar sala/iniciar jogo.
- Sem `?mila` ou token inválido → UI **cliente**: entra numa sala, vê o estado, sugere letras.

### 4. Estado e autoridade
- **Servidor cPanel** é a **fonte autoritativa** do estado de **todas as salas e partidas**.
- Estado por sala: palavra (mascarada para clientes, completa para o admin), tentativas restantes, jogador da vez, timer, lista de jogadores, status (aguardando / em jogo / encerrada).
- Mila (admin) também é cliente no sentido de fazer POSTs, mas com validação extra do admin token.
- A ordem dos turnos é responsabilidade do servidor; clientes e admin apenas reagem ao estado recebido via polling.
- Mila não é SPOF: se ela cair, os outros continuam vendo o estado e o turno passa normalmente.

### 5. Admin token (autenticação do host)
- O valor do token vive em **`config.php`** no servidor (ex.: `<?php $ADMIN_TOKEN = 'valor-seguro-aqui'; ?>`).
- **Rotacionável** sem deploy: editar o arquivo; o token antigo para de funcionar imediatamente.
- A PWA valida via **`GET /api/check-admin?token=<value>`** no carregamento. O servidor compara com `$ADMIN_TOKEN`.
- **Justificativa da rotação:** como o polling expõe a URL na internet, qualquer um com a URL pode tentar o parâmetro `?mila`. Se o token vazar (print de tela, link compartilhado por engano), basta o administrador (adulto) editar `config.php` para girar o token. O install da PWA da Mila (que contém o token na URL) precisa ser refeito pelo adulto com o novo token — a PWA oferece um campo "token do admin" para o usuário digitar/colar e salvar em `localStorage`, evitando reinstalar.

### 6. Multi-sala
- O servidor gerencia **múltiplas salas desde o início** (decisão de projeto, não singleton — apesar do usuário ter inicialmente cogitado singleton, ele pediu para já criar a possibilidade de mais salas).
- Cada sala tem estado próprio e independente.
- **Identificação:** cada sala tem um **ID numérico curto** (ex.: `1`, `2`, `3`...). O ID é o que o servidor usa internamente e o que a UI mostra na aba.
- **Criação de sala:** a UI admin tem a **última aba como `+`**; clicar cria uma nova sala com o próximo ID disponível e a aba `+` permanece no final.
- **Mover cliente:** na lista de clientes de uma sala, o botão **→** move o cliente para a **próxima sala à direita** na barra de abas. Se o cliente está na última sala (antes do `+`), o `→` fica desabilitado.
- **Banir cliente:** o botão **X** remove o cliente do jogo (desconecta; token invalidado para esta partida; o nickname fica registrado no log de bans).
- **Iniciar jogo:** Mila escolhe a palavra e o limite de tentativas para a sala em que está, depois clica "Iniciar". A sala passa para o estado "em jogo".
- **Fechar/encerrar sala:** a definir (provavelmente um X na própria aba, fora da lista de clientes).

### 7. Acesso à sala pelo cliente
- **Pendente:** o mecanismo de atribuição de um cliente novo a uma sala. Opções:
  - **Auto-assign:** o servidor coloca o cliente na primeira sala com espaço (ou na "sala padrão" = sala 1).
  - **Pick-from-list:** o cliente vê a lista de salas existentes e escolhe uma.
  - **Code:** Mila compartilha um código/URL da sala (ex.: `?sala=2`); o cliente entra com o código.
  - Decisão a tomar antes do stub da PWA cliente.

### 8. Regras de jogo
- Forca clássica: cada jogador, na sua vez, **sugere uma única letra**.
- Demais regras (timer 10s, desistência, espaços/hífens, tentativas, ranking) seguem [[ADR-003]].

## Consequências
- **Prós:**
  - Sem custo de Apple Developer; cPanel é o que já existe.
  - PWA instalável em iOS e Android.
  - **Sem WebSocket, sem WebRTC, sem STUN/TURN** — funciona em qualquer rede.
  - Backend é o servidor de jogo: simples de raciocinar, fácil de testar.
  - Mila não é SPOF: se o dispositivo dela cair, a partida continua para os outros.
  - **Multi-sala desde o início:** o adulto pode organizar várias crianças em jogos paralelos sem retrabalho.
  - **Admin token rotacionável:** segurança operacional sem precisar de deploy.
- **Contras:**
  - Latência fixa de até 1s para mudanças de estado.
  - Polling contínuo gasta bateria/tráfego (mitigável: pausar quando a aba está em background via `visibilitychange`).
  - Estado autoritativo no servidor exige persistência e cuidado com concorrência.
  - **Multi-sala** adiciona complexidade ao estado do servidor (mapa de salas, não uma sala única).
  - **Rotação do admin token** exige reinstalar a PWA da Mila (ou um fluxo de "atualizar token" dentro da PWA via campo + `localStorage`).
  - URL com `?mila=<token>` é, na prática, um *bearer token* na URL — se alguém printar a tela, o token vaza. Mitigação: rotação + a PWA pode ofuscar o token na URL após validar (mas isso quebra o reload).

## Pendente de decisão
- **Mecanismo de atribuição de clientes a salas:** auto-assign vs pick-from-list vs code.
