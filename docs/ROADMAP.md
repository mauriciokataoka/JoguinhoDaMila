*Afazeres/pendências do projeto (TODOs).*

## Roadmap
- [x] Confirmar se o nome da pasta é o nome do projeto (registrar no PRD/README).
- [x] Definir o destino do push (remote) — *pode adiar*.
- [x] Detalhar o objetivo no docs/PRD.md.
- [x] É um app? Se sim, criar src/.
- [x] Se for software: definir a stack.
- [x] Definir funcionalidades e regras de jogo (PRD).
- [x] Especificar fluxo servidor/cliente e tela de sala de espera.
- [ ] **BLOQUEADOR**: Decidir entre alternativa iOS (ex.: AltStore) ou migrar servidor para Android.
- [x] Atualizar PRD com arquitetura final iOS server + PWA e fluxo de partida.
- [x] Criar ADR de fallback de IP e reconexão manual no PWA.
- [x] Criar ADR de regras de sessão, ranking e identificação PWA.
- [ ] Implementar modal de inicialização offline com coração rosa claro, fundo rosa escuro e faixa diagonal.
- [ ] Criar protótipo inicial do app servidor e da PWA cliente.
- [x] Documentar regras de partida: limite de tentativas configurável, token por nickname, 10s por vez, desistência, palavras com espaços/hífens.

## Bloqueador crítico
- **Deploy iOS sem Apple Developer**: App servidor precisa ser definitivo (não pode expirar a cada 7 dias).
  - Opção A: Encontrar alternativa de deploy iOS (AltStore, código aberto, etc.).
  - Opção B: Migrar servidor de iOS para Android.
  - **Decisão aguardando para amanhã.**
  - Ver [ADR-004](docs/adr/004-deploy-ios.md) para detalhes.

## Planejamento imediato
- Retomar após decisão do bloqueador sobre iOS vs Android.
- Se iOS com alternativa: protótipo inicial do app servidor e da PWA cliente.
- Se Android: reavaliação da stack e reorganização do roadmap.


