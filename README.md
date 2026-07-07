# Joguinho da Mil!a

Jogo infantil multiplayer local. O servidor roda no celular da Mila e serve uma PWA para as outras jogadoras.

## Visão geral
- Servidor iOS com Go embutido.
- Clientes acessam por PWA via QR code.
- O jogo é um tipo de forca cooperativa com palavra secreta configurada pela Mila.
- O IP do servidor é exibido no app e a PWA permite atualizar o endereço se o servidor mudar de IP.

## Documentação do projeto
- [PRD](docs/PRD.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Design](docs/DESIGN.md)
- [Roadmap](docs/ROADMAP.md)
- [Changelog](docs/CHANGELOG.md)
- [ADRs](docs/adr/INDEX.md)

## Estado atual
- Documentação de requisitos e fluxo atualizada em `docs/PRD.md`.
- Roadmap atualizado em `docs/ROADMAP.md`.
- ADRs criadas em `docs/adr/` para decisões de stack, fallback de IP e regras de sessão.

## Deploy e GitHub
O repositório já foi criado em `github.com/mauriciokataoka/JoguinhoDaMila` e os commits atuais foram pushados para `main`.
