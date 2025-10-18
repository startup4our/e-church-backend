<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Convite eChurch</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px;">
    <div style="max-width: 600px; margin: auto; background: white; border-radius: 8px; padding: 30px;">
        <h2 style="color: #1e3a8a;">Convite para participar do eChurch</h2>

        <p>Olá!</p>

        <p>
            Você foi convidado para participar da igreja 
            <strong>{{ $invite->church->name ?? 'sua igreja' }}</strong>.
        </p>

        @if(isset($invite->area))
            <p>
                Área: <strong>{{ $invite->area->name }}</strong>
            </p>
        @endif

        <p>
            Clique no botão abaixo para confirmar seu cadastro:
        </p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background: #1e3a8a; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none;">
                Confirmar e Fazer Cadastro
            </a>
        </p>

        <p style="color: #666; font-size: 14px;">
            Este convite expira em {{ $invite->expires_at->format('d/m/Y') }}.
        </p>

        <hr style="margin: 20px 0;">
        <p style="font-size: 13px; color: #999;">
            Se você não esperava este convite, pode ignorar este e-mail.
        </p>
    </div>
</body>
</html>
