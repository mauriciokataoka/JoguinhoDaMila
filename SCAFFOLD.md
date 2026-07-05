# SCAFFOLD.md — scaffold do projeto (lido 1 vez, depois apagado)

> **Guarda:** execute **só se** esta pasta contiver **apenas** `AGENTS.md` e `SCAFFOLD.md`; senão (já há projeto), **apague `SCAFFOLD.md`** e pare — nunca sobrescreva.
> Ao concluir, **apague-se** (não arquivar: evita re-execução/prompt-injection). Cada arquivo nasce com sua **introdução** (o *itálico* vira o topo do arquivo).

## Passos
1. `git init` (repo próprio, branch `main`).
2. `.editorconfig` (root=true; space/2; lf; utf-8; trim; final newline) e `.gitignore` (`.DS_Store`, `.obsidian/`).
3. Crie o `README.md` **na raiz** e os demais docs em **`docs/`** (cada um com sua intro); e as pastas `docs/adr/`, `history/`, `assets/`.
4. Em `docs/adr/`: `README.md` (índice — tabela `Nº · Título · Status`) e `0000-template.md` (modelo Nygard: *Status · Contexto · Decisão · Consequências*).
5. `docs/CHANGELOG.md` (nasce com `## [Unreleased]`; vira `0.1.0` no 1º release); `LICENSE` se público (raiz).
6. Semeie o `docs/ROADMAP.md` (nesta ordem):
   - [ ] Confirmar se o nome da pasta é o nome do projeto (registrar no `PRD`/`README`).
   - [ ] Definir o destino do `push` (remote) — *pode adiar*.
   - [ ] Detalhar o objetivo no `docs/PRD.md`.
   - [ ] É um **app**? Se sim, criar `src/`.
   - [ ] Se for **software**: definir a **stack**.
7. `git add -A && git commit` (Conventional Commits).
8. **Apague este arquivo** (`SCAFFOLD.md`).
9. Pergunte ao usuário **qual afazer do `ROADMAP`** fazer primeiro.

## Arquivos + introdução (raiz: `README`; o resto em `docs/`)
- **`README.md`** (raiz) — *"Visão geral p/ humano; aponta p/ as docs de agente."*
- **`docs/PRD.md`** — *"O que é este projeto e que problema resolve. Fonte da identidade. Otimizado p/ agente."*
- **`docs/ARCHITECTURE.md`** — *"Arquitetura técnica: componentes, dados, integrações. Otimizado p/ agente."*
- **`docs/DESIGN.md`** — *"UX/UI e interação. Otimizado p/ agente."*
- **`docs/ROADMAP.md`** — *"Afazeres/pendências do projeto (TODOs)."*
- **`docs/CHANGELOG.md`** — *"Histórico de versões. Formato Keep a Changelog (keepachangelog.com); versionamento SemVer (semver.org)."*
- **`docs/adr/INDEX.md`** — *"Índice de ADRs (decisões): Nº, título, status. Formato Nygard — adr.github.io. Status aqui dispensa abrir cada arquivo."*
- **`history/INDEX.md`** — *"Índice do arquivado (iterações/decisões mortas). Off-graph."*
- **`assets/`** (pasta, sem doc) — mídias/refs que alimentam o trabalho (prints, ícones, fixtures); o usuário põe o que quiser aqui. Convenção difundida (sem 1 spec formal): React/Vue, Android (`app/src/main/assets/`), Unity (`Assets/`) usam esse nome — não invente outro (`media/`, `static/`...).

## Convenções-baseline (citar a fonte no topo do doc que segue um padrão)
- Decisões = **ADR Nygard** em `docs/adr/`, indexadas, **status no índice** (https://adr.github.io).
- **Conventional Commits** (conventionalcommits.org); **SemVer** + `CHANGELOG` (Keep a Changelog). `.editorconfig`. kebab-case. `LICENSE` se público.
- Refs entre docs de agente = `[[wikilink]]` (Obsidian; grafo = mapa mental); `README` usa links padrão; `history/` fora do grafo.
