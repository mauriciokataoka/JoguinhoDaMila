# ADR-005: cPanel shared + PWA + P2P

*Status · Contexto · Decisão · Consequências*

## Status
Proposed

## Contexto
O app precisa ser instalável em todos os dispositivos das crianças (iOS e Android) e ter deploy definitivo. As alternativas avaliadas em ADR-004 (superseded) esbarraram em:
- iOS nativo: exige Apple Developer ($99/ano); indisponível.
- Android nativo: viável, mas exigiria refazer toda a UI do servidor.

Restrições e recursos atuais:
- **cPanel shared hosting** é o que está disponível.
- **HTTPS** é obrigatório para PWA instalável; cPanel entrega via Let's Encrypt.
- Mila acessa `https://joguinhodamila.kataoka.com.br/?mila=…` e vira host autoritativo.
- Clientes formam uma **rede P2P** entre si; identidade técnica = IP.
- Velocidade não é crítica; tolerância a latência.

Confirmações desta iteração:
- O cPanel **não precisa de WebSocket**; a signaling API pode ser HTTP puro. O turno de cada jogador, a validação e o estado da partida fluem **inteiramente via P2P** entre as PWAs.
- O jogo é uma **forca clássica**: cada jogador, na sua vez, dá **uma única letra** (não a palavra inteira).

## Decisão

### 1. Host público
cPanel shared em `https://joguinhodamila.kataoka.com.br`:
- serve a PWA estática sobre HTTPS (Let's Encrypt);
- expõe uma **API HTTP de signaling** simples (registro de peers na sala, troca de ICE candidates);
- persiste ranking e histórico de partidas.

### 2. Signaling
- Protocolo: HTTP request/response (sem WebSocket, sem long-polling para o jogo em si).
- Mensagens: registro de peer na sala, listagem de peers, troca de ICE candidates.
- Roda no mesmo cPanel; runtime (Node, PHP ou Python) a confirmar com a hospedagem.

### 3. Cliente único (PWA)
Mesma PWA para Mila e crianças:
- Aberta **com** `?mila=…` → papel de **host autoritativo** (escolhe a palavra, inicia, kick, encerra).
- Aberta **sem** o parâmetro → papel de **jogadora** (fornece nickname na primeira vez; entra na sala ativa).

### 4. Transporte do jogo (P2P)
- **WebRTC DataChannel** entre todos os peers da sala.
- A PWA da Mila é a fonte **autoritativa** do estado (palavra, tentativas, turno, fim). Ela envia o estado aos pares pelo DataChannel.
- O turno é **organizado pela própria rede P2P**: a PWA da Mila anuncia "sua vez: \<peer\>" no canal; o peer responde com a letra; Mila valida e atualiza o estado.
- Identidade técnica na rede P2P: IP do cliente (visível aos pares via signaling).

### 5. STUN / TURN
- **STUN público** (ex.: `stun:stun.l.google.com:19302`) para atravessar NAT.
- **TURN postergado**; se P2P falhar em algum par (NAT simétrico), esse par fica fora até TURN ser provisionado.

### 6. Acesso à sala
- **Pendente:** decidir se a sala é singleton (uma sala por vez, controlada pela Mila ativa) ou se cada Mila gera um código e compartilha a URL `?sala=XYZ`.

### 7. Regras de jogo
- Forca clássica: cada jogador, na sua vez, **sugere uma única letra** (não a palavra inteira).
- Demais regras (timer 10s, desistência, espaços/hífens, tentativas, ranking) seguem [[ADR-003]].

## Consequências
- **Prós:**
  - Sem custo de Apple Developer; cPanel é o que já existe.
  - PWA instalável em iOS e Android.
  - Mesma base de código para cliente e host; Mila é só um papel.
  - Backend cPanel super leve — só signaling HTTP, sem WebSocket nem long-polling para o jogo.
  - Jogo funciona offline do ponto de vista das crianças (após o primeiro carregamento da PWA).
- **Contras:**
  - P2P cross-LAN depende de STUN público; sem TURN, ~20% dos NATs simétricos podem falhar.
  - Mila é ponto único de falha: se o dispositivo da Mila cair, a partida acaba (até ela voltar e reconectar).
  - "IP" como identidade é frágil em CGNAT/mobile — pode precisar de ID mais robusto.
  - Estado autoritativo no host complica a lógica de "quem pode reiniciar" e o tratamento de reconexão da Mila.

## Pendente de decisão
- **Modelo de rede:** mesmo Wi-Fi (crianças reunidas) ou cross-network? — define se STUN-only basta ou se TURN será necessário.
- **Modelo de acesso à sala:** singleton (uma sala global) ou código de sala por Mila.
- **Runtime do backend no cPanel:** Node, PHP ou Python — a confirmar com a hospedagem.
