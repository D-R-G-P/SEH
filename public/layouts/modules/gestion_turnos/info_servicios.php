<?php require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';
$user = new User();
$userSession = new
    UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'gestion_turnos']);
requireSubRole(['info_servicios']);

$title = "Gestión de Turnos -> Info de servicios";

$db = new DB();
$pdo = $db->connect();

?>
<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/gestion_turnos/css/info_servicios.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Gestión de Turnos - Información de servicios</h3>
        <p>Modulo destinado a la configuración de información por servicios.</p>
    </div>

    <div style="position: fixed; left: 20vw; top: 6.5vw; display: flex; flex-direction: column; z-index: 999;">
        <a href="gestion_turnos.php" class="btn-tematico">
            <i class="fa-solid fa-arrow-left"></i> <b>Volver</b>
        </a>
    </div>

    <div class="back" style="display: none;"></div>
    <div class="modulo">
        <h3>Gestión de Especialidades Médicas</h3>
        <div id="servicesList" class="item-list">
        </div>
        <div class="btn-group">
            <button type="button" id="openNewServiceModalBtn" class="btn-tematico"><b>+</b> Nueva Especialidad</button>
        </div>
    </div>

    <div class="filter-section modulo">
        <label for="serviceFilter">Seleccionar Especialidad</label>
        <select id="serviceFilter" class="form-select">
            <option value="">Mostrar Todas las Opciones</option>
        </select>
    </div>

    <div class="modulo">
        <h3>Opciones Principales del Servicio</h3>
        <div id="opcionesPrincipalesList" class="item-list">
        </div>
        <div class="btn-group">
            <button type="button" id="openNewOpcionPrincipalModalBtn" class="btn-tematico"><b>+</b> Nueva Opción
                Principal</button>
        </div>
    </div>

    <div id="contentEditingArea" class="modulo" style="display: none;">
        <h3>Detalle del Contenido y Sub-Opciones</h3>
        <p>Selecciona una Opción Principal para ver y editar su contenido y sub-opciones.</p>
    </div>
</div>

