<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
    <title>PLATAFORMA VIRTUAL DE TRÁMITES - MUSERPOL </title>
</head>

<body>
    @php ($n = 1)
    @include('partials.header', $header)
    <div class="block">
        
        <div class="font-semibold leading-tight text-center mb-5 text-2xl"> {{ $title }} </div>

        <br>
        <div class="block">
        <table style="font-size:13px;" class="table-info w-100 text-center uppercase my-10">
            <tr class="bg-grey-darker text-white">
                <td colspan="4" style="text-align: center; font-weight: bold;">
                    TIPO DE PROPIEDAD (Marque el tipo de propiedad)
                </td>
            </tr>
            <tr>
                <td class="data-row py-5 w-25">1. PROPIO
                    <span class="inline-block m-r-15" style="width: 40px; height: 15px; border: 1px solid #000; float: right;"></span>
                </td>
                <td class="data-row py-5 w-25">2. ANTICRETICO
                    <span class="inline-block m-r-15" style="width: 40px; height: 15px; border: 1px solid #000; float: right;"></span>
                </td>
                <td class="data-row py-5 w-25">3. ALQUILER
                    <span class="inline-block m-r-15" style="width: 40px; height: 15px; border: 1px solid #000; float: right;"></span>
                </td>
                <td class="data-row py-5 w-25">4. OTRO
                    <span class="inline-block m-r-15" style="width: 40px; height: 15px; border: 1px solid #000; float: right;"></span>
                </td>

            </tr>
            <tr class="bg-grey-darker text-white">
                <td colspan="2" style="text-align: center; font-weight: bold;">
                    ESPECIFIQUE, SI MARCO LA OPCIÓN 4.OTRO
                </td>
                <td colspan="2" style="text-align: center; font-weight: bold;">
                    TIEMPO QUE RESIDE EN EL LUGAR
                </td>
            </tr>
            <tr>
                <td colspan="2" class="data-row py-5 w-50 text-justify px-5">ESPECIFICAR: 
                    <span class="w-65 inline-block" style=" border-bottom: 1px dotted #000; position: relative; top: 2px;"></span>
                </td>
                <td colspan="4" class="data-row py-5 w-50 text-justify px-5">ESPECIFICAR:
                    <span class="w-65 inline-block" style=" border-bottom: 1px dotted #000; position: relative; top: 2px;"></span>
                </td>
            </tr>
            <tr class="bg-grey-darker text-white">
                <td style="text-align: center; font-weight: bold;">
                    DEPARTAMENTO
                </td>
                <td style="text-align: center; font-weight: bold;">
                    ZONA / BARRIO / URB.
                </td>
                <td style="text-align: center; font-weight: bold;">
                    CALLE / AV. / CAM. / CARR.
                </td>
                <td style="text-align: center; font-weight: bold;">
                    N° DOM.
                </td>
            </tr>
            <tr>
                <td class="data-row py-5 w-25">{{$address->city_name}}</td>
                <td class="data-row py-5 w-25">{{$address->zone}}</td>
                <td class="data-row py-5 w-25">{{$address->street}}</td>
                <td class="data-row py-5 w-25">{{$address->number_address}}</td>
            </tr>
            <tr class="bg-grey-darker text-white">
                <td colspan="4" style="text-align: center; font-weight: bold;">
                    COND. / EDIF. / TORRE (BLOQUE, PISO, N° DPTO)
                </td>
            </tr>
            <tr>
                <td colspan="4" class="data-row py-5 w-100">
                    @if(!empty($address->housing_unit))
                        {{ $address->housing_unit }}
                    @else
                        &nbsp;
                    @endif
                </td>
            </tr>
            <tr class="bg-grey-darker text-white">
                <td colspan="4" style="text-align: center; font-weight: bold;">
                    REFERENCIA
                </td>
            </tr>
            <tr>
                <td colspan="4" class="data-row py-5 w-100">
                @if(!empty($address->description))
                        {{ $address->description }}
                    @else
                        &nbsp;
                    @endif
                </td>
            </tr>
            <tr class="bg-grey-darker text-white">
                <td colspan="4" style="text-align: center; font-weight: bold;">
                    UBICACIÓN
                </td>
            </tr>
            <tr>
                <td colspan="4" class="data-row py-5 w-100" style="height: 800px">
                    @if(isset($image_map))
                        <img src="{{ $image_map }}" style="max-width: 100%; display: block; margin: 0 auto;" />
                    @endif
                </td>
            </tr>

        </table>

        <table style="font-size:11px;">

            <div class='text-center m-t-100'>
                <div>
                    <hr style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0;" width="250px">
                </div>
                @if(!$affiliate->dead)
                    <div>
                        {{ $affiliate->full_name }}
                    </div>
                    @if(isset( $affiliate->identity_card))
                        <div>
                            C.I. {{ $affiliate->identity_card }}
                        </div>
                    @endif                
                @else
                <div>
                   {{ $spouse->full_name }}
                </div>
                    @if(isset( $spouse->identity_card))
                        <div>
                            C.I. {{ $spouse->identity_card }}
                        </div>
                    @endif
                @endif
            </div>                            
        </table>
    </div>
</body>
</html>