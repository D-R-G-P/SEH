<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'hsi']);
requireSubRole(['capacitaciones_hsi']);

$title = "Capacitaciones de la HSI";

$db = new DB();
$pdo = $db->connect();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/hsiPanel/css/hsi.css">

<style>
    .select2-container--default .select2-selection--multiple {
        width: auto;
    }

    /*
 * Estilos para el modal de visualización de capacitaciones
 * Este CSS controla el diseño del modal, el fondo, el contenido y el botón de cerrar.
 */

/* Estilo para el fondo oscuro del modal */
.modal-hsi {
    display: none; /* Oculto por defecto */
    position: fixed; /* Se superpone a todo el contenido */
    z-index: 1000; /* Asegura que esté por encima de otros elementos */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; /* Permite desplazamiento si el contenido es muy largo */
    background-color: rgba(0, 0, 0, 0.4); /* Fondo oscuro con transparencia */
    
    /* Centrar el contenido usando Flexbox */
    display: flex; 
    align-items: center;
    justify-content: center;
}

/* Contenedor principal del contenido del modal */
.modal-content {
    background-color: #fefefe;
    margin: 10% auto; /* Espacio superior e inferior para el modal */
    padding: 20px;
    border-radius: 8px;
    width: 90%; /* Ancho en pantallas pequeñas */
    max-width: 600px; /* Ancho máximo en pantallas grandes */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    position: relative; /* Necesario para posicionar el botón de cierre */
    font-family: 'Inter', sans-serif; /* [Inferencia] Asumo una fuente moderna */
}

/* Botón para cerrar el modal */
.close-btn {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close-btn:hover,
.close-btn:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

/* Estilos para el título del modal */
#viewModalTitle {
    color: #333;
    margin-top: 0;
    border-bottom: 2px solid #ccc;
    padding-bottom: 10px;
}

/* Estilos para las tarjetas de instancias de capacitación */
.instancia-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
    background-color: #fafafa;
}

.instancia-card p {
    margin: 5px 0;
}

.modal-hsi {
    display: none;
}

.pill {
    display: inline-flex; /* Usa inline-flex para alinear contenido dentro */
    align-items: center; /* Centra verticalmente */
    justify-content: center; /* Centra horizontalmente */
    padding: 3px 8px; /* Ajuste para un tamaño más compacto */
    font-size: 0.75em;
    font-weight: bold;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    border-radius: 6px; /* Bordes menos redondeados */
    min-width: 80px; /* Ancho mínimo para mantener la forma */
    height: 24px;
    box-sizing: border-box;
}

/* Colores para cada estado, ahora con transparencia */
.pill-inscripto {
    background-color: rgba(59, 130, 246, 0.2); /* Azul con 20% de opacidad */
    border: 2px solid rgba(59, 130, 246, 0.5); /* Borde azul con 50% de opacidad */
    color: #1e40af; /* Azul oscuro */
}

.pill-presente {
    background-color: rgba(34, 197, 94, 0.2); /* Verde con 20% de opacidad */
    border: 2px solid rgba(34, 197, 94, 0.5); /* Verde con 20% de opacidad */
    color: #166534; /* Verde oscuro */
}

.pill-ausente {
    background-color: rgba(249, 115, 22, 0.2); /* Naranja con 20% de opacidad */
    border: 2px solid rgba(249, 115, 22, 0.5); /* Naranja con 20% de opacidad */
    color: #9a3412; /* Naranja oscuro */
}

.pill-cancelado {
    background-color: rgba(239, 68, 68, 0.2); /* Rojo con 20% de opacidad */
    border: 2px solid rgba(239, 68, 68, 0.5); /* Rojo con 20% de opacidad */
    color: #991b1b; /* Rojo oscuro */
}

.pill-programada {
    background-color: rgba(59, 130, 246, 0.2);
    border: 2px solid rgba(59, 130, 246, 0.5); 
    color: #1e40af;
}

.pill-en_curso {
    background-color: rgba(234, 179, 8, 0.2);
    border: 2px solid rgba(234, 179, 8, 0.5);
    color: #a16207;
}

.pill-completada {
    background-color: rgba(34, 197, 94, 0.2);
    border: 2px solid rgba(34, 197, 94, 0.5);
    color: #166534;
}

.pill-reprogramada {
    background-color: rgba(249, 115, 22, 0.2);
    border: 2px solid rgba(249, 115, 22, 0.5);
    color: #9a3412;
}

