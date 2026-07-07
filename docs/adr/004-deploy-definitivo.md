# ADR-004: Deploy definitivo iOS vs Android

*Status · Contexto · Decisão · Consequências*

## Status
Proposed

## Contexto
O app servidor deve ser definitivo. A solução de instalar via Apple ID gratuito e rebuildar a cada 7 dias não é aceitável.

O requisito funcional atual define que o servidor roda no celular da Mila. Não dispomos de conta Apple Developer no momento.

## Decisão
- Manter iOS como plataforma alvo do servidor, mas avaliar alternativas de deploy que não dependam de um build semanal.
- Se não for possível obter um deploy iOS definitivo, migrar o servidor para um app Android como plataforma de fallback.

## Consequências
- **Prós:** a decisão cria uma rota clara para um aplicativo servidor definitivo.
- **Contras:** a migração para Android implica reavaliação da stack e possíveis mudanças de plataforma.
- **Contras:** iOS continua sendo desejado, mas a limitação da conta Apple Developer pode impedir a implementação imediata.
