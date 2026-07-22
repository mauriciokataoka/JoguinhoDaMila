# Joguinho da Mil!a

*O que é este projeto e que problema resolve. Fonte da identidade. Otimizado p/ agente.*

## PRD (Product Requirements Document)

### Visão Geral
Jogo infantil multiplayer (forca com tema de reino mágico) para crianças. A hospedagem pública é o **cPanel shared**, que serve a PWA via HTTPS e roda o servidor de jogo. A PWA é o único cliente; aberta com **`?mila`**, ela se transforma no **host** (Mila escolhe a palavra, controla o jogo). Abertas sem o parâmetro, as outras crianças entram como jogadoras. Cada cliente conversa com o servidor via **polling HTTP a 1s** (sem WebSocket, sem P2P). Funciona em qualquer rede.

### Requisitos do Servidor (público)
- **Plataforma:** cPanel shared hosting.
- **Domínio:** `joguinhodamila.kataoka.com.br` (HTTPS via Let's Encrypt, requisito para PWA instalável).
- **Função 1:** serve a PWA estática para todos os clientes.
- **Função 2:** é o **servidor de jogo** — mantém o estado autoritativo da partida e expõe API HTTP para os clientes.
- **Função 3:** persiste ranking e histórico de partidas por nickname.
- **Stack do backend:** a confirmar conforme o que o cPanel aceita (Node, PHP ou Python).
- **Deploy:** upload via FTP/SSH no cPanel; sem rebuild semanal.

### Requisitos do Cliente
- **Plataforma:** PWA (qualquer navegador móvel moderno).
- **Instalação:** Add to Home Screen no iOS ou "Instalar app" no Android — exige HTTPS (garantido pelo cPanel).
- **Entrada:** acessa `https://joguinhodamila.kataoka.com.br`; se a URL contém `?mila`, a PWA assume o papel de **host** (Mila); senão, papel de **jogadora**.
- **Onboarding:** primeiro modal pede nickname e exibe o token gerado (salvo em `localStorage`).
- **Comunicação:** `GET /state` a cada 1s; `POST /action` para sugerir letra, desistir, iniciar/encerrar (host), kick (host).

### Stack Proposta
- **Host público:** cPanel shared (Node/PHP/Python) com HTTPS.
- **Cliente:** PWA (HTML/CSS/JS, instalável).
- **Transporte:** polling HTTP a 1s (sem WebSocket, sem WebRTC, sem STUN/TURN).
- **Host do jogo:** a PWA da Mila é o **cliente host**; o **servidor cPanel** é a fonte autoritativa do estado.

### Fluxo de Usuário e Conexão
1.  Mila abre `https://joguinhodamila.kataoka.com.br/?mila` no celular dela; a PWA carrega, identifica o papel de host e pede a palavra secreta e o limite de tentativas.
2.  Mila compartilha o link (sem `?mila`, ou com código de sala — a definir) com as outras crianças.
3.  Cada criança abre o link, a PWA carrega, pede um nickname na primeira vez e recebe um token; o token fica salvo em `localStorage`.
4.  Cada cliente entra na sala do servidor; o servidor atribui um turno com base na ordem de chegada.
5.  A PWA faz polling de estado a cada 1s. As crianças veem a sala de espera até Mila iniciar a partida.
6.  Mila inicia a partida; o estado passa a incluir a palavra mascarada, tentativas e o turno atual.
7.  Cada jogador, na sua vez, digita **uma única letra** e a PWA faz `POST /action`. O servidor valida, atualiza o estado e o próximo turno.
8.  Timer de 10s por turno: se o jogador não agir, o servidor passa a vez.
9.  O jogo termina quando a palavra é descoberta ou quando a Mila encerra/reinicia.
10.  Ao final, todos veem o modal de resultado; a PWA da Mila exibe botão para reiniciar.

### Identificação e Reconexão
- Cada cliente recebe um **token** ao definir o nickname; identifica o jogador no servidor e preserva o nickname entre partidas.
- Token salvo em `localStorage` da PWA.
- Acesso à sala é pelo link (e/ou código de sala — a definir).
- Reconexão: como o estado está no servidor, basta o cliente voltar a fazer polling com o mesmo token.

### Regras de Jogo e Conexão
- **Modo Privado:** a sala é privada; o link/código é o controle de acesso.
- **Multiplayer:** quantos clientes estiverem na sala antes do início da partida.
- **Ordem de Turno:** definida pela ordem de chegada na sala; o servidor coordena.
- **Painel do Host (Mila):** exibe os clientes conectados, opção de kick, controle de início/encerramento.
- **Limite de Clientes:** sem limite fixo (escala da carga de polling).
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
