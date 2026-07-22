*Afazeres/pendências do projeto (TODOs).*

## Roadmap
- [x] Confirmar se o nome da pasta é o nome do projeto (registrar no PRD/README).
- [x] Definir o destino do push (remote) — *pode adiar*.
- [x] Detalhar o objetivo no docs/PRD.md.
- [x] É um app? Se sim, criar src/.
- [x] Se for software: definir a stack.
- [x] Definir funcionalidades e regras de jogo (PRD).
- [x] Especificar fluxo servidor/cliente e tela de sala de espera.
- [x] ~~BLOQUEADOR iOS vs Android~~ — resolvido pelo pivô para cPanel + PWA + polling (ADR-005, accepted).
- [x] ~~Criar ADR de fallback de IP~~ — ADR-002 superseded; HTTPS do cPanel elimina o fallback manual.
- [x] ~~Criar ADR de regras de sessão~~ — ADR-003 mantida (Accepted); atualizada para "uma letra por turno".
- [x] ~~Modelo de rede (mesmo Wi-Fi vs cross-network)~~ — irrelevante: polling HTTP funciona em qualquer rede.
- [x] ~~Runtime do backend~~ — PHP (única opção no cPanel).
- [x] ~~Modelo de acesso à sala (singleton vs código)~~ — **multi-sala desde o início** (decisão do usuário).
- [x] ~~Parâmetro do host~~ — `?mila=<token>`, com valor em `config.php` (rotacionável).
- [x] ~~Confirmar o padrão do install da Mila~~ — **Mila é a admin**: URL `?mila=<token>` (sem `?nome=`); nome exibido "Mila" por padrão (customizável em `config.php` via `$ADMIN_NAME`).
- [x] ~~Definir o mecanismo de atribuição de clientes a salas~~ — **regra "última sala"**: todo novo cliente entra na sala mais recentemente criada; Mila começa com a sala 1 e cria mais via `+`.
- [ ] Suporte a `?nome=<valor>` na PWA cliente (pré-preenchimento do nickname; configurado pelo adulto na instalação).
- [ ] Criar `src/` com estrutura inicial (pwa/, api/).
- [ ] Criar `config.php` com `$ADMIN_TOKEN` placeholder e rotação documentada.
- [ ] Implementar `/api/check-admin` (valida token).
- [ ] Implementar `/api/admin/state` e `/api/admin/action` (criar sala, mover, banir, iniciar).
- [ ] Implementar `/state` e `/action` para clientes (entrar na sala, polling, sugerir letra).
- [ ] Implementar persistência SQLite (ranking, bans, histórico).
- [ ] Implementar modal de inicialização offline (coração rosa claro, fundo rosa escuro, faixa diagonal com "Joguinho da Mila").
- [ ] Implementar UI admin com abas (X, →, +) e UI cliente.
- [ ] Integrar a forca: Mila escolhe palavra, fluxo de turno, validação, ranking.

## Bloqueador crítico
- **Plataforma do servidor:** resolvida (ADR-005 Accepted) — cPanel shared (PHP) + PWA + polling HTTP + multi-sala + admin token rotacionável + regra "última sala".
- **Sem bloqueador arquitetural** restante. Todas as decisões de design foram tomadas; a partir daqui é implementação.

## Planejamento imediato
- Decidir o **mecanismo de atribuição de clientes a salas** antes de implementar o endpoint `/join`.
- Em paralelo: criar a estrutura `src/`, o `config.php`, o modal de inicialização e a UI admin (que não dependem do mecanismo de atribuição).
