<p>Estimado/a {{ $quotation->full_name }}</p>
<p>Nos complace informarle que hemos creado una nueva cotización para su evento. A continuación, encontrará los detalles de la misma:</p>

<ul>
    <li>Artista: El artista seleccionado para su evento: {{ $quotation->artist->name }}</li>
    <li>Fecha del Evento: Su evento está programado para el día {{ $quotation->event_date }}</li>
    <li>Duración del Evento: El evento tendrá una duración de {{ $quotation->event_hours }} horas</li>
    <li>Ciudad: El evento se llevará a cabo en la ciudad de {{ $quotation->city }}</li>
    <li>Dirección: La dirección exacta del lugar del evento es  {{ $quotation->address }}</li>
    <li>Teléfono: Su numero de Contacto es {{ $quotation->phone }}</li>
    <li>Correo electrónico: Su Correo Electronico es el siguiente: {{ $quotation->email }}</li>
    <li>El costo total estimado para su evento es de: $ {{ $quotation->price }}</li>
</ul>

<p>Gracias por solicitar tu Cotizacion.</p>
<p>Saludos,</p>
<p>Musica GSM</p>

