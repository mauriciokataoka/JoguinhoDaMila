# Joguinho da Mil!a

*O que é este projeto e que problema resolve. Fonte da identidade. Otimizado p/ agente.*

## PRD (Product Requirements Document)

### Visão Geral
Jogo infantil multiplayer (forca com tema de reino mágico) para crianças. A hospedagem pública é o **cPanel shared (PHP)**, que serve a PWA via HTTPS e roda o servidor de jogo. A PWA é o único cliente, instalada uma vez por um adulto em cada dispositivo (sem parâmetros variáveis por install). Aberta com **`?mila=<token_admin>`**, a PWA se transforma no **admin** (Mila) e mostra uma UI multi-sala com abas; sem o parâmetro (ou com token inválido), a PWA é um **cliente** que entra numa sala e joga. Cada cliente conversa com o servidor via **polling HTTP a 1s** (sem WebSocket, sem P2P). O servidor gerencia **múltiplas salas desde o início**.

### Requisitos do Servidor (público)
- **Plataforma:** cPanel shared hosting (apenas PHP disponível).
- **Domínio:** `joguinhodamila.kataoka.com.br` (HTTPS via Let's Encrypt, requisito para PWA instalável).
- **Função 1:** serve a PWA estática para todos os clientes.
- **Função 2:** é o **servidor de jogo** — mantém o estado autoritativo de **múltiplas salas** e expõe API HTTP para os clientes.
- **Função 3:** persiste ranking e histórico de partidas por nickname.
- **Função 4:** valida o **admin token** (definido em `config.php`).
- **Stack do backend:** PHP (única opção no cPanel).
- **Deploy:** upload via FTP/SSH no cPanel; sem rebuild semanal.

### Requisitos do Cliente
- **Plataforma:** PWA (qualquer navegador móvel moderno).
- **Instalação:** Add to Home Screen no iOS ou "Instalar app" no Android — feito uma vez por um adulto, **sem parâmetros por install** (URL base fixa).
- **Detecção de papel:**
  - Com `?mila=<token_válido>` → UI **admin** (Mila). O nome exibido da admin é "Mila" por padrão (customizável em `config.php`).
  - Sem `?mila` ou token inválido → UI **cliente** (pode ter `?nome=<valor>` para pré-preencher o nickname).
- **Onboarding (cliente):** primeiro modal pede nickname e exibe o token gerado (salvo em `localStorage`). Se a URL contém `?nome=<valor>`, o nickname é pré-preenchido (configurado pelo adulto na instalação) e o modal pode ser pulado ou só confirmado.
- **Comunicação (cliente):** `GET /state?sala=…` a cada 1s; `POST /action` para sugerir letra, desistir.
- **Comunicação (admin):** `GET /api/admin/state` a cada 1s (lista de salas e clientes); `POST /api/admin/action` para criar sala, escolher palavra, iniciar, mover, banir.
- **Token do admin:** validado no carregamento; pode ser digitado manualmente na PWA e salvo em `localStorage` (para suportar rotação sem reinstalar).

### Requisitos de Segurança
- O **admin token** vive em `config.php` no servidor e é rotacionável sem deploy.
- O link com `?mila=<token>` é a credencial admin — se vazar, basta editar `config.php`.
- Não há autenticação de cliente além do token de jogador (gerado no primeiro acesso).

### Stack Proposta
- **Host público:** cPanel shared (**PHP**) com HTTPS.
- **Cliente:** PWA (HTML/CSS/JS, instalável).
- **Transporte:** polling HTTP a 1s (sem WebSocket, sem WebRTC, sem STUN/TURN).
- **Admin token:** valor em `config.php`; validado por endpoint `/api/check-admin`.

### Fluxo de Usuário e Conexão

#### Fluxo Admin (Mila)
1.  Mila abre `https://joguinhodamila.kataoka.com.br/?mila=<token_admin>` no celular dela.
2.  A PWA carrega, valida o token via `/api/check-admin`. Se inválido, mostra campo para digitar/colar o novo token.
3.  A UI admin abre mostrando a(s) sala(s) em abas. A última aba é `+`.
4.  Mila clica `+` para criar uma sala (ou a sala 1 já existe por padrão).
5.  Mila compartilha a URL base com as crianças (elas instalam a PWA e abrem).
6.  Conforme as crianças entram (mecanismo a definir), Mila vê a lista de clientes na aba de cada sala.
7.  Mila pode clicar `X` em um cliente para banir ou `→` para movê-lo para a próxima sala.
8.  Quando estiver pronta, Mila escolhe a palavra e o limite de tentativas na sala em que está e clica "Iniciar".
9.  A sala passa para "em jogo"; a UI admin mostra o estado da partida (palavra mascarada, tentativas, turno).
10. Ao final (palavra descoberta ou Mila encerrar), a UI mostra o resultado e o botão de reiniciar.

#### Fluxo Cliente (criança)
1.  A criança abre a PWA (URL base, sem parâmetro).
2.  A PWA pede um nickname na primeira vez e recebe um token; salva em `localStorage`. Se a URL contém `?nome=<valor>`, o nickname vem pré-preenchido (configurado pelo adulto na instalação) e o modal pode ser pulado ou só confirmado.
3.  A PWA faz `POST /join` para entrar numa sala (mecanismo de atribuição a definir).
4.  A criança vê a tela da sala: "Aguardando Mila iniciar o jogo".
5.  Quando Mila inicia, a criança vê a palavra mascarada e espera sua vez.
6.  Na sua vez, digita uma letra e envia.
7.  O servidor valida, atualiza o estado e passa a vez.
8.  Acompanhar até o fim da partida.

### Identificação e Reconexão
- **Cliente:** token por nickname, salvo em `localStorage`. Reconexão: basta reabrir a PWA com o mesmo token.
- **Admin:** token validado no carregamento. Se rotacionado, a PWA mostra campo para digitar o novo (salva em `localStorage` para próximos loads).

### Regras de Jogo e Conexão
- **Salas:** múltiplas desde o início; Mila cria via `+`.
- **Multiplayer por sala:** quantos clientes estiverem na sala antes do início.
- **Ordem de Turno:** definida pela ordem de chegada na sala; o servidor coordena.
- **Ações do admin (Mila):**
  - `+` cria nova sala.
  - `X` em um cliente bane.
  - `→` em um cliente move para a próxima sala.
  - "Iniciar" inicia a partida na sala atual.
  - "Encerrar" encerra a partida atual.
- **Limite de Clientes por sala:** sem limite fixo.
- **Turnos:** cada cliente joga em sequência com timer de 10s para sugerir **uma única letra** (forca clássica).
- **Desistência:** o jogador pode desistir e sai da partida.
- **Limite de Tentativas:** configurável por partida junto com a palavra secreta.
- **Palavra Secreta:** pode conter espaços ou hífens; são exibidos e não precisam ser adivinhados.
- **Acesso:** nickname + token (cliente); admin token (Mila).
- **Persistência:** ranking acumulado por nickname (no backend cPanel).
- **Ranking:** conta todas as partidas e estatísticas, não apenas vitórias.
- **Fim de Jogo:** sem tempo total; termina quando a palavra é descoberta ou o admin encerra/reinicia.

### Enredo Principal
- O jogo se passa em um reino mágico com um portal.
- Após atravessar o portal, o jogador encontra um rei e uma rainha.
- Um vilão prendeu o rei, a rainha e a princesa.
- O vilão perdeu a chave do portal.
- Quem encontrar a chave liberta a família real e vence o jogo.
- O jogo se repete continuamente, permitindo partidas sucessivas.
