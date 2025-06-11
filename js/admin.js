document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("vehiculo-form");
    const lista = document.getElementById("vehiculos-list");
    let editandoId = null;

    // Cargar vehículos al iniciar
    // Modificar la función cargarVehiculos
async function cargarVehiculos() {
    try {
        const res = await fetch("./php/api_vehiculos.php");
        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || "Error al cargar vehículos");
        }
        
        const vehiculos = await res.json();
        renderizarVehiculos(vehiculos);
    } catch (error) {
        console.error("Error detallado:", error);
        lista.innerHTML = `
            <div class="border border-red-500 p-4 rounded-lg">
                <p class="text-red-500 font-bold">Error al cargar vehículos</p>
                <p class="text-red-400">${error.message}</p>
                <p class="text-sm text-gray-400">Verifica la conexión a internet y vuelve a intentar</p>
            </div>
        `;
    }
}

    // Guardar o editar
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const vehiculo = {
            marca: document.getElementById("vehiculo-marca").value,
            modelo: document.getElementById("vehiculo-modelo").value,
            anio: document.getElementById("vehiculo-año").value,
            placa: document.getElementById("vehiculo-placa").value
        };

        try {
            let response;
            if (editandoId) {
                vehiculo.id = editandoId;
                response = await fetch("./php/api_vehiculos.php", {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(vehiculo)
                });
                editandoId = null;
            } else {
                response = await fetch("./php/api_vehiculos.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(vehiculo)
                });
            }

            const result = await response.json();
            if (!result.success) {
                alert(result.error || "Error al guardar el vehículo");
                return;
            }

            form.reset();
            await cargarVehiculos(); // Esperar a que se recarguen los vehículos
            alert("Vehículo guardado correctamente");
        } catch (error) {
            alert("Error al conectar con el servidor");
            console.error(error);
        }
    });

    // Función mejorada para cargar vehículos
    async function cargarVehiculos() {
        try {
            const res = await fetch("./php/api_vehiculos.php");
            if (!res.ok) throw new Error("Error al cargar vehículos");
            
            const vehiculos = await res.json();
            renderizarVehiculos(vehiculos);
        } catch (error) {
            console.error(error);
            lista.innerHTML = `<p class="text-red-500">Error al cargar vehículos: ${error.message}</p>`;
        }
    }

    // Función para renderizar vehículos
    function renderizarVehiculos(vehiculos) {
        lista.innerHTML = vehiculos.map(v => `
            <div class="border border-cyberAccent p-4 rounded-lg flex justify-between items-center">
                <div>
                    <h3 class="font-bold">${v.marca} ${v.modelo} (${v.año})</h3>
                    <p>Placa: ${v.placa} • Estado: 
                        <span class="${v.estado === 'disponible' ? 'text-green-400' : 'text-yellow-400'}">
                            ${v.estado}
                        </span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <button onclick="editarVehiculo(${v.id})" class="px-3 py-1 bg-cyan-600 text-black rounded">Editar</button>
                    <button onclick="eliminarVehiculo(${v.id})" class="px-3 py-1 bg-red-600 text-black rounded">Eliminar</button>
                </div>
            </div>
        `).join("");
    }

    // Editar vehículo
    window.editarVehiculo = async (id) => {
        try {
            const res = await fetch(`./php/api_vehiculos.php?id=${id}`);
            if (!res.ok) throw new Error("Error al cargar vehículo");
            
            const vehiculo = await res.json();
            document.getElementById("vehiculo-marca").value = vehiculo.marca;
            document.getElementById("vehiculo-modelo").value = vehiculo.modelo;
            document.getElementById("vehiculo-año").value = vehiculo.año;
            document.getElementById("vehiculo-placa").value = vehiculo.placa;
            
            editandoId = id;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            alert("Error al cargar vehículo para editar");
            console.error(error);
        }
    };

    // Eliminar vehículo
    window.eliminarVehiculo = async (id) => {
        if (!confirm("¿Eliminar este vehículo permanentemente?")) return;
        
        try {
            const response = await fetch("./php/api_vehiculos.php", {
                method: "DELETE",
                body: new URLSearchParams({ id })
            });
            
            const result = await response.json();
            if (!result.success) {
                alert(result.error || "Error al eliminar vehículo");
                return;
            }
            
            await cargarVehiculos();
            alert("Vehículo eliminado correctamente");
        } catch (error) {
            alert("Error al eliminar vehículo");
            console.error(error);
        }
    };
});