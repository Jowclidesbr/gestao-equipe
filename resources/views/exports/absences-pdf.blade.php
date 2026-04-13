<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Afastamentos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #444; }
        h1 { font-size: 16px; color: #EC0000; margin-bottom: 4px; }
        .meta { font-size: 9px; color: #888; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; border-bottom: 2px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) td { background: #fafafa; }
    </style>
</head>
<body>
    <h1>Relatório de Afastamentos</h1>
    <p class="meta">Gerado em {{ now()->format('d/m/Y H:i') }} — {{ $absences->count() }} registros</p>
    <table>
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Departamento</th>
                <th>Tipo</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Dias</th>
                <th>CID</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absences as $ab)
            <tr>
                <td>{{ $ab->employee->user->name ?? '' }}</td>
                <td>{{ $ab->employee->department->name ?? '—' }}</td>
                <td>{{ $ab->type_label }}</td>
                <td>{{ $ab->start_date->format('d/m/Y') }}</td>
                <td>{{ $ab->end_date->format('d/m/Y') }}</td>
                <td>{{ $ab->start_date->diffInDays($ab->end_date) + 1 }}</td>
                <td>{{ $ab->cid_code ?? '—' }}</td>
                <td>{{ \Str::limit($ab->notes ?? '', 60) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
