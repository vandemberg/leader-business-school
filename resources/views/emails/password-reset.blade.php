<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redefinição de senha</title>
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
    color: #ffffff !important;
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
      <h1>{{ config('app.name') }}</h1>
    </div>

    <div class="content">
      <p>Olá{{ $userName ? ", {$userName}" : '' }}!</p>

      <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
      <p>Clique no botão abaixo para criar uma nova senha:</p>

      <div style="text-align: center;">
        <a href="{{ $url }}" class="button">Redefinir senha</a>
      </div>

      <div class="info">
        <p style="margin: 0;">Este link expira em <strong>{{ $expireMinutes }} minutos</strong>. Se você não solicitou a redefinição de senha, ignore este e-mail.</p>
      </div>

      <p>Ou copie e cole este link no seu navegador:</p>
      <p style="word-break: break-all; color: #2563eb;">{{ $url }}</p>
    </div>

    <div class="footer">
      <p>Este é um e-mail automático, por favor não responda.</p>
      <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
    </div>
  </div>
</body>

</html>
