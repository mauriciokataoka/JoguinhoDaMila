# Joguinho da Mila

Jogo infantil multiplayer (forca com tema de reino mágico) para crianças. A PWA roda em qualquer celular; a hospedagem pública (cPanel) serve a PWA via HTTPS e roda o servidor de jogo. Mila é a admin: ela abre a PWA com `?mila=<token_admin>`, gerencia múltiplas salas em abas, escolhe a palavra e controla o jogo. As crianças abrem a PWA pela URL base e entram na última sala. Tudo via polling HTTP a 1 segundo.

## Visão geral
- Hospedagem: cPanel shared (PHP) em `joguinhodamila.kataoka.com.br` (HTTPS via Let's Encrypt).
- Cliente: PWA instalável em iOS e Android (instalada uma vez por adulto; a URL do kid pode incluir `?nome=<valor>` para pré-preencher o nickname).
- Transporte: polling HTTP a 1s (sem WebSocket, sem P2P).
- Multi-sala desde o início: Mila cria salas via `+` na barra de abas; a sala mais recente é o destino padrão dos novos clientes.
- Admin token rotacionável: vive em `config.php`; se vazar, basta editar.
- Sem dependência de Apple Developer.

## Estado atual
- **v0.1.0** — implementação inicial completa: backend PHP com 6 endpoints + frontend PWA vanilla (HTML/CSS/JS + manifest + service worker). Pronto para deploy no cPanel e teste.
- Pendente (refinamentos): ícones PNG (atualmente SVG), pausa de polling em background, animações.

## Estrutura
```
src/
├── index.html              # PWA (servido na raiz do subdomínio)
├── app.js                  # polling + UI (admin e cliente)
├── style.css
├── manifest.json
├── sw.js                   # service worker (cache-first p/ shell)
├── icon.svg
└── api/                    # backend PHP
    ├── config.php          # $ADMIN_TOKEN, $ADMIN_NAME
    ├── db.php              # PDO + schema SQLite + helpers
    ├── api.php             # entry point / router
    ├── data/               # SQLite (gitignored; criado em runtime; protegido por .htaccess)
    └── endpoints/
        ├── check_admin.php
        ├── join.php
        ├── state.php
        ├── action.php
        ├── admin_state.php
        └── admin_action.php

deploy/                     # scripts de deploy (modelo MegaWordChallenge)
├── deploy.sh               # primário: rsync sobre SSH
├── deploy-ftp.sh           # fallback: lftp (FTP+TLS)
├── .deploy.env             # gitignored; credenciais reais
└── .deploy.env.example     # template
```

## Documentação do projeto
- [PRD](docs/PRD.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Design](docs/DESIGN.md)
- [Roadmap](docs/ROADMAP.md)
- [Changelog](docs/CHANGELOG.md)
- [ADRs](docs/adr/INDEX.md)

## Deploy

O deploy é automatizado pelos scripts em `deploy/` (mesmo padrão do MegaWordChallenge).

### Scripts
- **`deploy/deploy.sh`** — **primário**: `rsync` sobre SSH (Hostinger: porta 65002, chave `~/.ssh/mwc_deploy`).
- **`deploy/deploy-ftp.sh`** — **fallback**: `lftp` com FTP explícito sobre TLS.

Ambos aceitam: `--dry-run`, `--yes`, `--no-delete`.

### Primeira vez
1. `deploy/.deploy.env` já está criado e preenchido (gerado a partir do FileZilla sitemanager; gitignored, chmod 600).
2. Edite `src/api/config.php` e troque `$ADMIN_TOKEN` por um valor seguro.
3. No cPanel/Hostinger, crie o subdomínio `joguinhodamila.kataoka.com.br` com docroot em `/home/kataokac/joguinhodamila/` (= `REMOTE_PATH` relativo ao home).
4. Ative o HTTPS no domínio (Let's Encrypt no cPanel).
5. Rode `./deploy/deploy.sh --dry-run` para conferir, depois `./deploy/deploy.sh`.

### Rotina
- `./deploy/deploy.sh` — espelha `src/` inteiro em `~/joguinhodamila/` (PWA na raiz, `api/` como subdir).
- O SQLite (`api/data/jogo.sqlite`) é **preservado** entre deploys (`api/data/*.sqlite*` no exclude do rsync).
- `chmod 755` em `api/data/` é aplicado via SSH após o upload.
- Para rotacionar o admin token: edite `src/api/config.php`, faça deploy, e reenvie a nova URL com `?mila=<novo_token>` para Mila reinstalar a PWA.

## Teste local
- Backend: `php -S localhost:8000 -t src/api/` (servidor de desenvolvimento do PHP).
- PWA: abra `src/pwa/index.html` em um navegador, ou sirva com qualquer servidor estático.
- HTTPS é necessário para a PWA ser instalável; em localhost, o service worker funciona sem HTTPS.
