*Histórico de versões. Formato Keep a Changelog (keepachangelog.com); versionamento SemVer (semver.org).*

# Changelog

## [0.1.0] - 2026-07-22
### Added
- **Backend PHP (cPanel):**
  - `src/api/config.php` com `$ADMIN_TOKEN` (rotacionável) e `$ADMIN_NAME`.
  - `src/api/db.php` com conexão PDO/SQLite e schema (rooms, players, bans, ranking).
  - `src/api/api.php` como entry point / router.
  - 6 endpoints em `src/api/endpoints/`:
    - `check_admin` — valida o token do admin.
    - `join` — atribui cliente à última sala (regra ADR-005).
    - `state` — polling de estado para o cliente (1s).
    - `action` — ações do cliente (sugerir letra, sair).
    - `admin_state` — polling de estado para o admin (lista de salas e clientes).
    - `admin_action` — ações do admin (criar sala, iniciar, encerrar, resetar, mover cliente, banir).
- **PWA vanilla (HTML/CSS/JS):**
  - `src/pwa/index.html` — shell único; detecta `?mila=` (admin) ou `?nome=` (cliente).
  - `src/pwa/app.js` — polling 1s, UI admin (abas, X, →, +) e UI cliente (palavra mascarada, timer, input de letra).
  - `src/pwa/style.css` — tema rosa (alinhado ao DESIGN), modal inicial com coração e faixa diagonal.
  - `src/pwa/manifest.json` + `icon.svg` — PWA instalável.
  - `src/pwa/sw.js` — service worker (cache-first p/ shell, network-first p/ `/api/*`).
- **Lógica de forca:** máscara da palavra (espaços e hífens visíveis), validação de letra, detecção de vitória/derrota, ranking acumulado.
- **Multi-sala:** criação via `+`, mover cliente entre salas (`→`), banir (`X`).
- **Regras de turno:** 10s por jogador, servidor controla ordem e timer.

### Changed
- Documentação ampliada (PRD, ARCHITECTURE, DESIGN, ROADMAP) refletindo a arquitetura final.

### Notes
- v0.1.0 é a primeira versão funcional end-to-end. Pronto para deploy no cPanel e teste em dispositivos reais.
- `$ADMIN_TOKEN` em `config.php` precisa ser trocado para um valor seguro antes do deploy de produção.

### Deploy
- Scripts de deploy no padrão MegaWordChallenge: `deploy/deploy.sh` (rsync+SSH, primário) e `deploy/deploy-ftp.sh` (lftp+TLS, fallback).
- `deploy/.deploy.env` (gitignored, chmod 600) com credenciais reais (SSH `kataokac@kataoka.com.br:65002` via `~/.ssh/mwc_deploy`; FTP fallback).
- `src/api/data/.htaccess` protege o SQLite contra download direto.

### Changed
- Reestruturação de `src/`: PWA achatada para a raiz (`src/index.html`, `src/app.js`, ...) e `src/api/` como subdir. Necessário para que `joguinhodamila.kataoka.com.br/` sirva a PWA diretamente e o subdomínio `api/` (ou seja, `/api/api.php`) sirva o backend.
