# Joguinho da Mil!a

*O que é este projeto e que problema resolve. Fonte da identidade. Otimizado p/ agente.*

## PRD (Product Requirements Document)

### Visão Geral
Um jogo infantil multiplayer em rede para crianças.

### Requisitos do Servidor
- **Plataforma:** Preferencialmente iOS no celular da Mila, sujeito à disponibilidade de conta Apple Developer.
- **Formato:** App nativo iOS com servidor Go embutido ou alternativa equivalente se não houver conta Apple Developer.
- **Função:** Serve a PWA aos clientes, expõe API de jogo e gera QR code para conexão.
- **Deploy:** Atualização e distribuição apenas do app da Mila.

### Requisitos do Cliente
- **Plataforma:** PWA acessada por navegador móvel.
- **Entrada:** Conecta via QR code para o IP/porta do celular da Mila.
- **Onboarding:** Cada cliente fornece um nickname no primeiro modal.

### Stack Confirmada
- **Servidor:** Go executável standalone embutido no app iOS.
- **Cliente:** PWA para navegadores móveis.
- **Rede:** Wi-Fi comum ou hotspot local; não requer internet.
- **Admin:** App da Mila serve a API e a interface inicial do jogo.

### Fluxo de Usuário e Conexão
1.  O app servidor roda no celular da Mila e exibe o modal de inicialização.
2.  Mila configura a palavra secreta do baú e o limite de tentativas por partida.
3.  O servidor gera um QR code com o endereço local `http://<ip>:<porta>`.
4.  Cada cliente escaneia o QR code e abre a PWA.
5.  No primeiro modal, o cliente define um nickname e recebe um token de jogador.
6.  O token é salvo no PWA para re-identificação em partidas sucessivas.
7.  Clientes entram na sala de espera até a Mila iniciar a partida.
8.  Se o servidor não for encontrado, o PWA exibe um input para o IP do servidor com o valor antigo pré-preenchido.
9.  O app servidor exibe o IP atual sempre visível ao lado do título.
10. Ao iniciar, novas conexões são bloqueadas e a partida começa.
9.  Cada jogador tem até 10 segundos para sugerir uma letra ou uma palavra.
10. Se o timer zerar, a vez passa para o próximo jogador.
11. Se o jogador desistir, sai da partida.
12. Se não responder, perde a vez até a próxima rodada.
13. O servidor segue até a palavra ser descoberta ou o administrador encerrar/reiniciar a partida.
14. Ao final, todos veem o modal de resultado; o servidor exibe botão para reiniciar.

### Identificação e Reconexão
- Cada cliente recebe um token após definir o nickname.
- O token identifica o jogador no servidor e preserva o nickname entre partidas.
- O token é salvo no `localStorage` da PWA.
- O QR code entrega apenas o endereço local do servidor, não o token.
- Se o servidor não for encontrado, a PWA exibe um input para o IP do servidor, pré-preenchido com o IP antigo.
- O PWA não precisa ser reinstalado se o IP mudar; basta atualizar o endereço no input.
- O app servidor exibe o IP atual sempre visível ao lado do título.

### Regras de Jogo e Conexão
- **Modo Privado:** O servidor funciona como uma sala privada local.
- **Multiplayer:** Quantos clientes estiverem conectados pelo QR code antes do início da partida.
- **Ordem de Turno:** Definida pela ordem de chegada dos jogadores.
- **Painel do Servidor:** Deve exibir os clientes conectados com opção de kick.
- **Limite de Clientes:** Não há limite máximo fixo de clientes por partida.
- **Turnos:** Cada cliente joga em sequência com timer de 10 segundos para sugerir letra ou palavra.
- **Desistência:** O jogador pode desistir e sai da partida.
- **Limite de Tentativas:** Configurável por partida junto com a palavra secreta.
- **Palavra Secreta:** Pode conter espaços ou hífens; espaços e hífens são exibidos e não precisam ser adivinhados.
- **Acesso:** Não há autenticação além do nickname/token.
- **Persistência:** Partidas são salvas e o ranking é acumulado por nickname.
- **Ranking:** Conta todas as partidas e estatísticas, não apenas vitórias.
- **Fim de Jogo:** Não há tempo total de partida; ela termina apenas quando a palavra é descoberta ou o servidor encerra/reinicia.

### Enredo Principal
- O jogo se passa em um reino mágico com um portal.
- Após atravessar o portal, o jogador encontra um rei e uma rainha.
- Um vilão prendeu o rei, a rainha e a princesa.
- O vilão perdeu a chave do portal.
- Quem encontrar a chave liberta a família real e vence o jogo.
- O jogo se repete continuamente, permitindo partidas sucessivas.

...

...
