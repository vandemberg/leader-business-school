<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite para a Plataforma</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
        }
        .content {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
        }
        .info {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $platform->name ?? 'Plataforma' }}</h1>
        </div>

        <div class="content">
            <p>Olá!</p>

            @if($isNewUser)
                <p>Você foi convidado para se cadastrar na plataforma <strong>{{ $platform->name ?? 'nossa plataforma' }}</strong>.</p>
                <p>Para completar seu cadastro e começar a usar a plataforma, clique no botão abaixo:</p>
            @else
                <p>Você foi convidado para acessar a plataforma <strong>{{ $platform->name ?? 'nossa plataforma' }}</strong>.</p>
                <p>Como você já possui uma conta, basta aceitar o convite clicando no botão abaixo:</p>
            @endif

            <div style="text-align: center;">
                <a href="{{ $inviteUrl }}" class="button">
                    @if($isNewUser)
                        Cadastrar-se Agora
                    @else
                        Aceitar Convite
                    @endif
                </a>
            </div>

            <div class="info">
                <p><strong>Importante:</strong></p>
                <p>Este convite expira em {{ $invitation->expires_at->format('d/m/Y H:i') }}.</p>
                <p>Se você não solicitou este convite, pode ignorar este e-mail.</p>
            </div>

            <p>Ou copie e cole este link no seu navegador:</p>
            <p style="word-break: break-all; color: #2563eb;">{{ $inviteUrl }}</p>
        </div>

        <div class="footer">
            <p>Este é um e-mail automático, por favor não responda.</p>
            <p>&copy; {{ date('Y') }} {{ $platform->name ?? 'Plataforma' }}. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>

