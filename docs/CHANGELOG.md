*Histórico de versões. Formato Keep a Changelog (keepachangelog.com); versionamento SemVer (semver.org).*

# Changelog

## [Unreleased]
### Changed
- **Transporte (P2P → polling HTTP):** a iteração anterior propôs P2P via WebRTC DataChannel; reconsiderada a favor de **polling HTTP a 1s** entre cliente e servidor. Mais simples, funciona em qualquer rede, sem WebSocket, sem STUN/TURN, e Mila deixa de ser SPOF.
- **Parâmetro do host:** `?mila` (typo `?mmila` corrigido).

### Added
- ADR-005: cPanel shared + PWA + polling HTTP (proposed) — substitui a proposta de P2P.

### Accepted
- ADR-005 ratificada: polling HTTP a 1s entre cliente e servidor cPanel. Alternativa P2P via WebRTC foi descartada por simplicidade e robustez (rede local não é necessária para um jogo de turnos de 10s).

### Note
- A ADR-005 foi renomeada de `005-cpanel-pwa-p2p.md` para `005-cpanel-pwa-polling.md` para refletir a nova abordagem de transporte.
