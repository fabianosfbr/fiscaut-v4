<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            border-bottom: 1px solid #000;
        }
        td {
            padding: 10px;
            text-align: center;
        }
        .col-codigo { width: 20%; }
        .col-etiqueta { width: 50%; }
        .col-valor { width: 30%; }
    </style>
</head>
<body>
    <div class="header">Etiquetas</div>
    <table>
        <thead>
            <tr>
                <th class="col-codigo">Código</th>
                <th class="col-etiqueta">Etiqueta</th>
                <th class="col-valor">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tagged as $item)
                <tr>
                    <td>{{ $item->tag->code }}</td>
                    <td>{{ $item->tag_name }}</td>
                    <td>R$ {{ number_format($item->value, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
