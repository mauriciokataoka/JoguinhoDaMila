*Histórico de versões. Formato Keep a Changelog (keepachangelog.com); versionamento SemVer (semver.org).*

# Changelog

## [Unreleased]
### Changed
- **Transporte (P2P → polling HTTP):** a iteração anterior propôs P2P via WebRTC DataChannel; reconsiderada a favor de **polling HTTP a 1s** entre cliente e servidor.
- **Parâmetro do host:** `?mila` (typo `?mmila` corrigido).
- **Multi-sala desde o início:** o servidor gerencia múltiplas salas desde o início (não singleton). Mila cria salas via `+` na barra de abas; pode mover clientes entre salas (`→`) ou banir (`X`).
- **Admin token rotacionável:** o valor de `?mila=<token>` vive em `config.php` no servidor. Rotação sem deploy — editar o arquivo invalida o token antigo. A PWA suporta digitar/colar o novo token e salvar em `localStorage`.

### Added
- ADR-005: cPanel shared + PWA + polling HTTP (accepted) — agora inclui multi-sala e admin token.
- **Parâmetro de nome (`?nome=<valor>`)** para clientes: pré-preenche o nickname no onboarding, permitindo que o adulto configure o nome de cada kid na instalação da PWA.
- **Mila é a admin:** a URL dela é `?mila=<token>` (sem `?nome=`). O nome "Mila" é o nome padrão da role (customizável em `config.php` via `$ADMIN_NAME` se necessário).
- **Mecanismo de entrada de clientes nas salas — regra "última sala":** Mila sempre começa com a sala 1; quando cria uma nova via `+`, ela vira a "última sala". Todo novo cliente que faz `POST /join` é auto-atribuído à sala mais recentemente criada. Os kids já conectados ficam onde estão; Mila pode movê-los com `→` se quiser.
- **Decisões de implementação:**
  - **JS da PWA:** vanilla (HTML/CSS/JS puro).
  - **Router PHP:** híbrido (`api.php` como entry point + `endpoints/*.php` para a lógica).
  - **Service worker:** incluído (`sw.js` com cache-first p/ shell, network-first p/ API). Atende o requisito de offline do [[DESIGN]].

### Accepted
- ADR-005 ratificada: polling HTTP a 1s entre cliente e servidor cPanel.
- **Runtime do backend:** PHP (única opção disponível no cPanel do projeto).
