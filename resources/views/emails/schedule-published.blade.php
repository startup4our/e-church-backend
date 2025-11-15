<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Escala Publicada</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      line-height: 1.6;
      color: #333;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 8px;
      padding: 30px;
    }
    h2 {
      color: #1e3a8a;
      margin-top: 0;
    }
    .button {
      display: inline-block;
      padding: 12px 24px;
      background-color: #1e3a8a;
      color: #ffffff;
      text-decoration: none;
      border-radius: 6px;
      margin: 20px 0;
    }
    .button:hover {
      background-color: #1e40af;
    }
    p {
      margin-bottom: 16px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Você foi escalado!</h2>
    
    <p>Você foi escalado para <strong>{{ $schedule->name }}</strong>, dia {{ $formattedDate }}, visualize no app!</p>
    
    <p style="text-align: center;">
      <a href="{{ $url }}" class="button">Ver Escala no App</a>
    </p>
    
    <p>Obrigado!</p>
  </div>
</body>
</html>