<div class="back" id="serviceModal" style="display: none;">
    <div class="divBackForm">
        <div class="close">
            <button class="btn-red" onclick="closeModal('serviceModal'); resetServiceForm();"><b><i
                        class="fa-solid fa-xmark"></i></b></button>
        </div>
        <h3>Formulario de Especialidad</h3>
        <form id="serviceForm">
            <input type="hidden" id="serviceId" name="id">
            <div class="form-group">
                <label for="serviceName" class="form-label">Nombre de la Especialidad</label>
                <input type="text" id="serviceName" name="nombre" required class="form-input">
            </div>
            <div class="form-group">
                <label for="serviceDescription" class="form-label">Descripción (Opcional)</label>
                <textarea id="serviceDescription" name="descripcion" rows="2" class="form-textarea"></textarea>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn-green">Guardar Especialidad</button>
                <button type="button" class="btn-red"
                    onclick="closeModal('serviceModal'); resetServiceForm();">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="back" id="opcionPrincipalModal" style="display: none;">
    <div class="divBackForm">
        <div class="close">
            <button class="btn-red" onclick="closeModal('opcionPrincipalModal'); resetOpcionPrincipalForm();"><b><i
                        class="fa-solid fa-xmark"></i></b></button>
        </div>
        <h3>Formulario de Opción Principal</h3>
        <form id="opcionPrincipalForm">
            <input type="hidden" id="opcionPrincipalId" name="id">

            <div class="form-group">
                <label for="opcionPrincipalServicioId" class="form-label">Asociada a Especialidad</label>
                <select id="opcionPrincipalServicioId" name="servicio_id" required class="form-select">
                    <option value="">Seleccionar Especialidad</option>
                </select>
            </div>

            <div class="form-group">
                <label for="opcionPrincipalParentOpcionId" class="form-label">Opción Padre (Opcional)</label>
                <select id="opcionPrincipalParentOpcionId" name="parent_opcion_id" class="form-select">
                    <option value="">Sin Padre (Opción Principal)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="opcionPrincipalTexto" class="form-label">Texto de la Opción (Ej "Agendar Turno", "Ver
                    Requisitos")</label>
                <input type="text" id="opcionPrincipalTexto" name="texto_opcion" required class="form-input">
            </div>

            <div class="form-group">
                <label for="opcionPrincipalTextoContenido" class="form-label">Contenido Asociado (Texto extenso,
                    Opcional)</label>
                <textarea id="opcionPrincipalTextoContenido" name="texto_contenido" rows="6"
                    class="form-textarea"></textarea>
            </div>

            <div class="form-group">
                <label for="opcionPrincipalEstado" class="form-label">Estado</label>
                <select id="opcionPrincipalEstado" name="estado" class="form-select">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-green">Guardar Opción Principal</button>
                <button type="button" class="btn-red"
                    onclick="closeModal('opcionPrincipalModal'); resetOpcionPrincipalForm();">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="back" id="contenidoPasoModal" style="display: none;">
    <div class="divBackForm">
        <div class="close">
            <button class="btn-red" onclick="closeModal('contenidoPasoModal'); resetContenidoPasoForm();"><b><i
                        class="fa-solid fa-xmark"></i></b></button>
        </div>
        <h3>Formulario de Contenido de Paso</h3>
        <form id="contenidoPasoForm">
            <input type="hidden" id="contenidoPasoId" name="id">

            <div class="form-group">
                <label for="contenidoPasoTitulo" class="form-label">Título del Contenido (Ej: "Información de
                    Turnos")</label>
                <input type="text" id="contenidoPasoTitulo" name="titulo" required class="form-input">
            </div>

            <div class="form-group">
                <label for="contenidoPasoTextoCompleto" class="form-label">Texto Completo del Contenido</label>
                <textarea id="contenidoPasoTextoCompleto" name="texto_completo" rows="8" required
                    class="form-textarea"></textarea>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label for="contenidoPasoEstado" class="form-label">Estado</label>
                <select id="contenidoPasoEstado" name="estado" class="form-select">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-green">Guardar Contenido</button>
                <button type="button" class="btn-red"
                    onclick="closeModal('contenidoPasoModal'); resetContenidoPasoForm();">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="back" id="subOpcionModal" style="display: none;">
    <div class="divBackForm">
        <div class="close">
            <button class="btn-red" onclick="closeModal('subOpcionModal'); resetSubOpcionForm();"><b><i
                        class="fa-solid fa-xmark"></i></b></button>
        </div>
        <h3>Formulario de Sub-Opción</h3 >
        <form id="subOpcionForm">
            <input type="hidden" id="subOpcionId" name="id">
            <input type="hidden" id="subOpcionPasoOrigenId" name="paso_origen_id">
            <div class="form-group">
                <label for="subOpcionTexto" class="form-label">Texto de la Sub-Opción (Ej: "Volver", "Ver
                    Requisitos")</label>
                <input type="text" id="subOpcionTexto" name="texto_sub_opcion" required class="form-input">
            </div>

            <div class="form-group">
                <label for="subOpcionPasoDestino" class="form-label">Contenido Destino</label>
                <select id="subOpcionPasoDestino" name="paso_destino_id" class="form-select">
                    <option value="">-- Finaliza aquí / Sin destino específico --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="subOpcionEstado" class="form-label">Estado</label>
                <select id="subOpcionEstado" name="estado" class="form-select">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-green">Guardar Sub-Opción</button>
                <button type="button" class="btn-red"
                    onclick="closeModal('subOpcionModal'); resetSubOpcionForm();">Cancelar</button>
            </div>
        </form>
    </div>
</div>

</div>
<script src="/SGH/public/layouts/modules/gestion_turnos/js/info_servicios.js"></script>
<?php require_once '../../base/footer.php'; ?>