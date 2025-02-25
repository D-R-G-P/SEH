// Permisos de grupos de roles

function load_selects() {
    $('.tableGenSel').select2({
        placeholder: "Selecciona un subrol",
        allowClear: false
    });
}

function toggle_group_view(id) {
    var back = document.getElementById('back');
    var roles_groups = document.getElementById('roles_groups');
    var rolGenView = document.getElementById('rolGenView');
    

    back.style.display = 'flex';
    roles_groups.style.display = 'flex';

    $.ajax({
        url: 'controllers/rol_gen_view.php',
        method: 'POST',
        data: { id: id },
        success: function (response) {
            rolGenView.innerHTML = response;
            load_selects(); // Cargar Select2 después de insertar HTML dinámico
            attachEventListeners(); // Vincular eventos a los nuevos elementos
        }
    });

    
}

function attachEventListeners() {
    // Evento para checkboxes de roles
    $('.role-checkbox').on('change', function () {
        let roleId = this.id.replace("rol-", "");
        let isChecked = this.checked;
        let select = $(`#subrol-${roleId}`);

        if (select.length) {
            select.prop("disabled", !isChecked);

            if (!isChecked) {
                select.val(null).trigger("change"); // Limpiar selección si se desactiva
            }
        }

        guardarPermisos(roleId, isChecked, select.val() || []);
    });

    // Evento para cambios en Select2
    $('.tableGenSel').on('change', function () {
        let roleId = this.id.replace("subrol-", "");
        let isChecked = $(`#rol-${roleId}`).prop("checked");
        guardarPermisos(roleId, isChecked, $(this).val() || []);
    });
}

function guardarPermisos(roleId, isChecked, subroles) {
    let groupId = $('#grupoId').val();

    if (!groupId) {
        console.error("Error: groupId es nulo o indefinido.");
        return;
    }

    $.ajax({
        url: 'controllers/guardar_permisos.php',
        method: 'POST',
        data: {
            groupId: groupId,
            roleId: roleId,
            isChecked: isChecked,
            subroles: JSON.stringify(subroles) // 🔥 Convertir a JSON
        },
        dataType: 'json',
        success: function (response) {
            if (response && typeof response === 'object') {
                toast(response.message, response.success ? 'success' : 'error');
                console.log(response);
            } else {
                console.error('Respuesta inesperada:', response);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al guardar permisos:', error);
            console.log('Respuesta del servidor:', xhr.responseText);
        }
    });    
}

// Cargar Select2 en la inicialización
$(function () {
    load_selects();
    attachEventListeners();
    $('#selectServicioFilter').select2();
});


















function load_selects_seccond() {
    $('.table-select').select2({
        placeholder: "Selecciona un subrol",
        allowClear: false
    });
}


// ---------------------- Función para ver los permisos del usuario ----------------------
function toggle_user_view(dni) {
    let back = $('#back');
    let roles_groups = $('#roles_users');
    let rolAdmView = $('#rolAdmView');

    back.css('display', 'flex');
    roles_groups.css('display', 'flex');

    console.log("toggle_user_view - DNI:", dni);

    $.ajax({
        url: 'controllers/rol_adm_view.php',
        method: 'POST',
        data: { dni: dni },
        success: function (response) {
            // console.log("toggle_user_view - AJAX response:", response);
            rolAdmView.html(response);
            initializeUserPermissions(); // Cargar Select2 y eventos después de cargar los datos dinámicos
        }
    });
}

// ---------------------- Inicializar Select2 y Eventos ----------------------
function initializeUserPermissions() {
    console.log("initializeUserPermissions - Initializing Select2 and event listeners");

    $('.table-select').select2({
        placeholder: "Selecciona un subrol",
        allowClear: false
    });

    $('.role-checkbox').off('change').on('change', function () {
        let rolId = this.id.split('-')[1];
        let dni = $('#userDni').val();
        let isChecked = this.checked;

        console.log("initializeUserPermissions - Checkbox changed:", { rolId, dni, isChecked });

        let subroles = [];
        if (isChecked) {
            subroles = $(`#subrol-${rolId}`).val() || [];
        }

        if (this.checked) {
            estado = 'agregar';
        } else {
            estado = 'quitar';
        }

        guardarPermisosUsr(dni, rolId, subroles, estado);
    });

    $('.table-select').off('change').on('change', function () {
        let rolId = this.id.split('-')[1];
        let dni = $('#userDni').val();
        let subroles = $(this).val() || [];

        console.log("initializeUserPermissions - Select2 changed:", { rolId, dni, subroles });

        guardarPermisosUsr(dni, rolId, subroles, 'actualizar');
    });
}

// ---------------------- Guardar permisos en la base de datos ----------------------
function guardarPermisosUsr(dni, rolId, subroles, accion) {
    if (!dni || !rolId) {
        console.error("Error: DNI o rolId es nulo o indefinido.");
        console.error("Valores actuales:", { dni, rolId, subroles, accion });
        return;
    }

    console.log("guardarPermisos - Saving permissions:", { dni, rolId, subroles, accion });

    $.ajax({
        url: 'controllers/guardar_usuario_permisos.php',
        type: 'POST',
        data: { dni, rol_id: rolId, subroles: JSON.stringify(subroles), accion },
        dataType: 'json',
        success: function (response) {
            // console.log("guardarPermisos - AJAX success response:", response);
            toast(response.message, response.success ? 'success' : 'error');

            if (accion === 'quitar') {
                $(`#subrol-${rolId}`).prop('disabled', true).val(null).trigger('change');
            } else {
                $(`#subrol-${rolId}`).prop('disabled', false);
            }
        },
        error: function (xhr, error) {
            console.error("guardarPermisos - AJAX error:", error);
            console.log("guardarPermisos - Error details:", xhr.responseText);
        }
    });

    setTimeout(load_selects_seccond, 1000);
}