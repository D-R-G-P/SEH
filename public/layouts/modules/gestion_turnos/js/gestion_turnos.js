$(document).ready(function () {
    console.log("📡 Verificando estado inicial del bot...");
    updateBotStatus();

    $("#startBot").click(() => controlBot('start'));
    $("#stopBot").click(() => controlBot('stop'));
    $("#restartBot").click(() => controlBot('restart'));
});

function controlBot(action) {

    loader.style.display = "block";
    botStatus.style.display = "none";

    console.log(`⚡ Enviando acción: ${action}`);

    $.get(`/SGH/public/resources/controllers/bot_control.php?action=${action}`, function (response) {
        console.log("📩 Respuesta del servidor:", response);

        if (typeof response === "string") {
            try {
                response = JSON.parse(response);
            } catch (e) {
                console.error("❌ Error al parsear JSON:", e);
                return;
            }
        }

        if (response.error) {
            console.error("🚨 Error:", response.error);
        } else {
            console.log("✅ Acción realizada correctamente:", response.status);
        }

        setTimeout(updateBotStatus, 3000);
    });
}

function updateBotStatus() {

    $.get(`/SGH/public/resources/controllers/bot_control.php?action=status`, function (response) {
        console.log("📡 Estado del bot:", response);

        if (typeof response === "string") {
            response = JSON.parse(response);
        }

        let statusText = "🔴 Inactivo"; // Estado predeterminado
        let mostrarQR = false;
        let waiting = ""; // Estado de espera

        if (response.status === "active") {
            botStatus.style.display = 'block';
            loader.style.display = 'none';
            statusText = "🟢 Activo";
            startBot.style.display = "none";
            stopBot.style.display = "block";
            restartBot.style.display = "block";
        } else if (response.status === "waiting_qr") {
            botStatus.style.display = 'block';
            loader.style.display = 'none';
            statusText = "🟡 Esperando QR...";
            startBot.style.display = "none";
            stopBot.style.display = "block";
            restartBot.style.display = "block";
            mostrarQR = true;
        } else if (response.status === "inactive") {
            botStatus.style.display = 'block';
            loader.style.display = 'none';
            statusText = "🔴 Inactivo";
            startBot.style.display = "block";
            stopBot.style.display = "none";
            restartBot.style.display = "none";
        }

        $("#botStatus").text(statusText);

        // Verificar si hay un QR disponible en el servidor
        $.get(`/SGH/public/resources/controllers/bot_control.php?action=check_qr`, function (qrResponse) {
            if (typeof qrResponse === "string") {
                qrResponse = JSON.parse(qrResponse);
            }

            if (mostrarQR && qrResponse.exists) {
                $("#qrImage").attr("src", "/SGH/app/whatsapp_bot/qrcodes/whatsapp-qr.png").css("display", "block");
                console.log("📸 QR encontrado en servidor. Mostrando...");
            } else {
                $("#qrImage").css("display", "none");
                console.log("🗑️ QR oculto.");
            }
        });

    });

    setTimeout(updateBotStatus, 5000);
}

