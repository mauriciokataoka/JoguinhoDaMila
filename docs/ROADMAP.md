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
- [x] ~~Definir o mecanismo de atribuição de clientes a salas~~ — **regra "última sala"**: todo novo cliente entra na sala mais recentemente criada; Mila começa com a sala 1 e cria mais via `+`.
- [x] ~~JS da PWA~~ — **vanilla** (HTML/CSS/JS puro).
- [x] ~~Router PHP~~ — **opção C (híbrido)**: `api.php` como entry point + `endpoints/*.php` para a lógica.
- [x] ~~Service worker~~ — **incluído**: `sw.js` com cache-first p/ shell e network-first p/ API.
- [x] **v0.1.0** — implementação inicial completa: backend PHP + PWA vanilla (release 0.1.0 em 2026-07-22).

## Refinamentos pendentes (não-bloqueantes)
- [ ] Trocar `$ADMIN_TOKEN` em `config.php` para um valor seguro antes do deploy de produção.
- [ ] Gerar ícones PNG (192x192, 512x512) e atualizar `manifest.json` (atualmente só SVG).
- [ ] Pausar polling quando a aba está em background (`visibilitychange`) — economizar bateria.
- [ ] Adicionar `ETag` / `If-None-Match` para 304 quando o estado não mudou.
- [ ] Adicionar testes automatizados (PHP unit + JS smoke).
- [ ] CI/CD para deploy automático no cPanel.
- [ ] Animações e polimento visual (transições, feedback de acerto/erro).
- [ ] Documentar processo de rotação do admin token no `README` (quando, como, por quê).

## Próximos marcos
- **v0.2.0:** deploy no cPanel de produção; teste em dispositivos reais; feedback da Mila.
- **v0.3.0:** refinamentos visuais e de UX.
- **v1.0.0:** estável para uso regular.
