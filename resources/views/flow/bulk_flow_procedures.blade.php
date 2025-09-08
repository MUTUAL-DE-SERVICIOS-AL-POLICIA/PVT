<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PLATAFORMA VIRTUAL ADMINISTRATIVA - MUSERPOL </title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>

<body>
    @php ($plural = count($procedures) > 1)
    @php ($n = 1)
    @php ($hasSender = !is_null($states['from']))
    @include('partials.header', $header)

    <div class="block">
        <div class="font-semibold leading-tight text-center m-b-10 text-xs uppercase">{{ $title }}</div>
    </div>

    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DEL FLUJO</div>
    </div>

    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-xxs text-white">
                @if ($hasSender)
                <td class="w-50">Área Remitente</td>
                @endif
                <td class="{{ $hasSender ? 'w-50' : 'w-100' }}">Área de Destino</td>
            </tr>
            <tr>
                @if ($hasSender)
                <td class="data-row py-5">{{ $states['from']->name }}</td>
                @endif
                <td class="data-row py-5">{{ $states['to']->name }}</td>
            </tr>
        </table>
    </div>

    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DE{{ $plural ? ' LOS' : 'L' }} TRÁMITE{{ $plural ? 'S' : ''}}</div>
    </div>

    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-xxs text-white">
                @include('flow.thead.' . $type, ['hasSender' => $hasSender])
            </tr>
            @foreach ($procedures as $i => $procedure)
            <tr>
                <td class="data-row py-5">{{ $i + 1 }}</td>
                @include('flow.tbody.' . $type, ['hasSender' => $hasSender, 'procedure' => $procedure])
            </tr>
            @endforeach
            @switch($type)
                @case('loan')
                    @php ($total_cols = $hasSender ? 9 : 10)
                    @break
                @case('loan_payment')
                    @php ($total_cols = $hasSender ? 9 : 10)
                    @break
                @default
                    @php ($total_cols = 1)
            @endswitch
            <tr>
                <td class="data-row py-5 text-center" colspan="{{ $total_cols }}">
                    <span class="font-semibold uppercase">Total trámites: </span>
                    <span class="font-bold uppercase">{{ count($procedures) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="text-justify">

    </div>
    <div class="block no-page-break">
        <div class="m-b-10 text-justify">
            Los trámites mencionados en la lista anterior fueron revisados, validados y derivados en sistema. Por tanto, recomiendo dar continuidad al proceso de cada uno de ellos, para lo cual adjunto las carpetas que contienen la información de respaldo.
        </div>

        <div>
            @php($user = Auth::user() ?? (object)['full_name' => 'prueba', 'position' => 'prueba'])
            @include('partials.signature_box', [
                'full_name' => $user->full_name,
                'position' => $user->position,
                'employee' => true
            ])
        </div>
    </div>
</body>
</html>