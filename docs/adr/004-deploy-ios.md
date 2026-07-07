# ADR-004: Deploy iOS sem conta Apple Developer

*Status · Contexto · Decisão · Consequências*

## Status
Proposed

## Contexto
O servidor do jogo é obrigatoriamente um app iOS no celular da Mila.
Não dispomos de conta Apple Developer ($99/ano).

Avaliadas as alternativas:
1. Build local com Apple ID gratuito: expira em 7 dias, requer rebuild contínuo (não é aceitável para um app definitivo).
2. AltStore/AltServer: possível, mas não é oficial e não garante estabilidade.
3. Jailbreak: não recomendado para produto sério.
4. Conta Apple Developer: custo $99/ano, solução oficial mas não disponível no momento.

## Decisão
**Pendente**. Duas opções em avaliação:

### Opção A: Encontrar alternativa de deploy iOS
- Avaliar custos e viabilidade de AltStore ou ferramenta equivalente.
- Manter iOS como requisito.
- Verificar se é aceitável usar um método não-oficial de distribuição.

### Opção B: Migrar para Android
- Trocar o app servidor de iOS para Android.
- Android permite sideload mais facilmente e não requer assinatura paga obrigatória.
- Seria um desvio do requisito original, mas garante deploy definitivo sem custos.

## Consequências
- **Opção A (iOS com alternativa):** Continua com requisito iOS, mas pode depender de método não-oficial ou instável.
- **Opção B (Android):** Muda a plataforma do servidor, mas resolve o problema de deploy de forma estável.

**Esta decisão afeta diretamente se o projeto é viável ou precisa mudar de escopo.**
