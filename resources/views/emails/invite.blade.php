<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Convite Escalei</title>
  <style>
    /* Reset básico para e-mails */
    body, table, td, a {
      text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }
    table, td {
      border-collapse: collapse !important;
    }
    img {
      border: 0;
      line-height: 100%;
      text-decoration: none;
      vertical-align: middle;
    }
    body {
      margin: 0;
      padding: 0;
      width: 100% !important;
      background-color: #f4f4f4;
      font-family: Arial, sans-serif;
    }

    /* Responsividade básica */
    @media only screen and (max-width: 600px) {
      .container {
        width: 100% !important;
        padding: 20px !important;
      }
      .content {
        padding: 20px !important;
      }
      h2 {
        font-size: 20px !important;
      }
      a.button {
        display: block !important;
        width: 100% !important;
        box-sizing: border-box !important;
      }
      .tag {
        display: block !important;
        margin: 4px 0 !important;
      }
    }
  </style>
</head>
<body>
  <table width="100%" bgcolor="#f4f4f4" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center" style="padding: 40px 20px;">
        <table class="container" width="600" bgcolor="#ffffff" cellpadding="0" cellspacing="0" style="max-width:600px; border-radius:8px; overflow:hidden;">
          <tr>
            <td class="content" style="padding: 30px;">
              <h2 style="color:#1e3a8a; margin-top:0;">Convite para participar do Escalei</h2>

              <p style="margin-bottom: 16px;">Olá!</p>

              <p style="margin-bottom: 16px;">
                Você foi convidado para participar da igreja
                <strong>{{ $invite->church->name ?? 'sua igreja' }}</strong>.
              </p>

              @if($invite->areas->isNotEmpty())
              <p style="margin-bottom: 16px;">
                <strong>Áreas:</strong><br>
                @foreach($invite->areas as $area)
                  <span class="tag" style="display:inline-block; background:#e0e7ff; padding:6px 10px; border-radius:4px; margin:3px 2px; font-size:14px;">
                    {{ $area->name }}
                  </span>
                @endforeach
              </p>
              @endif

              @if($invite->roles->isNotEmpty())
              <p style="margin-bottom: 16px;">
                <strong>Funções:</strong><br>
                @foreach($invite->roles as $role)
                  <span class="tag" style="display:inline-block; background:#dbeafe; padding:6px 10px; border-radius:4px; margin:3px 2px; font-size:14px;">
                    {{ $role->name }}
                  </span>
                @endforeach
              </p>
              @endif

              <p style="margin-bottom: 16px;">Clique no botão abaixo para confirmar seu cadastro:</p>

              <p style="text-align:center; margin: 30px 0;">
                <a href="{{ $url }}" class="button"
                  style="background:#1e3a8a; color:white; padding:12px 24px; border-radius:6px; text-decoration:none; font-weight:bold; display:inline-block;">
                  Confirmar e Fazer Cadastro
                </a>
              </p>

              <p style="color:#666; font-size:14px;">
                Este convite expira em {{ $invite->expires_at->format('d/m/Y') }}.
              </p>

              <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;" />

              <p style="font-size:13px; color:#999; margin:0;">
                Se você não esperava este convite, pode ignorar este e-mail.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
