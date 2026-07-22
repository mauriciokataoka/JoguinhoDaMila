*Arquitetura técnica: componentes, dados, integrações. Otimizado p/ agente.*

## Architecture

### Componentes
- **PWA cliente** (browser/iOS/Android): UI do jogo, papel de host (Mila) ou jogadora, WebRTC DataChannel.
- **Backend de signaling** (cPanel shared): API HTTP simples para registro de peers e troca de ICE.
- **Persistência** (cPanel): ranking acumulado por nickname, histórico de partidas.

### Diagrama lógico
```
[ PWA Mila (host) ]──WebRTC DataChannel──[ PWA criança 1 ]
        │                                       │
        └──────────WebRTC DataChannel───────────┘
                          │
                  [ PWA criança 2 ]
                          │
        (todos se registram no backend de signaling)
                          │
              ┌───────────▼───────────┐
              │  Backend signaling      │  ← cPanel shared
              │  (rooms, ICE,           │     HTTPS
              │   /rooms/:id/peers)     │
              └─────────────────────────┘
```

### Stack candidato
- **Frontend (PWA):** HTML/CSS/JS vanilla ou framework leve (a decidir).
- **Backend (signaling):** Node, PHP ou Python — a confirmar com o cPanel.
- **Transporte do jogo:** WebRTC DataChannel; **sem WebSocket**, sem long-polling para o jogo.
- **Signaling:** HTTP request/response (REST simples).
- **STUN:** servidor público (ex.: `stun:stun.l.google.com:19302`); TURN postergado.
- **Persistência:** SQLite ou arquivo JSON no cPanel (depende do runtime).

### URLs e domínios
- Produção: `https://joguinhodamila.kataoka.com.br`
- Mila (host): `https://joguinhodamila.kataoka.com.br/?mila=1`
- Cliente: `https://joguinhodamila.kataoka.com.br/` (entra na sala ativa — a definir se singleton ou código)

### Estado e autoridade
- **Host (Mila)** é a fonte autoritativa do estado da partida (palavra, tentativas, turno, fim).
- Clientes replicam o estado via DataChannel; a ordem dos turnos é organizada pela própria P2P (a PWA da Mila anuncia "sua vez: <peer>" no canal; o peer responde com a letra; Mila valida e atualiza o estado).
- Se a P2P cair para um peer, ele re-registra no signaling e reabre o DataChannel; o estado é reenviado pelo host.

### Pendente
- Definir stack concreta do backend (Node/PHP/Python) conforme cPanel.
- Definir protocolo de signaling (mensagens, versionamento).
- Definir formato de token e sessão.
- Decidir modelo de acesso à sala (singleton vs código).
- Decidir modelo de rede (mesmo Wi-Fi ou cross-network) — afeta viabilidade de P2P.
