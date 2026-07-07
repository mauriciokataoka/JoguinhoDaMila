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
3.  Não dispomos de conta Apple Developer no momento; isso torna a distribuição oficial de um app iOS definitivo inviável atualmente.
4.  Como o app servidor precisa ser definitivo, não abrimos mão de uma solução de deploy que não exija rebuild a cada 7 dias.
5.  Precisamos avaliar duas alternativas:
    - obter uma conta Apple Developer e fazer deploy iOS oficial;
    - ou migrar o servidor para um app Android que possa ser instalado de forma definitiva.
6.  Executar o app no celular da Mila; ele servirá a PWA localmente e gerará o QR code para as clientes.
7.  O PWA não precisa ser publicado em servidor público, pois será servido pelo app localmente.

### Observações
- O deploy principal é a instalação do app servidor no celular da Mila.
- A solução de build com Apple ID gratuito não é aceitável para o app servidor definitivo.
- Precisamos decidir se vamos viabilizar o deploy iOS oficial ou mover o servidor para Android.
- O código fonte e as releases podem ser mantidos no GitHub.
- Vamos documentar essa decisão e retornar amanhã.
