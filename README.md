# Joguinho da Mil!a

Jogo infantil multiplayer (forca com tema de reino mágico) para crianças. A PWA roda em qualquer celular; a hospedagem pública (cPanel) serve a PWA via HTTPS e roda o servidor de jogo. Mila é a admin: ela abre a PWA com `?mila=<token_admin>`, gerencia múltiplas salas em abas, escolhe a palavra e controla o jogo. As crianças abrem a PWA pela URL base e entram numa sala. Tudo via polling HTTP a 1 segundo.

## Visão geral
- Hospedagem: cPanel shared (PHP) em `joguinhodamila.kataoka.com.br` (HTTPS via Let's Encrypt).
- Cliente: PWA instalável em iOS e Android (instalada uma vez por adulto, sem parâmetros por install).
- Transporte: polling HTTP a 1s (sem WebSocket, sem P2P).
- Multi-sala desde o início: Mila cria salas via `+` na barra de abas.
- Admin token rotacionável: vive em `config.php`; se vazar, basta editar.
- Sem dependência de Apple Developer.

## Documentação do projeto
- [PRD](docs/PRD.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Design](docs/DESIGN.md)
- [Roadmap](docs/ROADMAP.md)
- [Changelog](docs/CHANGELOG.md)
- [ADRs](docs/adr/INDEX.md)

## Estado atual
- Decisão de plataforma: cPanel + PWA + polling + multi-sala + admin token (ADR-005, accepted).
- Pendente: mecanismo de atribuição de clientes a salas (ver [[ROADMAP]]).
- Modal de inicialização desenhado, aguardando implementação.

## Deploy
1. O código fica no GitHub (repositório público).
2. Build da PWA é estático (HTML/CSS/JS); sobe via FTP/SSH no cPanel.
3. Backend de jogo em **PHP** roda no cPanel.
4. `config.php` no servidor define `$ADMIN_TOKEN` (rotacionável).
5. Let's Encrypt ativa o HTTPS no domínio.
6. Mila acessa `https://joguinhodamila.kataoka.com.br/?mila=<token_admin>` para ser admin; crianças acessam a URL base.
