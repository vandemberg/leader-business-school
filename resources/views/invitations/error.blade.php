@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">Erro no Convite</h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading">Ops! Algo deu errado</h5>
                        <p>{{ $message ?? 'Ocorreu um erro ao processar seu convite.' }}</p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary">Ir para Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

