*Arquitetura técnica: componentes, dados, integrações. Otimizado p/ agente.*

## Architecture

### Componentes
- **PWA cliente** (browser/iOS/Android): UI do jogo, papel de host (Mila) ou jogadora, faz polling de estado a cada 1s.
- **Backend de jogo** (cPanel shared): API HTTP que mantém o estado autoritativo da partida, valida ações, persiste ranking.
- **Persistência** (cPanel): ranking acumulado por nickname, histórico de partidas.

### Diagrama lógico
```
       ┌──────────────┐
       │  PWA Mila     │  GET /state (1s)     ┌──────────────────┐
       │  (host)       │ ──────────────────►  │                  │
       └──────────────┘  POST /action         │  Backend cPanel   │
                                              │  (servidor de     │
       ┌──────────────┐  GET /state (1s)     │   jogo + estáticos│
       │  PWA criança 1│ ──────────────────►  │   da PWA)         │
       └──────────────┘  POST /action         │                  │
                                              │  HTTPS            │
       ┌──────────────┐  GET /state (1s)     │                  │
       │  PWA criança 2│ ──────────────────►  │                  │
       └──────────────┘  POST /action         └──────────────────┘
```

### Stack candidato
- **Frontend (PWA):** HTML/CSS/JS vanilla ou framework leve (a decidir).
- **Backend (servidor de jogo):** **PHP** (única opção disponível no cPanel do projeto).
- **Transporte:** HTTP request/response; **polling a 1s** pelos clientes.
- **Sem WebSocket, sem WebRTC, sem STUN/TURN.**
- **Persistência:** SQLite via PDO em PHP.

### URLs e domínios
- Produção: `https://joguinhodamila.kataoka.com.br`
- Mila (host): `https://joguinhodamila.kataoka.com.br/?mila`
- Cliente: `https://joguinhodamila.kataoka.com.br/` (entra na sala ativa — a definir se singleton ou código)

### Estado e autoridade
- **Servidor cPanel** é a fonte autoritativa do estado da partida (palavra, tentativas, turno, fim).
- Clientes fazem `GET /state` a cada 1s e enviam ações via `POST /action`.
- O host (Mila) também é cliente: suas ações (iniciar, encerrar, kick) são POSTs como os outros.
- A ordem dos turnos é responsabilidade do servidor; clientes apenas reagem ao estado recebido.
- Mila não é SPOF: se ela cair, os outros continuam vendo o estado e o turno passa normalmente.

### Otimizações pendentes
- Pausar polling quando a aba está em background (`visibilitychange`) — economizar bateria.
- `ETag` / `If-None-Match` para o servidor retornar 304 quando nada mudou — economizar banda.

### Pendente
- Definir protocolo de API (rotas, payloads, versionamento).
- Definir formato de token e sessão.
- Decidir modelo de acesso à sala (singleton vs código).
