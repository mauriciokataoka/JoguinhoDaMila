*UX/UI e interação. Otimizado p/ agente.*

## Design

### Modal de Inicialização
- Não precisa de internet: o jogo deve ser totalmente funcional offline (após o primeiro carregamento).
- Primeiro modal de inicialização com visual característico:
  - fundo rosa mais escuro;
  - coração rosa claro, grande e centralizado;
  - faixa diagonal no coração com o texto "Joguinho da Mila".
- O modal pede o nickname na primeira vez (clientes) ou valida o admin token (Mila).

### UI Cliente (criança)
- **Onboarding:** se a URL contém `?nome=<valor>`, mostra o nickname pré-preenchido e pede confirmação; sem o parâmetro, mostra o campo de nickname vazio. Após confirmar, salva o token em `localStorage`.
- Tela principal: estado da sala em que está.
  - Aguardando: "Aguardando Mila iniciar o jogo."
  - Em jogo: palavra mascarada (`_ _ _ _`), tentativas restantes, lista de jogadores com indicador de quem é a vez, timer.
  - Na sua vez: campo de input para uma letra + botão de enviar.
  - Fim: modal de resultado.
- Reconexão automática ao recarregar.

### UI Admin (Mila)
- A admin é identificada pelo **token na URL** (`?mila=<token>`); o nome exibido na UI é **"Mila"** por padrão (customizável em `config.php`).
- Barra de **abas** no topo, uma por sala. Cada aba mostra o ID da sala (ex.: "1", "2", "3").
- A **última aba é sempre `+`** — clicar cria uma nova sala.
- Conteúdo da aba selecionada:
  - **Cabeçalho:** campo para a palavra secreta + campo para o limite de tentativas (visível quando a sala está "aguardando"); botão "Iniciar".
  - **Lista de clientes** na sala:
    - Cada linha: nickname + botões **`X`** (banir) e **`→`** (mover para a próxima sala à direita).
    - `→` desabilitado se a sala é a última antes do `+`.
  - Quando a sala está "em jogo": estado da partida (palavra mascarada, tentativas, turno) + botão "Encerrar".
- **Validação do admin token** no carregamento:
  - Se `?mila=<válido>` na URL → entra direto.
  - Se inválido ou ausente → campo "Token do admin" para digitar/colar; salva em `localStorage` para próximos loads.

### Cores e tom
- Tema rosa (alinhado ao modal): tons suaves para crianças.
- Tipografia grande e legível.
- Botões grandes e com área de toque generosa (mobile-first).
- Feedback visual claro para ações (quem é a vez, quem moveu, quem baniu).
