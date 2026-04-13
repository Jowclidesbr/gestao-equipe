<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Colaboradores</title>
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
    <h1>Relatório de Colaboradores</h1>
    <p class="meta">Gerado em {{ now()->format('d/m/Y H:i') }} — {{ $employees->count() }} registros</p>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Departamento</th>
                <th>Cargo</th>
                <th>Contrato</th>
                <th>Turno</th>
                <th>Admissão</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $emp)
            <tr>
                <td>{{ $emp->user->name ?? '' }}</td>
                <td>{{ $emp->user->email ?? '' }}</td>
                <td>{{ $emp->department->name ?? '—' }}</td>
                <td>{{ $emp->jobPosition->title ?? '—' }}</td>
                <td>{{ strtoupper($emp->contract_type) }}</td>
                <td>{{ $emp->shift ?? '—' }}</td>
                <td>{{ $emp->admission_date?->format('d/m/Y') ?? '' }}</td>
                <td>{{ match($emp->status) { 'active'=>'Ativo','inactive'=>'Inativo','on_leave'=>'Licença','terminated'=>'Desligado',default=>$emp->status } }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
