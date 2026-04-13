<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Férias</title>
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
    <h1>Relatório de Férias</h1>
    <p class="meta">Gerado em {{ now()->format('d/m/Y H:i') }} — {{ $vacations->count() }} registros</p>
    <table>
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Departamento</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Dias</th>
                <th>Abono</th>
                <th>Status</th>
                <th>Submetido</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vacations as $vr)
            <tr>
                <td>{{ $vr->employee->user->name ?? '' }}</td>
                <td>{{ $vr->employee->department->name ?? '—' }}</td>
                <td>{{ $vr->start_date->format('d/m/Y') }}</td>
                <td>{{ $vr->end_date->format('d/m/Y') }}</td>
                <td>{{ $vr->days_requested }}</td>
                <td>{{ $vr->sell_days }}</td>
                <td>{{ $vr->status_label }}</td>
                <td>{{ $vr->submitted_at?->format('d/m/Y') ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
