# ADR-005: cPanel shared + PWA + polling HTTP

*Status · Contexto · Decisão · Consequências*

## Status
Accepted

## Contexto
O app precisa ser instalável em todos os dispositivos das crianças (iOS e Android) e ter deploy definitivo. As alternativas avaliadas em ADR-004 (superseded) esbarraram em:
- iOS nativo: exige Apple Developer ($99/ano); indisponível.
- Android nativo: viável, mas exigiria refazer toda a UI do servidor.

Restrições e recursos atuais:
- **cPanel shared hosting** é o que está disponível.
- **HTTPS** é obrigatório para PWA instalável; cPanel entrega via Let's Encrypt.
- Mila acessa `https://joguinhodamila.kataoka.com.br/?mila` e vira **host** do jogo (escolhe a palavra, inicia/encerra, kick).
- Os outros clientes acessam a URL sem o parâmetro e entram como **jogadores**.
- Velocidade não é crítica; tolerância a latência (timer de turno é 10s).
- cPanel shared impõe limites (processos longos, WebSocket pode não estar disponível).

Iteração anterior propôs **P2P via WebRTC DataChannel**. O usuário explicitou a alternativa: *"se achar melhor, podemos fazer os dispositivos se comunicarem a cada segundo com o servidor para se atualizar"*. Para este caso (turnos de 10s, ~5 clientes, cPanel shared), polling HTTP é objetivamente mais simples e robusto que P2P:

| Critério | Polling (1Hz) | P2P (WebRTC) |
|---|---|---|
| Complexidade do cliente | baixa | alta (SDP, ICE, STUN) |
| Funciona em qualquer rede | sim | depende de STUN; NAT simétrico precisa TURN |
| Custo de infra | só cPanel | cPanel + (opcional) TURN |
| Mila como SPOF | não (estado no servidor) | sim |
| Latência de atualização | até 1s | sub-segundo |
| Carga do servidor | N req/s (baixa) | só signaling (menor) |

## Decisão

### 1. Host público
cPanel shared em `https://joguinhodamila.kataoka.com.br`:
- serve a PWA estática sobre HTTPS (Let's Encrypt);
- expõe uma **API HTTP** que é o **servidor de jogo** (mantém o estado autoritativo da partida);
- persiste ranking e histórico de partidas.

### 2. Transporte: polling HTTP (sem WebSocket, sem P2P)
- A cada **1 segundo**, cada cliente faz `GET /state?sala=…` para receber o estado atual (palavra mascarada, tentativas restantes, de quem é a vez, timer, lista de jogadores).
- Ações do cliente (sugerir letra, desistir, host iniciar/encerrar, kick) são `POST /action` com o token e o payload.
- 1s de latência é aceitável: o timer de turno é 10s e o jogo é por turnos.
- **Sem WebSocket, sem WebRTC, sem STUN/TURN.** Roda em qualquer rede.

### 3. Modelo de papel (única PWA)
Mesma PWA para Mila e crianças. O papel é detectado pelo **parâmetro `?mila`** na URL:
- Aberta **com** `?mila` → papel de **host** (escolhe a palavra, inicia, kick, encerra).
- Aberta **sem** o parâmetro → papel de **jogador** (fornece nickname na primeira vez; entra na sala ativa).

### 4. Estado e autoridade
- **Servidor cPanel** é a **fonte autoritativa** do estado da partida.
- O host (Mila) também é cliente e se comunica com o servidor via polling; não é ponto único de falha.
- Token identifica o jogador entre requests; o servidor mantém o estado da sala em memória + persistência periódica.

### 5. Acesso à sala
- **Pendente:** decidir se a sala é singleton (uma sala global) ou se cada Mila gera um código e compartilha `?sala=XYZ`.
- Decisão a tomar antes do stub da PWA.

### 6. Regras de jogo
- Forca clássica: cada jogador, na sua vez, **sugere uma única letra**.
- Demais regras (timer 10s, desistência, espaços/hífens, tentativas, ranking) seguem [[ADR-003]].

## Consequências
- **Prós:**
  - Sem custo de Apple Developer; cPanel é o que já existe.
  - PWA instalável em iOS e Android.
  - **Sem WebSocket, sem WebRTC, sem STUN/TURN** — funciona em qualquer rede (Wi-Fi, 4G, hotspot).
  - Backend é o servidor de jogo: simples de raciocinar, fácil de testar.
  - Mila não é ponto único de falha: se o dispositivo dela cair, a partida continua para os outros.
  - Carga do servidor: ~N req/s (N = número de clientes). Para 5 clientes, 5 req/s — desprezível.
- **Contras:**
  - Latência fixa de até 1s para mudanças de estado.
  - Polling contínuo gasta bateria/tráfego (mitigável: pausar quando a aba está em background via `visibilitychange`).
  - Estado autoritativo no servidor exige persistência e cuidado com concorrência (mais código de servidor que a versão P2P).

## Pendente de decisão
- **Modelo de acesso à sala:** singleton (uma sala global) ou código de sala por Mila.
- **Runtime do backend no cPanel:** Node, PHP ou Python — a confirmar com a hospedagem.
