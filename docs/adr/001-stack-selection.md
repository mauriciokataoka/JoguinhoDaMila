# ADR-001: Escolha da Stack Tecnológica

*Status · Contexto · Decisão · Consequências*

## Status
Accepted

## Context
Precisamos definir a tecnologia para o desenvolvimento do "Joguinho da Mil!a". Requisitos principais incluem:
- Servidor independente (single binary) executável em Windows e macOS.
- Aplicativo cliente com suporte a Mobile (APK) e Web (PWA).

## Decision
**Backend:** Go (Golang). Escolhido pela capacidade de gerar binários estáticos e otimização de concorrência, facilitando o requisito de "arquivo único" para o servidor.
**Frontend:** React Native com Expo. Permite compartilhar lógica entre plataformas Mobile e Web, cobrindo tanto a necessidade de APK quanto PWA.

## Consequences
- **Prós:** Desenvolvimento rápido (code sharing), facilidade de distribuição do binário do servidor.
- **Contras:** Curva de aprendizado para Go se não houver experiência prévia; configuração inicial do Expo pode ser complexa em certas máquinas.
