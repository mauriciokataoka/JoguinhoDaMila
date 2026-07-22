# Joguinho da Mil!a

*O que é este projeto e que problema resolve. Fonte da identidade. Otimizado p/ agente.*

## PRD (Product Requirements Document)

### Visão Geral
Jogo infantil multiplayer (forca com tema de reino mágico) para crianças. A hospedagem pública é o **cPanel shared**, que serve a PWA via HTTPS. A PWA é o único cliente; aberta com `?mila`, ela se transforma no **host autoritativo** (Mila escolhe a palavra, controla o jogo). Abertas sem o parâmetro, as outras crianças entram como jogadoras. Os clientes formam uma **rede P2P** entre si, e o estado do jogo (turno, validação, placar) flui inteiramente via P2P — o cPanel só faz signaling (descoberta de peers, troca de ICE). Sem WebSocket.

### Requisitos do Servidor (público)
- **Plataforma:** cPanel shared hosting.
- **Domínio:** `joguinhodamila.kataoka.com.br` (HTTPS via Let's Encrypt, requisito para PWA instalável).
- **Função 1:** serve a PWA estática para todos os clientes.
- **Função 2:** expõe uma API HTTP simples de signaling (registro de peers na sala, troca de ICE candidates).
- **Função 3:** persiste ranking e histórico de partidas por nickname.
- **Stack do backend:** a confirmar conforme o que o cPanel aceita (Node, PHP ou Python).
- **Deploy:** upload via FTP/SSH no cPanel; sem rebuild semanal.

### Requisitos do Cliente
- **Plataforma:** PWA (qualquer navegador móvel moderno).
- **Instalação:** Add to Home Screen no iOS ou "Instalar app" no Android — exige HTTPS (garantido pelo cPanel).
- **Entrada:** acessa `https://joguinhodamila.kataoka.com.br`; se a URL contém `?mila=…`, a PWA assume o papel de **host** (Mila); senão, papel de **jogadora**.
- **Onboarding:** primeiro modal pede nickname e exibe o token gerado (salvo em `localStorage`).
- **Identidade na rede P2P:** cada cliente registra seu IP no signaling; peers trocam IPs via WebRTC.

### Stack Proposta
- **Host público:** cPanel shared (Node/PHP/Python) com HTTPS.
- **Cliente:** PWA (HTML/CSS/JS, instalável).
- **Transporte entre clientes:** WebRTC DataChannel (P2P).
- **Signaling:** API HTTP simples no cPanel (sem WebSocket, sem long-polling para o jogo).
- **STUN:** servidor público (ex.: `stun:stun.l.google.com:19302`); TURN postergado.
- **Host do jogo:** a PWA da Mila é autoritativa para o estado da partida.

### Fluxo de Usuário e Conexão
1.  Mila abre `https://joguinhodamila.kataoka.com.br/?mila=1` no celular dela; a PWA carrega, identifica o papel de host e pede a palavra secreta e o limite de tentativas.
2.  Mila compartilha o link (com ou sem `?mila`, ou com código de sala — a definir) com as outras crianças.
3.  Cada criança abre o link, a PWA carrega, pede um nickname na primeira vez e recebe um token; o token fica salvo em `localStorage`.
4.  Cada cliente se registra no backend de signaling (sala, papel, ICE candidates).
5.  Quando todas estão na sala, Mila inicia a partida; novas conexões podem ser bloqueadas nesse momento.
6.  A PWA da Mila retransmite o estado do jogo para os clientes via DataChannel P2P. A rede P2P organiza a ordem dos turnos.
7.  Cada jogador tem até 10 segundos para sugerir **uma única letra**; timeout passa a vez.
8.  Se um jogador desistir, sai da partida; se não responder, perde a vez.
9.  O jogo termina quando a palavra é descoberta ou quando a Mila encerra/reinicia.
10.  Ao final, todos veem o modal de resultado; a PWA da Mila exibe botão para reiniciar.

### Identificação e Reconexão
- Cada cliente recebe um **token** ao definir o nickname; identifica o jogador no backend signaling e preserva o nickname entre partidas.
- Token salvo em `localStorage` da PWA.
- Acesso à sala é pelo link (e/ou código de sala — a definir).
- Identidade técnica na rede P2P é o IP do cliente (visível aos pares via signaling).
- Reconexão: se a P2P cair, o cliente re-registra no signaling e reabre o DataChannel.

### Regras de Jogo e Conexão
- **Modo Privado:** a sala é privada; o link/código é o controle de acesso.
- **Multiplayer:** quantos clientes estiverem na sala antes do início da partida.
- **Ordem de Turno:** definida pela ordem de chegada na sala; organizada pela rede P2P (a PWA da Mila coordena).
- **Painel do Host (Mila):** exibe os clientes conectados, opção de kick, controle de início/encerramento.
- **Limite de Clientes:** sem limite fixo (escala da mesh P2P).
- **Turnos:** cada cliente joga em sequência com timer de 10s para sugerir **uma única letra** (forca clássica).
- **Desistência:** o jogador pode desistir e sai da partida.
- **Limite de Tentativas:** configurável por partida junto com a palavra secreta.
- **Palavra Secreta:** pode conter espaços ou hífens; são exibidos e não precisam ser adivinhados.
- **Acesso:** nickname + token; sem autenticação adicional.
- **Persistência:** ranking acumulado por nickname (no backend cPanel).
- **Ranking:** conta todas as partidas e estatísticas, não apenas vitórias.
- **Fim de Jogo:** sem tempo total; termina quando a palavra é descoberta ou o host encerra/reinicia.

### Enredo Principal
- O jogo se passa em um reino mágico com um portal.
- Após atravessar o portal, o jogador encontra um rei e uma rainha.
- Um vilão prendeu o rei, a rainha e a princesa.
- O vilão perdeu a chave do portal.
- Quem encontrar a chave liberta a família real e vence o jogo.
- O jogo se repete continuamente, permitindo partidas sucessivas.
