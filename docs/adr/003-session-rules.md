# ADR-003: Regras de sessão, ranking e identificação PWA

*Status · Contexto · Decisão · Consequências*

## Status
Accepted

## Contexto
O aplicativo servidor roda no celular da Mila e os clientes se conectam via PWA.
As regras de jogo precisam ser explícitas para garantir comportamento consistente na partida e para que o servidor possa gerenciar corretamente o fluxo de jogadores.

## Decisão
- A ordem de turno será baseada na ordem de chegada dos jogadores.
- O ranking contará todas as partidas e fará pontuação acumulada por nickname.
- O painel do servidor exibirá todos os clientes conectados e terá opção de kick disponível a qualquer momento.
- O token do jogador será armazenado no `localStorage` da PWA para manter a identificação entre partidas.
- O timer de cada jogador será de 10 segundos para sugerir uma letra ou palavra.
- Se o jogador não responder em 10 segundos, a vez passa para o próximo jogador.
- O servidor não terá limite fixo de clientes por partida, mas poderá encerrar conexões quando necessário.
- A palavra secreta pode conter espaços ou hífens; espaços e hífens são exibidos e não precisam ser adivinhados.
- Não há tempo total de partida; a rodada segue até a palavra ser descoberta ou o servidor reiniciar/encerrar a partida.

## Consequences
- Garante um fluxo de jogo previsível e simples para crianças.
- Permite gerenciamento de sala pelo servidor via kick.
- Mantém a identificação do jogador entre partidas sem necessidade de login completo.
- Minimiza frustração ao permitir que a partida continue automaticamente após 10 segundos sem resposta.
