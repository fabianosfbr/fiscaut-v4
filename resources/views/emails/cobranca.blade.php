<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subjectContent }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        td {
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        img {
            border: 0;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
            height: auto;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f4f7;
            padding-bottom: 60px;
            padding-top: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eaeaec;
        }
        .header {
            padding: 30px 40px;
            text-align: center;
            background-color: #ffffff;
            border-bottom: 1px solid #eeeeee;
        }
        .header img {
            max-width: 220px;
            height: auto;
        }
        .content {
            padding: 40px;
            line-height: 1.6;
            font-size: 16px;
            color: #444444;
        }
        .content p {
            margin: 0 0 20px 0;
        }
        .content h1, .content h2, .content h3 {
            color: #222222;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .content a {
            color: #0056b3;
            text-decoration: none;
            font-weight: bold;
        }
        .content a:hover {
            text-decoration: underline;
        }
        .footer {
            padding: 20px 40px;
            background-color: #f9f9fb;
            text-align: center;
            font-size: 13px;
            color: #888888;
            border-top: 1px solid #eeeeee;
            line-height: 1.5;
        }
        .footer p {
            margin: 0 0 10px 0;
        }
        .footer a {
            color: #888888;
            text-decoration: underline;
        }
        @media screen and (max-width: 700px) {
            .main {
                width: 100% !important;
                border-radius: 0 !important;
                border: none !important;
            }
            .content, .header, .footer {
                padding: 20px !important;
            }
            .wrapper {
                padding-top: 0;
                padding-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="main" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <!-- Header -->
            <tr>
                <td class="header">
                    <a href="{{ config('app.url') }}" target="_blank">
                        <img src="https://app.fiscaut.com.br/logo.png" style="max-width: 180px;"  alt="{{ $tenantName }}" />
                    </a>
                </td>
            </tr>
            <!-- Content -->
            <tr>
                <td class="content">
                    {!! $bodyContent !!}
                </td>
            </tr>
            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p>&copy; {{ date('Y') }} {{ $tenantName }}. Todos os direitos reservados.</p>
                    <p>Este é um e-mail automático, por favor não responda diretamente a esta mensagem.</p>        
                </td>
            </tr>
        </table>
    </center>
</body>
</html>