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

## Deploy depois de pronto
1.  Finalizar o desenvolvimento no repositório e garantir que o app iOS servidor esteja funcionando.
2.  Build do app iOS com Xcode, incorporando o binário Go e os arquivos estáticos da PWA.
3.  Não dispomos de conta Apple Developer no momento; o deploy iOS precisa ser avaliado com alternativas de distribuição ou assinatura.
4.  Instalar o app no celular da Mila via Xcode direto, TestFlight ou distribuição ad hoc somente se/quando a conta estiver disponível.
5.  Executar o app no celular da Mila; ele servirá a PWA localmente e gerará o QR code para as clientes.
6.  O PWA não precisa ser publicado em servidor público, pois será servido pelo app localmente.

### Observações
- O deploy principal é a instalação do app servidor no celular da Mila.
- Se a conta Apple Developer não estiver disponível, precisamos validar outra plataforma ou método de distribuição antes de seguir com a stack iOS.
- O código fonte e as releases podem ser mantidos no GitHub.
- Se quiser, podemos também incluir um script de build e um release no GitHub para versão futura.
