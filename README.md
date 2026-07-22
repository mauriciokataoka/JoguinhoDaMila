# Joguinho da Mil!a

Jogo infantil multiplayer (forca com tema de reino mágico) para crianças. A PWA roda em qualquer celular; a hospedagem pública (cPanel) serve a PWA via HTTPS e roda o servidor de jogo. A Mila é a host: ela abre a PWA com `?mila`, escolhe a palavra, e as crianças entram pela mesma URL. Os clientes conversam com o servidor via polling HTTP a cada 1 segundo.

## Visão geral
- Hospedagem: cPanel shared em `joguinhodamila.kataoka.com.br` (HTTPS via Let's Encrypt).
- Cliente: PWA instalável em iOS e Android.
- Transporte: polling HTTP a 1s (sem WebSocket, sem P2P).
- Sem dependência de Apple Developer.

## Documentação do projeto
- [PRD](docs/PRD.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Design](docs/DESIGN.md)
- [Roadmap](docs/ROADMAP.md)
- [Changelog](docs/CHANGELOG.md)
- [ADRs](docs/adr/INDEX.md)

## Estado atual
- Decisão de plataforma: cPanel + PWA + polling (ADR-005, proposed).
- Pendente: modelo de acesso à sala e runtime do backend (ver [[ROADMAP]]).
- Modal de inicialização desenhado, aguardando implementação.

## Deploy
1. O código fica no GitHub (repositório público).
2. Build da PWA é estático (HTML/CSS/JS); sobe via FTP/SSH no cPanel.
3. Backend de jogo roda no cPanel (linguagem a confirmar com a hospedagem).
4. Let's Encrypt ativa o HTTPS no domínio.
5. Mila acessa `https://joguinhodamila.kataoka.com.br/?mila` para ser host; crianças acessam a URL base.
