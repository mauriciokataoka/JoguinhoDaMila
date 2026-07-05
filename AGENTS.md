# AGENTS.md

> Segue a convenção [agents.md](https://agents.md). **Retomada:** leia este arquivo → [[ROADMAP]] (o que fazer) + `docs/adr/` (porquês). Tudo relevante referenciado aqui; arquivado é off-graph.

## Início
- Se `SCAFFOLD.md` existir nesta pasta (não procurar aninhado): execute-o **1×**. Senão: siga o método.
- Config do agente: se você tem um arquivo de config padrão (ex.: `CLAUDE.md`), **crie-o na 1ª execução** referenciando [[AGENTS]] como **fonte canônica** (lida primeiro, com precedência), sem contrariá-lo. Não entra no Mapa; é privado. Use-o só p/ **rascunho efêmero seu**; o que vale p/ o projeto ou outro agente **vai p/ a doc canônica** (ver Método), nunca só no seu config — senão some na troca de agente.

## Mapa (docs canônicas em `docs/`; raiz só `AGENTS`+`README`; não listar diretórios; arquivado = off-graph)
| Arquivo | Quando ler |
|---|---|
| [[PRD]] | identidade, objetivo, produto, problema, solução |
| [[ARCHITECTURE]] | mexer em estrutura |
| [[DESIGN]] | mexer em UX/UI |
| [[README]] | visão p/ humano |
| [[ROADMAP]] | **sempre, p/ retomar** — afazeres/pendências |
| `docs/adr/INDEX.md` | decisões e porquês — **sob demanda**; status no índice |
| [[CHANGELOG]] | histórico de versões |
| `history/INDEX.md` | arquivado — off-graph (só se vasculhar) |

## Método
- Iteração = brainstorm: questionar, checar consistência, digerir em detalhe **na doc** — o chat recebe só o essencial (ver `Resposta` abaixo), nunca fica vazio de conteúdo útil.
- **Resposta:** o definitivo/simples → responda já, curto. O que pede decisão ou altera doc → vira tópico. No chat: a resposta + os **títulos** dos pendentes + a **descrição de UM só** tópico (o mais importante × abrangência) + **um pedido**.
- **Documentar é execução direta, sem pedir permissão** — de forma **objetiva** (≠ reduzida): **tópico = título + explicação**, nunca só o título (outro agente não deve inventar a explicação de um título alheio). Todos os tópicos ficam documentados em detalhe, autossuficientes p/ qualquer agente retomar e apresentá-los *as-it-is*.
- **Decisões significativas → ADR** (`docs/adr/`, formato Nygard; indexadas; aberta = `status: proposed`).
- **Afazeres → [[ROADMAP]]**.
- **Persistência imediata:** doc/ROADMAP atualizados **antes** de responder no chat — a conversa pode ser abandonada a qualquer momento e outro agente retoma **só pelas docs**, sem handoff. O que **aguarda decisão do dono** entra no [[ROADMAP]] (ou ADR `proposed`) **antes** da pergunta no chat; sai quando decidido.
- A cada iteração, revisar [[ROADMAP]] + ADRs `proposed` (um brainstorm pode resolver sem o usuário notar).
- Doc canônica = estado atual; `history/` = off-graph.
- **Formato Obsidian:** refs entre docs = `[[wikilink]]` (grafo = mapa mental); `README` usa links padrão (GitHub); `history/` fica fora do grafo.
- **Propor→aprovar→executar** = só p/ mudança **estrutural** (I0: novo tipo de artefato/termo/doc/pasta) ou p/ alterar **este arquivo** (I2: exige aprovação explícita do dono do projeto — nunca por sua iniciativa). Atualizar **conteúdo** de PRD/ARCHITECTURE/DESIGN/README/ROADMAP em resposta direta a uma instrução do usuário (nome, objetivo, rename, item do ROADMAP) é execução direta.

## Invariantes
- **I0** novo **artefato/termo/doc/pasta**: propor→ratificar→codificar aqui→usar — mudança **abrangente** (registre como ADR, com justificativa); termos preferem o **padrão da indústria**.
- **I1** ler/operar só nesta pasta.
- **I2** este arquivo só muda com aprovação explícita do dono do projeto — nunca por sua iniciativa.

## Comportamento / Operação
- Objetivo claro (senão pergunte); pergunta é pergunta; "né?/não?" confirma premissa; tarefa grande→plano; **doc = fonte de verdade**.
- Git imediato (repo próprio); **push só com aprovação**; commit livre; [Conventional Commits](https://www.conventionalcommits.org/).
- **Versão** ([SemVer](https://semver.org/)): corrente = **tag `vX.Y.Z`**, espelhada no topo do `CHANGELOG` ([Keep a Changelog](https://keepachangelog.com)); bump pelos commits (`fix`→patch, `feat`→minor, `!`/`BREAKING`→major); nasce em `0.1.0`. Release = mover `[Unreleased]`→`[X.Y.Z] - data` + criar a tag. Com código, o manifesto nativo (`package.json`…) espelha a tag.
