*Arquitetura técnica: componentes, dados, integrações. Otimizado p/ agente.*

## Architecture

### Componentes
- **PWA cliente** (browser/iOS/Android): UI do jogo. Papel detectado pelo `?mila=<token>`:
  - Válido → UI admin (multi-sala com abas).
  - Inválido/ausente → UI cliente (uma sala).
- **Backend de jogo** (cPanel shared, PHP): API HTTP que mantém o estado autoritativo de **múltiplas salas**, valida ações, persiste ranking, valida o admin token.
- **Persistência** (cPanel, SQLite via PDO): ranking por nickname, histórico de partidas, log de bans.
- **Config** (`config.php`): valor do admin token (rotacionável).

### Diagrama lógico
```
       ┌──────────────┐
       │  PWA Mila     │  GET /api/admin/state (1s)  ┌──────────────────┐
       │  (admin)      │ ─────────────────────────►  │                  │
       └──────────────┘  POST /api/admin/action      │  Backend cPanel   │
                                                     │  (PHP)            │
       ┌──────────────┐  GET /state?sala=X (1s)     │  - estado das     │
       │  PWA cliente 1│ ─────────────────────────►  │    salas          │
       └──────────────┘  POST /action                │  - valida admin    │
                                                     │    token           │
       ┌──────────────┐  GET /state?sala=Y (1s)     │  - persiste SQLite │
       │  PWA cliente 2│ ─────────────────────────►  │                    │
       └──────────────┘  POST /action                │  HTTPS             │
                                                     └──────────────────┘
                            │
                       config.php
                     (admin token)
```

### Stack
- **Frontend (PWA):** HTML/CSS/JS vanilla ou framework leve (a decidir).
- **Backend (servidor de jogo):** **PHP** (única opção no cPanel).
- **Transporte:** HTTP request/response; **polling a 1s** pelos clientes e pelo admin.
- **Sem WebSocket, sem WebRTC, sem STUN/TURN.**
- **Persistência:** SQLite via PDO em PHP.
- **Config:** `config.php` com `$ADMIN_TOKEN`.

### URLs e domínios
- Produção: `https://joguinhodamila.kataoka.com.br`
- Admin (Mila): `https://joguinhodamila.kataoka.com.br/?mila=<token_admin>`
- Cliente: `https://joguinhodamila.kataoka.com.br/` (entra numa sala via mecanismo a definir)

### Admin token
- Valor em `config.php`: `<?php $ADMIN_TOKEN = 'valor-seguro'; ?>`.
- Rotação: editar o arquivo; sem deploy.
- Validação: endpoint `GET /api/check-admin?token=<value>` compara com `$ADMIN_TOKEN`.
- Se a PWA recebe token inválido, mostra campo para o usuário digitar/colar o novo (salva em `localStorage` para próximos loads).

### Estado e autoridade
- **Servidor cPanel** é a fonte autoritativa do estado de **todas as salas**.
- Estado por sala: palavra (mascarada para clientes, completa para o admin), tentativas restantes, jogador da vez, timer, lista de jogadores, status (aguardando / em jogo / encerrada).
- Mila (admin) também é cliente no sentido de fazer POSTs, mas com validação extra do admin token.
- A ordem dos turnos é responsabilidade do servidor; clientes e admin apenas reagem ao estado recebido via polling.
- Mila não é SPOF: se ela cair, os outros continuam vendo o estado e o turno passa normalmente.

### Multi-sala
- Servidor mantém um **mapa de salas** (ID → estado).
- IDs numéricos curtos (1, 2, 3...).
- Admin cria salas via `POST /api/admin/action {type: 'create_room'}`; recebe o ID da nova sala.
- Admin move cliente: `POST /api/admin/action {type: 'move_client', client_token: ..., to_room: ...}`.
- Admin bane: `POST /api/admin/action {type: 'ban', client_token: ...}`.
- Acesso de cliente a uma sala: **regra "última sala"** — `POST /join` sempre atribui à sala mais recentemente criada (a de maior ID, equivalente à aba mais à direita antes do `+`). Se a última sala está bloqueada/encerrada, o servidor rejeita o join (decisão de UX padrão; refinável).

### Otimizações pendentes
- Pausar polling quando a aba está em background (`visibilitychange`) — economizar bateria.
- `ETag` / `If-None-Match` para o servidor retornar 304 quando nada mudou — economizar banda.

### Pendente
- Definir o **mecanismo de atribuição de clientes a salas** (auto-assign vs pick-from-list vs code).
- Definir protocolo de API completo (rotas, payloads, versionamento).
- Definir formato de token de cliente e de admin.
- Decidir como fechar/encerrar uma sala pela UI admin (X na aba?).
