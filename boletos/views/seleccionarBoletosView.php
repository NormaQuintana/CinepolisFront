<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Boletos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .seat-grid {
            display: grid;
            grid-template-columns: auto repeat(var(--max-cols, 15), 40px);
            grid-auto-rows: 40px;
            grid-gap: 5px;
            padding: 20px 0;
            justify-content: center;
            margin: 0 auto;
            max-width: fit-content;
        }
        .seat {
            width: 40px;
            height: 40px;
            background-color: #e0e0e0;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
        }
        .seat.available {
            background-color: #a8e6cf;
            border-color: #4CAF50;
            cursor: pointer;
        }
        .seat.occupied {
            background-color: #ffadad;
            border-color: #f44336;
            cursor: not-allowed;
            opacity: 0.8;
        }
        .seat.selected {
            background-color: #007bff;
            color: white;
            border-color: #0056b3;
        }
        .seat.disabled {
            background-color: transparent;
            border: none;
            cursor: default;
        }
        .screen {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .seat-legend {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 20px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 8px;
            border: 1px solid #ccc;
        }
        .row-label {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            font-weight: bold;
            padding-right: 10px;
            color: #555;
            min-width: 30px;
        }
        .booking-summary-section {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: .25rem;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        #selectedSeatsButtons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2 class="mb-4">Seleccionar Boletos</h2>

        <div class="movie-details mb-4">
            <p><strong>Película:</strong> <?php echo htmlspecialchars($pelicula); ?></p>
            <p><strong>Cine:</strong> <?php echo htmlspecialchars(str_replace('-', ' ', $cine)); ?></p>
            <p><strong>Horario:</strong> <?php echo htmlspecialchars($horario); ?></p>
            <p><strong>Sala:</strong> <?php echo htmlspecialchars($idSala); ?></p>
            <p><strong>Cartelera ID:</strong> <?php echo htmlspecialchars($idCartelera); ?></p>
        </div>

        <form id="bookingForm" class="booking-summary-section">
            <input type="hidden" id="idPelicula" name="idPelicula" value="<?php echo htmlspecialchars($idPelicula); ?>">
            <input type="hidden" id="idCine" name="idCine" value="<?php echo htmlspecialchars($cine); ?>">
            <input type="hidden" id="idHorario" name="idHorario" value="<?php echo htmlspecialchars($horario); ?>">
            <input type="hidden" id="idSala" name="idSala" value="<?php echo htmlspecialchars($idSala); ?>">
            <input type="hidden" id="idCartelera" name="idCartelera" value="<?php echo htmlspecialchars($idCartelera); ?>">

            <div class="form-group">
                <label for="numTickets">Cantidad de Boletos:</label>
                <select class="form-control" id="numTickets">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
            </div>

            <div class="form-group">
                <label>Seleccionar Asientos:</label>
                <div class="screen">PANTALLA</div>
                <div id="seatMap" class="seat-grid">
                    <?php
                    if (empty($asientos_sala)) {
                        echo '<p class="alert alert-warning text-center">No se encontraron asientos para la sala y cartelera especificadas. Por favor, verifica la configuración de la sala o la disponibilidad de datos en el servidor.</p>';
                    }

                    $seatsByRow = [];
                    $maxCols = 0;

                    foreach ($asientos_sala as $seat) {
                        $seatsByRow[$seat['fila']][(int)$seat['numero']] = $seat;
                        if ((int)$seat['numero'] > $maxCols) {
                            $maxCols = (int)$seat['numero'];
                        }
                    }
                    ksort($seatsByRow);

                    echo '<style>:root { --max-cols: ' . $maxCols . '; }</style>';

                    foreach ($seatsByRow as $fila => $seatsInRow) {
                        echo '<div class="row-label">' . htmlspecialchars($fila) . '</div>';

                        for ($col = 1; $col <= $maxCols; $col++) {
                            $seat = $seatsInRow[$col] ?? null;

                            $estado_clase = '';
                            $data_id = '';
                            $data_seat_name = '';
                            $is_clickable = 'false';

                            if ($seat) {
                                if ($seat['estado'] === 'disponible') {
                                    $estado_clase = 'available';
                                    $data_id = 'data-id="' . htmlspecialchars($seat['id_asiento']) . '"';
                                    $data_seat_name = 'data-seat-name="' . htmlspecialchars($seat['nombre_asiento']) . '"';
                                    $is_clickable = 'true';
                                } elseif ($seat['estado'] === 'ocupado') {
                                    $estado_clase = 'occupied';
                                }
                                echo '<div class="seat ' . $estado_clase . '" ' . $data_id . ' ' . $data_seat_name . ' data-clickable="' . $is_clickable . '">';
                                echo htmlspecialchars($seat['numero']);
                                echo '</div>';
                            } else {
                                echo '<div class="seat disabled"></div>';
                            }
                        }
                    }
                    ?>
                </div>
                <div class="seat-legend">
                    <div class="legend-item"><span class="legend-color" style="background-color: #a8e6cf; border-color: #4CAF50;"></span> Disponible</div>
                    <div class="legend-item"><span class="legend-color" style="background-color: #ffadad; border-color: #f44336;"></span> Ocupado</div>
                    <div class="legend-item"><span class="legend-color" style="background-color: #007bff; border-color: #0056b3;"></span> Seleccionado</div>
                </div>

                <div id="selectedSeatsButtons" class="mt-3"></div>
                <input type="hidden" id="selectedSeatsInput" name="selectedSeats">
            </div>

            <div class="form-group">
                <label for="paymentMethod">Método de Pago:</label>
                <select class="form-control" id="paymentMethod">
                    <option value="credit_card">Tarjeta de crédito</option>
                    <option value="debit_card">Tarjeta de débito</option>
                    <option value="paypal">PayPal</option>
                </select>
            </div>

            <div class="form-group">
                <label for="totalPrice">Precio Total:</label>
                <input type="text" class="form-control" id="totalPrice" value="0.00" readonly>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Apartar Boletos</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var ticketPrice = 75.00;
        var selectedSeats = [];

        $(document).ready(function() {
            updateTotalPrice();

            $('#seatMap').on('click', '.seat.available[data-clickable="true"]', function() {
                var seatId = $(this).data('id');
                var seatName = $(this).data('seat-name');
                var maxTickets = parseInt($('#numTickets').val());

                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    selectedSeats = selectedSeats.filter(item => item.id !== seatId);
                } else {
                    if (selectedSeats.length < maxTickets) {
                        $(this).addClass('selected');
                        selectedSeats.push({id: seatId, name: seatName});
                    } else {
                        alert('Ya has seleccionado la cantidad máxima de boletos (' + maxTickets + '). Por favor, deselecciona uno para elegir otro, o aumenta la cantidad de boletos.');
                    }
                }
                updateSelectedSeatsDisplay();
                updateTotalPrice();
            });

            $('#numTickets').change(function() {
                $('.seat.selected').removeClass('selected');
                selectedSeats = [];
                updateSelectedSeatsDisplay();
                updateTotalPrice();
            });

            function updateSelectedSeatsDisplay() {
                $('#selectedSeatsButtons').empty();

                selectedSeats.forEach(function(item) {
                    var btn = $('<button type="button" class="btn btn-info btn-sm mr-2 mb-2"></button>')
                        .text(item.name + ' ✕')
                        .attr('data-seat-id', item.id)
                        .click(function() {
                            $('#seatMap div[data-id="' + item.id + '"]').removeClass('selected');
                            selectedSeats = selectedSeats.filter(s => s.id !== item.id);
                            updateSelectedSeatsDisplay();
                            updateTotalPrice();
                        });
                    $('#selectedSeatsButtons').append(btn);
                });

                // Este campo oculto ya no es estrictamente necesario si envías el XML directamente
                // Pero lo dejamos por si acaso lo usas para otros fines.
                $('#selectedSeatsInput').val(JSON.stringify(selectedSeats.map(item => item.id)));
            }

            function updateTotalPrice() {
                var numTicketsSelectedDropdown = parseInt($('#numTickets').val());
                var numSeatsActuallySelected = selectedSeats.length;
                var total;

                if (numSeatsActuallySelected === 0) {
                    total = numTicketsSelectedDropdown * ticketPrice;
                } else {
                    total = numSeatsActuallySelected * ticketPrice;
                }
                
                $('#totalPrice').val(total.toFixed(2));
            }

            // Función para convertir objeto JavaScript a string XML
            function convertToXml(obj) {
                let xmlString = '<boleto>';
                for (let key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        if (Array.isArray(obj[key])) {
                            xmlString += `<${key}>`;
                            obj[key].forEach(item => {
                                xmlString += `<asiento>${item}</asiento>`; // Ya envía el nombre de asiento aquí
                            });
                            xmlString += `</${key}>`;
                        } else {
                            xmlString += `<${key}>${obj[key]}</${key}>`;
                        }
                    }
                }
                xmlString += '</boleto>';
                return xmlString;
            }

            // Manejador del formulario de reserva
            $('#bookingForm').submit(function(e) {
                e.preventDefault();

                var numTickets = parseInt($('#numTickets').val());
                if (selectedSeats.length !== numTickets) {
                    alert('Por favor, selecciona exactamente ' + numTickets + ' asientos.');
                    return;
                }
                if (selectedSeats.length === 0) {
                    alert('Por favor, selecciona al menos un asiento.');
                    return;
                }

                var postData = {
                    // Coincide con lo que tu boletoModel espera enviar al API XML (o lo que el API XML espera recibir)
                    // Usa guiones bajos para que el XML resultante <id_cartelera> <id_sala> coincida
                    id_cartelera: $('#idCartelera').val(), 
                    id_sala: $('#idSala').val(),           
                    cantidad: selectedSeats.length,
                    num_asientos: selectedSeats.map(item => item.name), // Nombres de asientos
                    metodo_pago: $('#paymentMethod').val(), 
                    precio_total: parseFloat($('#totalPrice').val()) 
                };

                var xmlData = convertToXml(postData);
                console.log("XML FINAL enviado al backend:", xmlData); // Log para depuración

                $.ajax({
                    url: '/Cinepolis-Front/boletos/apartar.php', // Asegúrate de que esta URL sea la correcta para tu controlador PHP
                    type: 'POST',
                    contentType: 'application/xml', // Indispensable para enviar XML
                    data: xmlData, // Envía el string XML generado
                    success: function(response) {
                        try {
                            const parser = new DOMParser();
                            const xmlDoc = parser.parseFromString(response, "application/xml");
                            const errorMessage = xmlDoc.getElementsByTagName('error')[0];
                            const successMessage = xmlDoc.getElementsByTagName('message')[0];

                            if (successMessage) {
                                alert(successMessage.textContent || 'Boletos apartados con éxito.');
                                selectedSeats.forEach(function(seat) {
                                    $('#seatMap div[data-id="' + seat.id + '"]')
                                        .removeClass('selected available')
                                        .addClass('occupied')
                                        .attr('data-clickable', 'false')
                                        .off('click');
                                });
                                selectedSeats = [];
                                updateSelectedSeatsDisplay();
                                updateTotalPrice();
                                $('#numTickets').val('1');
                            } else if (errorMessage) {
                                alert('Error al apartar boletos: ' + errorMessage.textContent);
                            } else {
                                alert('Boletos apartados con exito.');
                            }
                        } catch (e) {
                            console.error("Error al parsear la respuesta XML exitosa:", e, response);
                            alert('Error al procesar la respuesta del servidor.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la llamada AJAX:', xhr.status, xhr.statusText, xhr.responseText);
                        let errorMessage = 'Hubo un error desconocido al apartar los boletos.';
                        try {
                            const parser = new DOMParser();
                            const errorXmlDoc = parser.parseFromString(xhr.responseText, "application/xml");
                            const errorNode = errorXmlDoc.getElementsByTagName('error')[0];
                            if (errorNode) {
                                errorMessage = errorNode.textContent;
                            } else {
                                errorMessage = xhr.responseText;
                            }
                        } catch (e) {
                            errorMessage = xhr.responseText || error;
                        }
                        alert('Error al apartar los boletos: ' + errorMessage);
                    }
                });
            });
        });
    </script>
</body>
</html>