*Afazeres/pendências do projeto (TODOs).*

## Roadmap
- [x] Confirmar se o nome da pasta é o nome do projeto (registrar no PRD/README).
- [x] Definir o destino do push (remote) — *pode adiar*.
- [x] Detalhar o objetivo no docs/PRD.md.
- [x] É um app? Se sim, criar src/.
- [x] Se for software: definir a stack.
- [x] Definir funcionalidades e regras de jogo (PRD).
- [x] Especificar fluxo servidor/cliente e tela de sala de espera.
- [x] ~~BLOQUEADOR iOS vs Android~~ — resolvido pelo pivô para cPanel + PWA + P2P (ADR-005, proposed).
- [x] ~~Criar ADR de fallback de IP~~ — ADR-002 superseded; HTTPS do cPanel elimina o fallback manual.
- [x] ~~Criar ADR de regras de sessão~~ — ADR-003 mantida (Accepted); atualizada para "uma letra por turno".
- [ ] **BLOQUEADOR**: Definir o modelo de rede do jogo (mesmo Wi-Fi sempre? cross-network com STUN/TURN?).
- [ ] Definir o modelo de acesso à sala: singleton (uma sala global) vs código de sala por Mila.
- [ ] Confirmar runtime do backend no cPanel (Node, PHP ou Python) — só precisa servir signaling HTTP + arquivos estáticos.
- [ ] Desenhar protocolo de signaling (rooms, ICE exchange, mensagens).
- [ ] Implementar modal de inicialização offline (coração rosa claro, fundo rosa escuro, faixa diagonal com "Joguinho da Mila").
- [ ] Stub do backend de signaling no cPanel (Hello World + endpoint de registro de peer).
- [ ] Stub da PWA (carrega, detecta `?mila`, conecta ao signaling, abre DataChannel).
- [ ] Integrar a forca: Mila escolhe palavra, fluxo de turno via DataChannel, validação, ranking.
- [x] Documentar regras de partida — ADR-003.

## Bloqueador crítico
- **Plataforma do servidor:** resolvido por ADR-005 (proposed) — cPanel shared + PWA + P2P, sem WebSocket.
- **Item aberto do ADR-005 que trava a implementação:** modelo de rede (mesmo Wi-Fi ou cross-network) — define se STUN-only basta ou se TURN será necessário.

## Planejamento imediato
- Assim que o modelo de rede for definido, iniciar:
  1. Protocolo de signaling (rooms, ICE exchange).
  2. Stub do backend (endpoint de registro).
  3. Stub da PWA (carrega + `?mila` + DataChannel).
- Modal de inicialização pode começar em paralelo (independe de rede).
