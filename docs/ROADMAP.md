*Afazeres/pendências do projeto (TODOs).*

## Roadmap
- [x] Confirmar se o nome da pasta é o nome do projeto (registrar no PRD/README).
- [x] Definir o destino do push (remote) — *pode adiar*.
- [x] Detalhar o objetivo no docs/PRD.md.
- [x] É um app? Se sim, criar src/.
- [x] Se for software: definir a stack.
- [x] Definir funcionalidades e regras de jogo (PRD).
- [x] Especificar fluxo servidor/cliente e tela de sala de espera.
- [x] ~~BLOQUEADOR iOS vs Android~~ — resolvido pelo pivô para cPanel + PWA + polling (ADR-005, proposed).
- [x] ~~Criar ADR de fallback de IP~~ — ADR-002 superseded; HTTPS do cPanel elimina o fallback manual.
- [x] ~~Criar ADR de regras de sessão~~ — ADR-003 mantida (Accepted); atualizada para "uma letra por turno".
- [x] ~~Modelo de rede (mesmo Wi-Fi vs cross-network)~~ — irrelevante: polling HTTP funciona em qualquer rede.
- [ ] Definir o modelo de acesso à sala: singleton (uma sala global) vs código de sala por Mila.
- [ ] Confirmar runtime do backend no cPanel (Node, PHP ou Python).
- [ ] Desenhar protocolo de API (rotas `/state`, `/action`, payloads, versionamento).
- [ ] Implementar modal de inicialização offline (coração rosa claro, fundo rosa escuro, faixa diagonal com "Joguinho da Mila").
- [ ] Stub do backend de jogo no cPanel (Hello World + endpoints básicos).
- [ ] Stub da PWA (carrega, detecta `?mila`, faz polling, mostra estado).
- [ ] Integrar a forca: Mila escolhe palavra, fluxo de turno via polling, validação, ranking.
- [x] Documentar regras de partida — ADR-003.

## Bloqueador crítico
- **Plataforma do servidor:** resolvida (ADR-005 Accepted) — cPanel shared + PWA + polling HTTP.
- **Sem bloqueador arquitetural** no momento. Itens abertos (modelo de sala, runtime, protocolo) são escolhas de implementação que podem ser feitas em paralelo com a codificação.

## Planejamento imediato
- Decidir (1) modelo de acesso à sala e (2) runtime do backend antes de iniciar o stub.
- Em paralelo: começar o modal de inicialização (independe de rede/servidor).
- Stub do backend + stub da PWA depois das duas decisões acima.
