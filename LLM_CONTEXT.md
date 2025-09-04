# Contexto para LLM — Backend (Laravel)

Este documento resume o propósito, a arquitetura e os fluxos principais do backend para orientar LLMs em tarefas de leitura, manutenção e extensão do código.

## Objetivo do Projeto

Plataforma de ensino (LMS) para a Leader Business School com:
- Catálogo de cursos, módulos e vídeos.
- Acompanhamento de progresso do aluno por vídeo assistido.
- Experiência autenticada com dashboard e visualização de conteúdo.
- Pipeline automatizado para processar novos vídeos do YouTube: baixar áudio, transcrever (OpenAI Whisper) e gerar newsletter (Chat completions) a partir da transcrição.
- Suporte a múltiplas plataformas (multi-tenant leve) via `Platform` e associação `PlatformUser`.

## Pilha Técnica

- Framework: Laravel (PHP)
- Frontend: Inertia.js + React/TypeScript (servindo via Vite) — ver `resources/js`
- Banco de dados: migrations e Eloquent ORM
- Estilos: Tailwind CSS
- Infra: Docker (arquivos `Dockerfile`, `docker-compose.yml`)
- Testes: PHPUnit (pasta `tests`)

## Rotas e Fluxos Principais

- Arquivo: `routes/web.php`
  - Protegidas por `auth`: `dashboard`, `courses.index`, `courses.show`, `courses.watch`, `courses.videos.show`, `courses.videos.store` (marcar vídeo como concluído), `teachers.index`, `profile.*`.
  - Rota raiz redireciona para `/dashboard`.

- Dashboard
  - Controller: `app/Http/Controllers/DashboardController.php`
  - Lógica: carrega cursos, resolve `platform_id` (da sessão ou cria relação `PlatformUser`), calcula progresso por curso para o usuário autenticado, renderiza `Pages/Dashboard` via Inertia.
  - Cálculo de progresso: conta vídeos do curso e quantos estão em `WatchVideo::STATUS_WATCHED` para o usuário, gerando `%` arredondado.

- Cursos e Visualização
  - Controllers: `CoursesController` (lista e show) e `WatchController` (assistir/complete).
  - Fluxo de assistir: `/courses/{course}/watch` descobre o “vídeo atual” do usuário e redireciona para `/courses/{course}/videos/{video}`; renderiza `Pages/Watch/Index` com lista de vídeos e status `watched`.
  - Conclusão de vídeo: POST `/videos/{video}/complete` atualiza `WatchVideo` para `finished`.

## Modelos e Relacionamentos

- `Course` (`app/Models/Course.php`)
  - Tem muitos `Module`; vídeos via `hasManyThrough(Video, Module)`.
  - Método `currentVideo($user)`: retorna o próximo vídeo a assistir baseado em histórico `WatchVideo` do usuário (último em andamento; senão o próximo após o último concluído; fallback para o primeiro).

- `Video` (`app/Models/Video.php`)
  - Pertence a `Course` e `Module`; tem muitos `WatchVideo`.
  - Dispara evento `VideoCreated` no `created` (hook `booted`).

- `WatchVideo` (`app/Models/WatchVideo.php`)
  - Marca status por usuário/vídeo: `watching` ou `finished`.

- Multi-plataforma
  - `Platform` e `PlatformUser`: associação N:N para isolar branding/contexto por plataforma (carregado no Dashboard).

## Pipeline de Processamento de Vídeo (AI)

- Evento: `app/Events/VideoCreated.php` (carrega `$video`).
- Listener: `app/Listeners/HandleVideoCreated.php` (fila/async via `ShouldQueue`):
  1) Baixa áudio do YouTube com `YouTubeService::downloadAudio($video->url)` usando `yt-dlp` (requer binário instalado); salva em `storage/app/public/audio/<videoId>.mp3`.
  2) Transcreve áudio via `OpenAIService::transcribeAudio()` (modelo Whisper `whisper-1`).
  3) Atualiza `Video->transcription` e salva.
  4) Gera newsletter com `OpenAIService::generateNewsletter()` (modelo chat `gpt-4.1-nano`) usando a transcrição e o título do vídeo.
  5) Salva e envia newsletter com `NewsletterService` (persistência em arquivo e exemplo de envio por e-mail/log).

- Serviços:
  - `app/Services/YouTubeService.php`: encapsula chamada ao `yt-dlp` (via `Symfony\Process`), prepara diretórios e valida sucesso.
  - `app/Services/OpenAIService.php`: cliente OpenAI (`openai-php/client`), usa `services.openai.api_key`; funções para Whisper e Chat.
  - `app/Services/NewsletterService.php`: salvar conteúdo (ex.: arquivo) e enviar (ex.: log/email). 

- Providers: singletons registrados em `app/Providers/AppServiceProvider.php` para `OpenAIService` e `NewsletterService`.

## Autenticação e Layout

- Autenticação nativa Laravel (rotas em `routes/auth.php`), páginas React para Login/Register/etc. via Inertia.
- Layouts em `resources/js/Layouts` gerenciam navegação e cabeçalhos.

## Integração Frontend (Inertia)

- Controllers retornam `Inertia::render()` com props (`courses`, `platform`, `videos`, `currentVideo`), consumidas por componentes React em `resources/js/Pages`.
- Exemplo: `Dashboard` consome `courses` e `platform`; `Watch/Index` carrega player YouTube e lista vídeos.

## Configurações Importantes

- `.env`/`config/services.php`: `OPENAI_API_KEY` obrigatório para `OpenAIService`.
- Dependência de sistema: `yt-dlp` instalado e disponível no PATH para baixar áudio do YouTube.
- Docker: ver `Dockerfile` e `docker-compose.yml` (e diretório `docker-compose/`) para serviços de app/web.

## Como Rodar (resumo)

1) `cp .env.example .env` e ajustar credenciais (`OPENAI_API_KEY` etc.).
2) `composer install` e `npm install`.
3) `php artisan key:generate` e `php artisan migrate`.
4) Rodar app: `php artisan serve` e `npm run dev` (ou Docker via `docker-compose up -d`).

## Padrões para LLMs

- Preferir mudanças focadas: manter estilo, nomes e padrões de Inertia/Controllers já adotados.
- Não introduzir novas dependências externas sem necessidade; se precisar, documentar no final do arquivo.
- Ao alterar fluxo de vídeo, garantir consistência com `WatchVideo` e `currentVideo()`.
- Em código que usa OpenAI, validar chaves e tratar exceções/logs.