.pill-cerrada {
    background-color: rgba(107, 114, 128, 0.2);
    border: 2px solid rgba(107, 114, 128, 0.5);
    color: #374151;
}

.pill-cancelada {
    background-color: rgba(239, 68, 68, 0.2);
    border: 2px solid rgba(239, 68, 68, 0.5);
    color: #991b1b;
}

</style>

<div class="content">
    <div class="back" id="back" style="display: none;">
        <div class="divBackForm" id="newCap" style="display: none; width: auto;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="resetTableAndForm();"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Nueva capacitación</h3>

            <form action="/SGH/public/layouts/modules/hsiPanel/capacitaciones_hsi/controllers/new_capacitacion.php"
                method="post" class="backForm" id="newCapForm" style="width: auto; padding: 1vw;">

                <div>
                    <label for="title">Titulo</label>

                    <input type="text" name="title" id="title" required style="width: 100%;">
                </div>

                <div>
                    <label for="description">Descripción</label>

                    <textarea name="description" id="description" required style="resize: none; width: 100%; field-sizing: content;"></textarea>
                </div>

                <div>
                    <label for="rol_asociated">Roles asociados</label>

                    <select name="rol_asociated[]" id="rol_asociated" class="select2" multiple style="width: 100%;" required>
                        <option value="" disabled></option>

                        <?php

                        $query = "SELECT id, rol FROM roles_hsi WHERE estado != 'eliminado' ORDER BY rol ASC";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['id'] . "'>" . $row['rol'] . "</option>";
                        }

                        ?>
                    </select>
                </div>

                <div>
                    <h2>Fechas y horarios</h2>
                </div>

                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Lugar</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="dateTimeRows">
                            <!-- TR final, con boton de agregar nuevo -->
                                <tr>
                                    <td colspan="4" class="table-middle table-center"><button type="button" class="btn-green" onclick="addInstancia();"><i class="fa-solid fa-plus"></i> Nuevo horario</button></td>
                                </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <button type="submit" class="btn-green" id="submitBtn"><i class="fa-solid fa-plus"></i> Agregar capacitación</button>
                </div>
            </form>
        </div>

        <div class="divBackForm" id="editCap" style="display: none; width: auto;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="resetTableAndForm();"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Nueva capacitación</h3>

            <form action="/SGH/public/layouts/modules/hsiPanel/capacitaciones_hsi/controllers/edit_capacitacion.php"
                method="post" class="backForm" id="editCapForm" style="width: auto; padding: 1vw;">

                <input type="hidden" name="edit_id" id="edit_id">

                <div>
                    <label for="title">Titulo</label>

                    <input type="text" name="title" id="edit_title" required style="width: 100%;">
                </div>

                <div>
                    <label for="description">Descripción</label>

                    <textarea name="description" id="edit_description" required style="resize: none; width: 100%; field-sizing: content;"></textarea>
                </div>

                <div>
                    <label for="rol_asociated">Roles asociados</label>

                    <select name="rol_asociated[]" id="edit_rol_asociated" class="select2" multiple style="width: 100%;" required>
                        <option value="" disabled></option>

                        <?php

                        $query = "SELECT id, rol FROM roles_hsi WHERE estado != 'eliminado' ORDER BY rol ASC";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['id'] . "'>" . $row['rol'] . "</option>";
                        }

                        ?>
                    </select>
                </div>

                <div>
                    <h2>Fechas y horarios</h2>
                </div>

                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Lugar</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="edit_dateTimeRows">
                            <!-- TR final, con boton de agregar nuevo -->
                                <tr>
                                    <td colspan="4" class="table-middle table-center"><button type="button" class="btn-green" onclick="addInstancia();"><i class="fa-solid fa-plus"></i> Nuevo horario</button></td>
                                </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <button type="submit" class="btn-green" id="submitBtn"><i class="fa-solid fa-plus"></i> Agregar capacitación</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modulo">
        <h1>Capacitaciones programadas</h1>
        <div>
            <button class="btn-green" onclick="back.style.display = 'flex'; newCap.style.display = 'flex';"><i class="fa-solid fa-plus"></i> Nueva capacitación</button>
        </div>

        <div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Titulo</th>
                        <th>Descripción</th>
                        <th>Roles asociados</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Lugar</th>
                        <th>Asistentes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="capacitacionesTableBody">
                    <!-- Aquí se llenarán las capacitaciones desde el JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/SGH/public/layouts/modules/hsiPanel/js/capacitaciones_hsi.js"></script>
<?php require_once '../../base/footer.php'; ?>