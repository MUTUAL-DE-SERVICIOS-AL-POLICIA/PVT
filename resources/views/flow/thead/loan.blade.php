<td class="w-5">Nº</td>
<td class="w-15">Código</td>
<td class="w-15">Modalidad</td>
<td class="w-25">Solicitante</td>
<td class="w-10">CI del solicitante</td>
<td class="w-10">Fecha de solicitud</td>
<td class="w-10">Monto aprobado</td>
<td class="{{ $hasSender ? 'w-10' : 'w-5' }}">Plazo</td>
<td class="w-10">Departamento de Origen</td>
@if (!$hasSender)
<td class="w-10">Remitente</td>
@endif