<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invitation;
use App\Models\User;
use App\Models\PlatformUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /**
     * Lista convites pendentes da plataforma atual (não aceitos e não expirados).
     */
    public function index(Request $request)
    {
        $platformId = $this->getPlatformId($request);

        if (!$platformId) {
            abort(403, 'Plataforma não identificada');
        }

        $invitations = Invitation::query()
            ->where('platform_id', $platformId)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        $list = $invitations->map(function (Invitation $invitation) {
            $existsUser = User::where('email', $invitation->email)->exists();
            $inviteUrl = $existsUser
                ? url("/invite/accept/{$invitation->token}")
                : url("/invite/register/{$invitation->token}");

            return [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'token' => $invitation->token,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'created_at' => $invitation->created_at->toIso8601String(),
                'invite_url' => $inviteUrl,
            ];
        });

        return response()->json($list, 200);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return response()->json([
                'message' => 'Convite não encontrado'
            ], 404);
        }

        if ($invitation->isExpired()) {
            return response()->json([
                'message' => 'Convite expirado'
            ], 422);
        }

        if ($invitation->isAccepted()) {
            return response()->json([
                'message' => 'Convite já foi aceito'
            ], 422);
        }

        // Verificar se usuário já existe
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            // Criar novo usuário - senha obrigatória
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'role' => $invitation->role,
                'current_platform_id' => $invitation->platform_id,
            ]);
        } else {
            // Usuário já existe, apenas adicionar à plataforma
            // Atualizar role se necessário (apenas se for admin)
            if ($invitation->role === User::ROLE_ADMIN && $user->role !== User::ROLE_ADMIN) {
                $user->update(['role' => User::ROLE_ADMIN]);
            }
        }

        // Criar associação PlatformUser se não existir
        PlatformUser::firstOrCreate([
            'user_id' => $user->id,
            'platform_id' => $invitation->platform_id,
        ]);

        // Atualizar current_platform_id se não tiver
        if (!$user->current_platform_id) {
            $user->update(['current_platform_id' => $invitation->platform_id]);
        }

        // Marcar convite como aceito
        $invitation->update(['accepted_at' => now()]);

        return response()->json([
            'message' => 'Convite aceito com sucesso',
            'user' => $user
        ], 200);
    }
}
