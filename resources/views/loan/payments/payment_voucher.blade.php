<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PLATAFORMA VIRTUAL ADMINISTRATIVA - MUSERPOL </title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>

<body>
    @php ($plural = count($lenders) > 1)
    @php ($n = 1)
    @include('partials.header', $header)
    <div>
        <table>
            <tr>
                <td class="w-25">
                    &nbsp;
                </td>
                <td class="w-50">
                    &nbsp;
                    <div class="font-semibold leading-tight text-center m-b-10 text-3xl">{{ $title }}</div>
                </td>
                <td class="w-25">
                    <div class="font-semibold leading-tight rounded-full border text-center m-b-10 text-2xl">Bs. {{  Util::money_format($voucher->total)}} </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="block">
        <table class="table-info border w-100 my-20 text-2xl">
            <tr>
                <td class="font-semibold w-25">
                    Recibimos de:
                </td>
                <td class="w-75" colspan="3">
                    {{ $lenders[0]->full_name }}
                </td>
            </tr>
            <tr>
                <td class="font-semibold">
                    La suma de:
                </td>
                <td class="uppercase" colspan="3">
                {{  Util::money_format($voucher->total, true)}}
                </td>
            </tr>
            <tr>
                <td class="font-semibold">
                    Por Concepto de:
                </td>
                <td class="uppercase" colspan="3">
                    {{ $voucher->voucher_type->name }}
                </td>
            </tr>
            <tr>
                <td class="font-semibold">
                    Forma de Pago:
                </td>
                <td class="uppercase" colspan="3">
                    {{ $voucher->payment_type->name }}
                </td>
            </tr>
            <tr>
                <td class="font-semibold">
                Impresión de Refrendo y Sello de Tesorería:
                </td>
                <td class="uppercase" colspan="3">
                  
                </td>
            </tr>
        </table>
    </div>
    <div class="m-t-25 no-page-break">
        <table>
            <tbody>
                <tr>
                    <td>
                        @php($user = Auth::user())
                        @include('partials.signature_box', [
                            'full_name' => $user->full_name,
                            'position' => $user->position,
                            'employee' => true
                        ])
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="m-b-10">
            ***Esta liquidación no es válida sin el Refrendo y Sello de Tesoreria ***
        </div>
    </div>
    <br>
  
</body>
</html>