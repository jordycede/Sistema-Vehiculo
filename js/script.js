const card = document.getElementById("card");
const toRegister = document.getElementById("toRegister");
const toLogin = document.getElementById("toLogin");

// Nuevo efecto de transición 3D con zoom
toRegister.addEventListener("click", () => {
  card.style.transform = "rotateY(180deg) scale(0.9)";
  setTimeout(() => {
    card.style.transform = "rotateY(180deg) scale(1)";
  }, 300);
});

toLogin.addEventListener("click", () => {
  card.style.transform = "rotateY(0deg) scale(0.9)";
  setTimeout(() => {
    card.style.transform = "rotateY(0deg) scale(1)";
  }, 300);
});

// Registro sin recarga
const registroForm = document.querySelector(".back form");

registroForm.addEventListener("submit", async function(e) {
  e.preventDefault();

  const formData = new FormData(registroForm);

  try {
    const response = await fetch("./php/registro.php", {
      method: "POST",
      body: formData
    });

    const data = await response.json();
    mostrarMensaje(data.message, data.success);

    if (data.success) {
      setTimeout(() => {
        card.style.transform = "rotateY(0deg) scale(0.9)";
        setTimeout(() => {
          card.style.transform = "rotateY(0deg) scale(1)";
        }, 300);
      }, 2000);
    }

  } catch (error) {
    mostrarMensaje("Error al conectar con el servidor.", false);
  }
});

function mostrarMensaje(mensaje, success) {
  let mensajeDiv = document.querySelector("#mensajeRegistro");

  if (!mensajeDiv) {
    mensajeDiv = document.createElement("div");
    mensajeDiv.id = "mensajeRegistro";
    registroForm.appendChild(mensajeDiv);
  }

  mensajeDiv.textContent = mensaje;
  mensajeDiv.style.color = success ? "#00b894" : "#ff7675";
  mensajeDiv.style.marginTop = "10px";
  mensajeDiv.style.textAlign = "center";
  mensajeDiv.style.fontWeight = "bold";
}