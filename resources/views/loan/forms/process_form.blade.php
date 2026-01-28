<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ $file_title }}</title>
    <link rel="stylesheet" href="{{ public_path('/css/report-print.min.css') }}" media="all"/>
</head>
<style>
  .tcode {
    width: 100%;
    border-collapse: separate;   /* importante para border-radius */
    border-spacing: 0;
    font-size: 12px;
    text-transform: uppercase;
    table-layout: fixed;         /* anchos estables en PDF */
  }
  .tcode td {
    border: 1px solid #444;
    padding: 8px 10px;
    vertical-align: middle;
  }
  .tcode .left { text-align: left; }
  .tcode .center { text-align: center; }

  /* esquinas redondeadas */
  .tcode tr:first-child td:first-child { border-top-left-radius: 10px; }
  .tcode tr:first-child td:last-child  { border-top-right-radius: 10px; }

  /* evita doble borde interno al usar separate */
  .tcode tr td + td { border-left: 0; }
  .tcode tr + tr td { border-top: 0; }

  .f-10 { font-size:10px; }
  .f-12 { font-size:12px; }
  .f-14 { font-size:14px; }
  .f-16 { font-size:16px; font-weight:bold; }
  .f-20 { font-size:20px; font-weight:bold; }
  .f-24 { font-size:24px; font-weight:bold; }
</style>

<body>
@include('partials.header', $header)
<div class="w-50 text-left f-24">
    <b> HOJA DE TRAMITE </b>
</div>
<br>
<div class="block">
    <b>
        <table class="tcode">
            <colgroup>
                <col style="width:50%">
                <col style="width:25%">
                <col style="width:25%">
            </colgroup>
            <tbody>
                <tr>
                    <td rowspan="3" class="center f-14">
                        <div class="text-center">
                            <img src="{{ public_path("/img/logo.png") }}" class="w-40">
                        </div>
                        <br>
                        {{ $header['direction'] }}
                        <br>
                        {{ $header['unity'] }}
                    </td>
                    <td colspan="2" class="center f-20">
                        {{ $loan->city->name }}
                    </td>
                </tr>
                <tr style="height:90px;">
                    <td colspan="2" class="center f-24">
                        {{ $loan->code }}
                    </td>
                </tr>
                <tr>
                    <td class="center f-14"> FECHA DE SOLICITUD </td>
                    <td class="center f-16"> {{ Carbon::parse($loan->request_date)->format('d/m/Y') }} </td>
                </tr>
            </tbody>
        </table>
    </b>
</div>

<div class="block">
    <b>
        <table class="table-code w-100 text-center uppercase my-10" style="font-size:20px;">
            <tbody>
                <tr style="height:60px;">
                    <td> {{ $loan->modality->name }} </td>
                </tr>
            </tbody>
        </table>
    </b>
</div>

<!-- Tabla de derivaciones -->
<div class="block">
    <b>
        <table class="tcode w-100 text-center uppercase my-10" style="font-size:12px;">
            <tbody>
                @for ($n = 1; $n <= 5; $n++)
                    <tr style="height:25px;">
                        <td class="w-5 text-left f-12">A:</td>
                        <td class="w-45 text-left px-10"></td>
                        <td class="w-50 text-left f-12">FECHA:</td>
                    </tr>
                    <tr style="height:170px;">
                        <td colspan="2" class="w-50 text-center px-10">
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        </td>
                        <td class="w-50 text-left px-10"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </b>
</div>
<br>
<br>
<br>
<br>
<div class="block">
    <b>
        <table class="tcode w-100 text-center uppercase my-10" style="font-size:12px;">
            <tbody>
                @for ($n = 1; $n <= 7; $n++)
                    <tr style="height:25px;">
                        <td class="w-5 text-left f-12">A:</td>
                        <td class="w-45 text-left px-10"></td>
                        <td class="w-50 text-left f-12">FECHA:</td>
                    </tr>
                    <tr style="height:170px;">
                        <td colspan="2" class="w-50 text-left px-10">
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        ---------------------------------------------------------------------------------
                        <br>
                        <br>
                        </td>
                        <td class="w-50 text-left px-10"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </b>
</div>
</body>
</html>
