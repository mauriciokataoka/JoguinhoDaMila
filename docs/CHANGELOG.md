*Histórico de versões. Formato Keep a Changelog (keepachangelog.com); versionamento SemVer (semver.org).*

# Changelog

## [Unreleased]
### Changed
- **Pivô de arquitetura:** servidor deixa de ser um app iOS nativo com Go embutido e passa a ser **cPanel shared hosting** (HTTPS) servindo uma PWA. A PWA da Mila (aberta com `?mila`) é o host autoritativo do jogo. Clientes formam uma rede **P2P via WebRTC DataChannel** com signaling por API HTTP simples no cPanel (sem WebSocket).
- **Gameplay simplificado:** cada jogador, na sua vez, sugere **uma única letra** (forca clássica) — não a palavra inteira.

### Added
- ADR-005: cPanel shared + PWA + P2P (proposed).
