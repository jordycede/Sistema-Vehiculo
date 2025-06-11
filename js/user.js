document.addEventListener("DOMContentLoaded", function() {
    const reservaForm = document.getElementById("reserva-form");
    const reservasList = document.getElementById("reservas-list");
    const calendarEl = document.getElementById("calendar");
    let calendar;

    // Inicializar calendario
    function initCalendar() {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            locale: "es",
            events: [],
            eventColor: '#0ff',
            eventTextColor: '#000'
        });
        calendar.render();
    }

    // Cargar reservas del usuario
    async function cargarReservas() {
        const userId = 1; // Cambiar por $_SESSION['user_id'] en producción
        const res = await fetch(`./php/api_reservas.php?usuario_id=${userId}`);
        const reservas = await res.json();
        
        reservasList.innerHTML = reservas.map(r => `
            <div class="border border-cyberAccent p-4 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold">${r.marca} ${r.modelo}</h3>
                        <p>Placa: ${r.placa}</p>
                        <p>${r.fecha_inicio} al ${r.fecha_fin}</p>
                        <p class="text-${r.estado === 'aprobada' ? 'green' : 'yellow'}-400">Estado: ${r.estado}</p>
                    </div>
                    <button onclick="cancelarReserva(${r.id})" class="px-3 py-1 bg-red-600 text-black rounded">Cancelar</button>
                </div>
            </div>
        `).join("");

        // Actualizar calendario
        calendar.removeAllEvents();
        reservas.forEach(r => {
            calendar.addEvent({
                title: `Reserva: ${r.marca} ${r.modelo}`,
                start: r.fecha_inicio,
                end: r.fecha_fin,
                color: r.estado === 'aprobada' ? '#00ff00' : '#ffff00'
            });
        });
    }

    // Enviar reserva
    reservaForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const vehiculoId = document.getElementById("vehiculo-seleccionado").value;
        if (!vehiculoId) {
            alert("Por favor selecciona un vehículo");
            return;
        }
        
        const reserva = {
            usuario_id: 1, // Cambiar por $_SESSION['user_id'] en producción
            vehiculo_id: vehiculoId,
            fecha_inicio: document.getElementById("reserva-inicio").value,
            fecha_fin: document.getElementById("reserva-fin").value
        };

        const response = await fetch("./php/api_reservas.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(reserva)
        });
        
        const result = await response.json();
        if (!result.success) {
            alert(result.message);
            return;
        }

        reservaForm.reset();
        cargarReservas();
        mostrarVehiculosDisponibles();
    });

    // Cancelar reserva
    window.cancelarReserva = async (id) => {
        if (confirm("¿Cancelar esta reserva?")) {
            await fetch("./php/api_reservas.php", {
                method: "DELETE",
                body: new URLSearchParams({ id })
            });
            cargarReservas();
            mostrarVehiculosDisponibles();
        }
    };

    // Inicializar
    initCalendar();
    cargarReservas();
});