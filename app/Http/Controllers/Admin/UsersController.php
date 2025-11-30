<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Invitation;
use App\Models\PlatformUser;
use App\Models\WatchVideo;
use App\Mail\InvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $platformId = $this->getPlatformId($request);
        $search = $request->get('search');

        $query = User::query();

        // Filtrar usuários que pertencem à plataforma atual
        if ($platformId) {
            $query->whereHas('platforms', callback: function ($q) use ($platformId) {
                $q->where('platform_id', $platformId);
            });
        }

        if (isset($search) && $search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return response()->json($users, 200);
        }

        $videoIds = $this->getPlatformVideoIds($platformId);
        $totalVideos = $videoIds->count();

        $completedByUser = collect();

        if ($totalVideos > 0) {
            $completedByUser = WatchVideo::query()
                ->select('user_id')
                ->selectRaw('COUNT(DISTINCT video_id) as completed_videos')
                ->whereIn('video_id', $videoIds)
                ->whereIn('user_id', $users->pluck('id'))
                ->where('status', WatchVideo::STATUS_WATCHED)
                ->groupBy('user_id')
                ->pluck('completed_videos', 'user_id');
        }

        $users->transform(function ($user) use ($completedByUser, $totalVideos) {
            $completed = (int) ($completedByUser->get($user->id) ?? 0);
            $progress = $totalVideos > 0
                ? (int) round(($completed / $totalVideos) * 100)
                : 0;

            $user->setAttribute('completed_videos', $completed);
            $user->setAttribute('progress_percent', $progress);

            return $user;
        });

        return response()->json($users, 200);
    }

    public function show(User $user)
    {
        $platformId = $this->getPlatformId(request());

        // Validar que usuário pertence à plataforma
        if ($platformId) {
            $hasAccess = PlatformUser::where('user_id', $user->id)
                ->where('platform_id', $platformId)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'Usuário não pertence à sua plataforma');
            }
        }

        return response()->json($user, 200);
    }

    public function invite(Request $request)
    {
        $platformId = $this->getPlatformId($request);

        if (!$platformId) {
            abort(403, 'Plataforma não identificada');
        }

        $data = $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        // Verificar se já existe um usuário com este email
        $existingUser = User::where('email', $data['email'])->first();
        $isNewUser = !$existingUser;

        if ($existingUser) {
            // Verificar se já está na plataforma
            $alreadyInPlatform = PlatformUser::where('user_id', $existingUser->id)
                ->where('platform_id', $platformId)
                ->exists();

            if ($alreadyInPlatform) {
                return response()->json([
                    'message' => 'Usuário já está nesta plataforma'
                ], 422);
            }
        }

        // Verificar se já existe um convite pendente para este email nesta plataforma
        $existingInvitation = Invitation::where('email', $data['email'])
            ->where('platform_id', $platformId)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return response()->json([
                'message' => 'Já existe um convite pendente para este email nesta plataforma'
            ], 422);
        }

        // Criar convite
        $token = Str::random(64);
        $expiresAt = now()->addDays(7); // Convite expira em 7 dias

        $invitation = Invitation::create([
            'email' => $data['email'],
            'platform_id' => $platformId,
            'role' => User::ROLE_USER, // Sempre student para alunos
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_by' => auth()->id(),
        ]);

        // Carregar relacionamento da plataforma
        $invitation->load('platform');

        // Enviar email com link de convite
        Mail::to($data['email'])->send(new InvitationMail($invitation, $isNewUser));

        return response()->json([
            'message' => 'Convite enviado com sucesso',
            'invitation' => $invitation
        ], 201);
    }

    public function removeFromPlatform(Request $request, User $user)
    {
        $platformId = $this->getPlatformId($request);

        if (!$platformId) {
            abort(403, 'Plataforma não identificada');
        }

        // Verificar se usuário pertence à plataforma
        $platformUser = PlatformUser::where('user_id', $user->id)
            ->where('platform_id', $platformId)
            ->first();

        if (!$platformUser) {
            return response()->json([
                'message' => 'Usuário não pertence a esta plataforma'
            ], 404);
        }

        // Remover associação
        $platformUser->delete();

        // Se o current_platform_id do usuário era esta plataforma, limpar
        if ($user->current_platform_id === $platformId) {
            $user->update(['current_platform_id' => null]);
        }

        return response()->json([
            'message' => 'Usuário removido da plataforma com sucesso'
        ], 200);
    }
}
