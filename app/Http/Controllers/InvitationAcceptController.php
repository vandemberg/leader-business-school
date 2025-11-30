<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Models\PlatformUser;
use Illuminate\Http\Request;

class InvitationAcceptController extends Controller
{
    public function show(string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->with('platform')
            ->first();

        if (!$invitation) {
            return view('invitations.error', [
                'message' => 'Convite não encontrado'
            ]);
        }

        if ($invitation->isExpired()) {
            return view('invitations.error', [
                'message' => 'Este convite expirou. Entre em contato para solicitar um novo convite.'
            ]);
        }

        if ($invitation->isAccepted()) {
            return view('invitations.error', [
                'message' => 'Este convite já foi utilizado.'
            ]);
        }

        // Verificar se usuário existe
        $user = User::where('email', $invitation->email)->first();
        if (!$user) {
            // Se não existe, redirecionar para registro
            return redirect()->route('invite.register', ['token' => $token]);
        }

        // Verificar se já está na plataforma
        $alreadyInPlatform = PlatformUser::where('user_id', $user->id)
            ->where('platform_id', $invitation->platform_id)
            ->exists();

        if ($alreadyInPlatform) {
            return view('invitations.error', [
                'message' => 'Você já está cadastrado nesta plataforma.'
            ]);
        }

        return view('invitations.accept', [
            'invitation' => $invitation,
            'user' => $user
        ]);
    }

    public function store(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return back()->withErrors(['token' => 'Convite não encontrado']);
        }

        if ($invitation->isExpired()) {
            return back()->withErrors(['token' => 'Este convite expirou']);
        }

        if ($invitation->isAccepted()) {
            return back()->withErrors(['token' => 'Este convite já foi utilizado']);
        }

        // Verificar se usuário existe
        $user = User::where('email', $invitation->email)->first();
        if (!$user) {
            return redirect()->route('invite.register', ['token' => $token]);
        }

        // Verificar se já está na plataforma
        $alreadyInPlatform = PlatformUser::where('user_id', $user->id)
            ->where('platform_id', $invitation->platform_id)
            ->exists();

        if ($alreadyInPlatform) {
            return back()->withErrors(['platform' => 'Você já está cadastrado nesta plataforma']);
        }

        // Criar associação PlatformUser
        PlatformUser::create([
            'user_id' => $user->id,
            'platform_id' => $invitation->platform_id,
        ]);

        // Atualizar current_platform_id se não tiver
        if (!$user->current_platform_id) {
            $user->update(['current_platform_id' => $invitation->platform_id]);
        }

        // Marcar convite como aceito
        $invitation->update(['accepted_at' => now()]);

        return redirect()->route('login')->with('status', 'Convite aceito com sucesso! Você já pode fazer login.');
    }
}

