# Contexto para LLM — Frontend (React + Inertia)

Este documento resume objetivo, arquitetura e componentes do frontend para orientar LLMs em tarefas de manutenção e evolução.

## Objetivo do Frontend

- Entregar a experiência do aluno autenticado: dashboard com cursos e página de visualização de vídeo com player do YouTube.
- Integrar-se ao Laravel via Inertia, consumindo props vindas dos Controllers.
- Marcar conclusão de vídeos e apresentar progresso de cursos.

## Pilha Técnica

- Framework: React + TypeScript
- Integração: Inertia.js (`@inertiajs/react`)
- Build: Vite
- Estilos: Tailwind CSS
- Player: `react-youtube`

## Estrutura de Pastas (principal)

- `resources/js/app.tsx` e `resources/js/ssr.tsx`: bootstrap Inertia (SPA/SSR).
- `resources/js/Layouts/AuthenticatedLayout.tsx`: layout autenticado (navbar, links, user menu).
- `resources/js/Pages/Dashboard.tsx`: lista cursos e mostra nome da plataforma.
- `resources/js/Pages/Watch/Index.tsx`: player YouTube, lista de vídeos, marcação de conclusão.
- `resources/js/components/*`: botões, modal, navegação, etc.
- `resources/js/utils/seconds-to-string.ts`: formata duração dos vídeos.

## Fluxos Principais

- Dashboard
  - Recebe `auth.user`, `courses`, `platform` via Inertia.
  - Renderiza cards de curso (componente `Thumb` em `components/courses/thumb`).

- Assistir Vídeo
  - Página: `Pages/Watch/Index.tsx`.
  - Props: `course`, `currentVideo`, `videos`, `auth`.
  - Usa `<YouTube videoId={currentVideo.url} ... />`.
  - Ao terminar (`onEnd`), envia POST `axios.post(/videos/{id}/complete)` e faz `router.visit(/courses/{course.id}/watch)` para avançar.
  - Lista lateral indica `watched`/`not-watched`; exibe duração com `secondsToString`.

## Integração com Backend (Inertia)

- Controllers (Laravel) retornam `Inertia::render()` com dados já agregados (ex.: progresso no Dashboard, `currentVideo` no Watch).
- Navegação usa links padrão (`href`) e componentes do Inertia.
- Requisições pontuais (como concluir vídeo) usam `axios` diretamente.

## Estilo e Acessibilidade

- Tailwind para utilitários de layout/cores/spacing.
- Componentes de UI padronizados em `components/*` para consistência.

## Boas Práticas para LLMs

- Manter tipagem de props (`interface ...Props`) e componentes funcionais.
- Preservar contratos de props esperados pelos Controllers (nomes e formas das props Inertia).
- Evitar lógica de negócio no frontend: cálculos de progresso e seleção de `currentVideo` são do backend.
- Para novas páginas, seguir padrão: Layout autenticado + `Head` + props Inertia.

## Executando em Desenvolvimento

- `npm install` e `npm run dev` (Vite).
- O backend deve estar rodando para Inertia entregar as props e rotas funcionarem.

