<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Models\PlatformUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationRegisterController extends Controller
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

        // Verificar se usuário já existe (não deveria acontecer, mas por segurança)
        $existingUser = User::where('email', $invitation->email)->first();
        if ($existingUser) {
            return redirect()->route('invite.accept', ['token' => $token]);
        }

        return view('invitations.register', [
            'invitation' => $invitation
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

        // Verificar se usuário já existe
        $existingUser = User::where('email', $invitation->email)->first();
        if ($existingUser) {
            return redirect()->route('invite.accept', ['token' => $token]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Criar novo usuário
        $user = User::create([
            'name' => $data['name'],
            'email' => $invitation->email,
            'password' => Hash::make($data['password']),
            'role' => $invitation->role,
            'current_platform_id' => $invitation->platform_id,
        ]);

        // Criar associação PlatformUser
        PlatformUser::create([
            'user_id' => $user->id,
            'platform_id' => $invitation->platform_id,
        ]);

        // Marcar convite como aceito
        $invitation->update(['accepted_at' => now()]);

        return redirect()->route('login')->with('status', 'Cadastro realizado com sucesso! Você já pode fazer login.');
    }
}

