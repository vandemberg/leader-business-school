@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Aceitar Convite - {{ $invitation->platform->name ?? 'Plataforma' }}</h4>
                </div>

                <div class="card-body">
                    <p class="mb-3">Olá, <strong>{{ $user->name }}</strong>!</p>
                    <p class="text-muted">Você foi convidado para acessar a plataforma <strong>{{ $invitation->platform->name ?? 'nossa plataforma' }}</strong>.</p>
                    <p class="text-muted mb-4">Clique no botão abaixo para aceitar o convite e começar a usar a plataforma.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('invite.accept.store', ['token' => $invitation->token]) }}">
                        @csrf

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">E-mail:</label>
                            <div class="col-md-6">
                                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    {{ __('Accept Invitation') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="text-muted small">Após aceitar o convite, você poderá fazer login normalmente.</p>
                        <a href="{{ route('login') }}" class="btn btn-link">Já tenho uma conta - Fazer Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

