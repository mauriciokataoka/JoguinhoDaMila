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
├── api/                    # backend PHP
│   ├── config.php          # $ADMIN_TOKEN, $ADMIN_NAME
│   ├── db.php              # PDO + schema SQLite + helpers
│   ├── api.php             # entry point / router
│   ├── data/               # SQLite (gitignored; criado em runtime)
│   └── endpoints/
│       ├── check_admin.php
│       ├── join.php
│       ├── state.php
│       ├── action.php
│       ├── admin_state.php
│       └── admin_action.php
└── pwa/                    # frontend PWA
    ├── index.html
    ├── app.js              # polling + UI (admin e cliente)
    ├── style.css
    ├── manifest.json
    ├── sw.js               # service worker (cache-first p/ shell)
    └── icon.svg
```

## Documentação do projeto
- [PRD](docs/PRD.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Design](docs/DESIGN.md)
- [Roadmap](docs/ROADMAP.md)
- [Changelog](docs/CHANGELOG.md)
- [ADRs](docs/adr/INDEX.md)

## Deploy
1. Edite `src/api/config.php` e troque `$ADMIN_TOKEN` por um valor seguro.
2. Suba `src/` para o cPanel via FTP/SSH (em `public_html/` ou similar).
3. Garanta que `src/api/data/` seja gravável pelo PHP (chmod 0755 ou 0775).
4. Ative o HTTPS no domínio (Let's Encrypt no cPanel).
5. Mila acessa `https://joguinhodamila.kataoka.com.br/src/pwa/?mila=<seu_token>` para ser admin.
6. Crianças acessam `https://joguinhodamila.kataoka.com.br/src/pwa/` (com `?nome=<valor>` opcional).

**Importante:** ajuste os caminhos acima conforme a estrutura do cPanel (a raiz pública pode ser `public_html/`, e a PWA pode ficar em `public_html/pwa/` em vez de `src/pwa/`). O `api.php` deve ficar acessível na URL que o `app.js` aponta (`./api/api.php` em relação à PWA).

## Teste local
- Backend: `php -S localhost:8000 -t src/api/` (servidor de desenvolvimento do PHP).
- PWA: abra `src/pwa/index.html` em um navegador, ou sirva com qualquer servidor estático.
- HTTPS é necessário para a PWA ser instalável; em localhost, o service worker funciona sem HTTPS.
