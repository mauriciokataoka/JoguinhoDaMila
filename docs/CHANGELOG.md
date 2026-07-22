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

### Accepted
- ADR-005 ratificada: polling HTTP a 1s entre cliente e servidor cPanel.
- **Runtime do backend:** PHP (única opção disponível no cPanel do projeto).
