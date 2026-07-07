# ADR-002: Fallback de conexão de IP para PWA

*Status · Contexto · Decisão · Consequências*

## Status
Accepted

## Contexto
O servidor do jogo roda no celular da Mila e os clientes usam um PWA conectado via QR code. Em redes móveis ou Wi-Fi comuns, o IP local do servidor pode mudar durante a sessão.

Se o servidor mudar de IP, o PWA não deve precisar ser reinstalado. Os jogadores devem ser capazes de reconectar editando o endereço do servidor diretamente.

## Decisão
- O app servidor exibirá o IP atual sempre visível ao lado do título do app.
- O QR code continuará a fornecer o endereço local inicial do servidor.
- A PWA armazenará um token de jogador associado ao nickname no dispositivo.
- Se o servidor não for encontrado, a PWA exibirá um input para o IP do servidor.
- O input virá pré-preenchido com o IP antigo para que o jogador apenas edite o valor.
- Esse fluxo permite reconexão sem reinstalar o PWA.

## Consequências
- **Prós:** Melhor experiência para jogadores quando o IP muda; não exige reinstalação do PWA.
- **Prós:** O servidor pode ser acessado novamente rapidamente apenas corrigindo o endereço.
- **Contras:** A PWA precisa manter e validar o último endereço conhecido.
- **Contras:** O processo depende da habilidade do jogador em inserir o IP correto, mas a prévia e o QR code minimizam esse risco.
