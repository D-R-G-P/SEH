// gestion_camas.js

// Define all relevant patient form fields
const patientFormInputs = [
	'#nombres_dni', '#apellidos_dni', '#nombre_autopercibido', '#numero_documento',
	'#fecha_nacimiento', '#phone_number', '#family_phone_number', '#email',
	'#codigo_postal', '#calle', '#numero', '#piso', '#departamento', '#barrio',
	'#health_insurance', '#health_insurance_number', '#phone_number_alt', '#family_phone_number_alt'
];

const patientFormSelects = [
	'#sexo_dni', '#tipo_documento_patient', '#identidad_genero',
	'#pais', '#provincia', '#partido', '#ciudad'
];

const patientFormCheckboxes = [
	'#dni_rectificado'
];

/**
 * Habilita o deshabilita los campos del formulario del paciente.
 * @param {boolean} enable Si es true, habilita los campos; si es false, los deshabilita y los pone en solo lectura.
 */
function togglePatientFormFields(enable) {
	// Alternar inputs
	patientFormInputs.forEach(selector => {
		$(selector).prop('disabled', !enable).prop('readonly', !enable);
	});

	// Alternar checkboxes
	patientFormCheckboxes.forEach(selector => {
		$(selector).prop('disabled', !enable);
	});

	// Alternar selects (manejo específico de Select2)
	patientFormSelects.forEach(selector => {
		const $select = $(selector);
		// Establecer la propiedad 'disabled' estándar del HTML
		$select.prop('disabled', !enable);

		// Si Select2 está inicializado, forzarlo a re-leer su estado y actualizar su UI
		if ($select.data('select2')) {
			$select.select2(); // Llama a Select2 sin argumentos para que se refresque
		}
	});

	// Manejo específico para nombre_administrativo_final (siempre solo lectura/deshabilitado)
	$('#nombre_administrativo_final').prop('disabled', true).prop('readonly', true);
}


$(document).ready(function () {
	// Inicialización de Select2 que no depende de PHP
	// Estas inicializaciones solo ocurren una vez al cargar la página principal.
	// Los Select2 dentro de contenido cargado por AJAX deben ser inicializados después de la inyección.
	$('#complejidad').select2({ // Esta inicialización es para la cama principal si existe desde el inicio
		placeholder: 'Seleccionar una complejidad...',
	});
	$('#bloqueo_tipo').select2({
		placeholder: 'Seleccionar un tipo de bloqueo...'
	});
	$('#tipo_documento').select2({
		placeholder: 'Seleccionar un tipo de documento'
	});

	const toggleConfig = [
		// Configuración para cada nivel jerárquico
		// headerClass: El selector CSS del elemento clickeable.
		// contentClass: El selector CSS del contenido a mostrar/ocultar (hermano inmediato siguiente).
		// initiallyOpen: true para estar desplegado por defecto.
		{ headerClass: '.hospital_name', contentClass: '.hospital_lat', initiallyOpen: true },
		{ headerClass: '.pabellon_name', contentClass: '.pabellon_lat', initiallyOpen: true },
		{ headerClass: '.piso_name', contentClass: '.piso_lat', initiallyOpen: true },
		{ headerClass: '.sala_name', contentClass: '.habitacion_lat', initiallyOpen: true },
		{ headerClass: '.habitacion_name', contentClass: '.cama', initiallyOpen: true }
	];

	toggleConfig.forEach(config => {
		// Iteramos sobre cada cabecera encontrada en el DOM
		$(config.headerClass).each(function () {
			const $header = $(this);
			const $contentToToggle = $header.next(config.contentClass);

			// Solo aplicar si el contenido a mostrar/ocultar existe
			if ($contentToToggle.length) {
				$header.css('cursor', 'pointer'); // Cambiar cursor para indicar que es clickeable

				// Establecer estado inicial (visible/oculto)
				if (config.initiallyOpen) {
					$contentToToggle.show();
				} else {
					$contentToToggle.hide();
				}

				// Manejador del evento click
				$header.on('click', function (e) {
					e.preventDefault(); // Prevenir comportamiento por defecto si la cabecera fuera un enlace
					$contentToToggle.slideToggle(200); // Animación de mostrar/ocultar
				});
			}
		});
	});

	// Event listener para abrir la información de la cama
	$(document).on('click', '.cama_item', function () {
		const camaId = this.id.replace('cama_', '');
		$.post('controllers/info_cama.php', { cama_id: camaId }, function (response) {
			$('#info_cama').html(response);
			document.getElementById('back').style.display = 'flex';
			document.getElementById('info_cama').style.display = 'flex';
			// Almacenar el camaId en el contenedor de info_cama para que sea accesible
			$('#info_cama').data('currentCamaId', camaId);
			// Llama a la función para inicializar el formulario de búsqueda de pacientes
			initializeSearchPatientForm();
			// Llama a initBedActions para configurar el estado de la cama cargada
			window.initBedActions(camaId, null); // Pasa null para complejidad, ya que se leerá del HTML
		});
	});

	// Event listener para el botón de nueva cama
	$(document).on('click', '#new_bed', function () {
		$.post('controllers/info_cama.php', function (response) {
			$('#info_cama').html(response);
			document.getElementById('back').style.display = 'flex';
			document.getElementById('info_cama').style.display = 'flex';
			// Almacenar el camaId como null para una nueva cama
			$('#info_cama').data('currentCamaId', null);
			// Llama a la función para inicializar el formulario de búsqueda de pacientes
			initializeSearchPatientForm();
			// Llama a initBedActions para configurar el estado de una nueva cama
			window.initBedActions(null, null); // Pasa null para ID y complejidad para nueva cama
		});
	});

	// Event listener para el botón "Nuevo Paciente" (asume que existe en el HTML principal)
	$(document).on('click', '#btnNuevoPaciente', function () {
		const currentCamaId = $('#info_cama').data('currentCamaId');
		window.setInfo_paciente(null, currentCamaId); // Llama a setInfo_paciente sin ID para cargar formulario vacío
	});

	// Función para inicializar el formulario de búsqueda de pacientes
	// Esta función se llama después de que el HTML de info_cama.php es inyectado
	function initializeSearchPatientForm() {
		const search_patient_form = document.getElementById('search_patient');
		const currentCamaId = $('#info_cama').data('currentCamaId'); // Obtener el camaId actual

		// Asegúrate de que el elemento exista antes de añadir el listener
		if (search_patient_form) {
			// Primero, removemos cualquier listener previo para evitar duplicados
			// Esto es crucial si el HTML se inyecta múltiples veces
			$(search_patient_form).off('submit').on('submit', async (event) => {
				event.preventDefault(); // Evita que el formulario se envíe de la forma tradicional

				const formData = new FormData(search_patient_form);

				try {
					// Usa 'await' para esperar la respuesta de la petición fetch
					const response = await fetch('/SGH/public/layouts/modules/gestion_camas/controllers/search_patient.php', {
						method: 'POST',
						body: formData
					});

					// Verifica si la respuesta HTTP fue exitosa (código 2xx)
					if (response.ok) {
						// Usa 'await' para esperar a que la respuesta se parsee como JSON
						const data = await response.json();

						// *** Lógica para manejar la respuesta ***
						if (data.success) {
							toast('Paciente encontrado', 'success');
							// Asegúrate de que setInfo_paciente esté disponible globalmente
							if (typeof window.setInfo_paciente === 'function') {
								window.setInfo_paciente(data.patient.id, currentCamaId); // Pasa camaId
							} else {
								console.error("Error: setInfo_paciente no está definida o no es una función.");
							}
						} else {
							// Si el paciente no se encuentra, inicia el flujo de creación de un nuevo paciente
							toast('Paciente no encontrado. Cargando formulario para crear nuevo paciente...', 'warning', 5000);
							if (typeof window.setInfo_paciente === 'function') {
								window.setInfo_paciente(null, currentCamaId); // Pasa camaId
							} else {
								console.error("Error: setInfo_paciente no está definida o no es una función.");
							}
						}
					} else {
						// Manejo de errores de red o del servidor (códigos de estado 4xx, 5xx)
						console.error('Error al enviar el formulario. Código de estado HTTP:', response.status);
						const errorText = await response.text(); // Intenta leer el cuerpo del error
						console.error('Detalles del error:', errorText);
						alert('Hubo un problema al comunicarse con el servidor. Inténtalo de nuevo.');
					}
				} catch (error) {
					// Manejo de errores de conexión o del proceso fetch
					console.error('Error de conexión o en la petición:', error);
					alert('No se pudo conectar con el servidor. Verifica tu conexión a internet.');
				}
			});

			// Inicializar Select2 para el tipo de documento en el formulario de búsqueda
			$('#tipo_documento').select2({
				placeholder: 'Seleccionar un tipo de documento'
			});
		} else {
			console.warn("Elemento 'search_patient' no encontrado en el DOM después de la inyección de info_cama.php.");
		}
	}


	// Unificación de todos los event listeners de click en document
	document.addEventListener('click', async function (event) { // Asegura que 'event' se pasa al callback y permite uso de await
		let confirmation; // Declarar 'confirmation' aquí para que sea accesible en todo el switch

		switch (event.target.id) {
			case 'ingresarBtn':
				document.getElementById('ingresar_popup').style.display = 'flex';
				break;
			case 'reservarBtn':
				document.getElementById('reserve_popup').style.display = 'flex';
				break;
			case 'liberarReservaBtn':
				confirmation = confirm('¿Está seguro de que desea liberar la reserva?');
				if (confirmation) {
					const form = document.createElement('form');
					form.method = 'POST';
					form.action = 'controllers/change_bed_status.php';
					form.style.display = 'none';

					const inputCamaId = document.createElement('input');
					inputCamaId.type = 'hidden';
					inputCamaId.name = 'cama_id';
					inputCamaId.value = document.getElementById('cama_id').value;
					form.appendChild(inputCamaId);

					const inputCamaStatus = document.createElement('input');
					inputCamaStatus.type = 'hidden';
					inputCamaStatus.name = 'cama_status';
					inputCamaStatus.value = 'Liberar';
					form.appendChild(inputCamaStatus);

					document.body.appendChild(form);
					form.submit();
				}
				break;
			case 'bloquearBtn':
				document.getElementById('bloquear_popup').style.display = 'flex';
				$('#bloqueo_tipo').select2({ placeholder: 'Seleccionar un tipo de bloqueo...' });
				break;
			case 'desbloquearBtn':
				confirmation = confirm('¿Está seguro de que desea desbloquear la cama?'); // Mensaje más claro
				if (confirmation) {
					const form = document.createElement('form');
					form.method = 'POST';
					form.action = 'controllers/change_bed_status.php';
					form.style.display = 'none';

					const inputCamaId = document.createElement('input');
					inputCamaId.type = 'hidden';
					inputCamaId.name = 'cama_id';
					inputCamaId.value = document.getElementById('cama_id').value;
					form.appendChild(inputCamaId);

					const inputCamaStatus = document.createElement('input');
					inputCamaStatus.type = 'hidden';
					inputCamaStatus.name = 'cama_status';
					inputCamaStatus.value = 'Desbloquear';
					form.appendChild(inputCamaStatus);

					document.body.appendChild(form);
					form.submit();
				}
				break;
			case 'egresarBtn':
				confirmation = confirm('¿Está seguro de que desea egresar al paciente?');
				if (confirmation) {
					this.location.href = 'controllers/egresar_paciente.php?patient_id=' + patient_id_let + "&cama_id=" + cama_id_let;
				}
				break;
			case 'camilleroBtn':
				break;
			case 'paseBtn':
				document.getElementById('pase_popup').style.display = 'flex';
				let ubiid = document.getElementById('unidad_id_selector-pase').value;
				loadBedsForLocation(ubiid);
				break;
			case 'cancelReserveBtn':
				document.getElementById('reserve_popup').style.display = 'none';
				break;
			case 'cancelBlockBtn':
				document.getElementById('bloquear_popup').style.display = 'none';
				break;
			case 'cancelBlockBtnIng':
				document.getElementById('ingresar_popup').style.display = 'none';
				break;
			case 'cancelPaseBtn':
				document.getElementById('pase_popup').style.display = 'none';
			case 'editBtnBed':
				// Asegúrate de que cama_name, description, etc. existan en el DOM
				$('#cama_name, #description, #complejidad, #unidad_select_selector-container').prop('disabled', false);
				$('#cama_name, #description, #complejidad, #unidad_select_selector-container').prop('readonly', false);
				$('#editBtnBed').hide();
				$('#saveBtnBed').show();
				$('#canBtnBed').show();
				$('#newBtnBed').hide();
				break;
			case 'canBtnBed':
				location.reload();
				break;
			case 'deleteBtnBed':
				document.getElementById('delete_poup').style.display = 'flex';
				break;
			case 'cancelDeleteBtn':
				document.getElementById('delete_poup').style.display = 'none';
				break;
			// --- Nuevos casos para los botones de paciente ---
			case 'editPatientBtn':
				togglePatientFormFields(true); // Habilitar campos
				$('#savePatientBtn').show(); // Mostrar botón "Guardar"
				$('#editPatientBtn').hide(); // Ocultar botón "Editar"
				$('#cancelPatientBtn').show(); // Mostrar botón "Cancelar"
				$('#ingresarPaciente').hide(); // Ocultar botón "Ingresar paciente" al editar
				break;
			case 'cancelPatientBtn':
				const patientIdToRevert = document.getElementById('info_paciente_content') ? (JSON.parse(document.getElementById('info_paciente_content').dataset.patientData || '{}')).id : null;
				if (patientIdToRevert) {
					// Si era un paciente existente, recargar el formulario para revertir cambios
					window.setInfo_paciente(patientIdToRevert, document.getElementById('info_paciente').dataset.camaId);
				} else {
					// Si era un paciente nuevo y se cancela, simplemente ocultar el formulario de paciente
					document.getElementById('back').style.display = 'none';
					document.getElementById('info_paciente').style.display = 'none';
				}
				break;
			case 'ingresarPaciente':
				const camaIdForIngreso = document.getElementById('info_paciente').dataset.camaId;
				const patientIdForIngreso = document.getElementById('info_paciente_content') ? (JSON.parse(document.getElementById('info_paciente_content').dataset.patientData || '{}')).id : null;

				if (!camaIdForIngreso || !patientIdForIngreso) {
					toast('No se puede ingresar al paciente. Falta ID de cama o paciente.', 'error');
					return;
				}

				confirmation = confirm('¿Está seguro de que desea ingresar a este paciente en la cama?');
				if (confirmation) {
					try {
						const response = await fetch('controllers/ingresar_paciente.php', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
							},
							body: JSON.stringify({ cama_id: camaIdForIngreso, patient_id: patientIdForIngreso }),
						});
						const result = await response.json();
						if (response.ok) {
							toast(result.message || 'Paciente ingresado exitosamente.', 'success');
							// Cerrar formulario de paciente y recargar la vista principal de camas
							document.getElementById('back').style.display = 'none';
							document.getElementById('info_paciente').style.display = 'none';
							location.reload(); // Recarga la página para actualizar el estado de la cama
						} else {
							toast('Error al ingresar paciente: ' + (result.message || 'Error desconocido.'), 'error');
						}
					} catch (error) {
						console.error('Error de red o JS al ingresar paciente:', error);
						toast('Ocurrió un error al intentar ingresar al paciente. Por favor, intente de nuevo.', 'error');
					}
				}
				break;
		}
	});
});

// Función global setInfo_paciente
window.setInfo_paciente = async function (id, camaId = null) {
	//console.log('[Paso 1] Mostrar formularios y ocultar info_cama', { id });
	document.getElementById('back').style.display = 'flex';
	document.getElementById('info_paciente').style.display = 'flex';
	document.getElementById('info_cama').style.display = 'none';

	// Almacenar el camaId en el contenedor del formulario de paciente
	document.getElementById('info_paciente').dataset.camaId = camaId;
	console.log("ID:", id, "CAMA:", camaId);

	try {
		const url = id ? `controllers/info_patient.php?id=${id}` : `controllers/info_patient.php`;
		//console.log('[Paso 2] Fetch info_patient.php', { url: url });
		const response = await fetch(url);

		if (!response.ok) {
			console.error('[Paso 3] Error al cargar el formulario del paciente', { status: response.status, statusText: response.statusText });
			throw new Error(`Error al cargar el formulario del paciente: ${response.status} ${response.statusText}`);
		}

		const formHtml = await response.text();
		//console.log('[Paso 4] HTML del formulario recibido', { formHtml });

		document.getElementById('info_paciente').innerHTML = formHtml;

		const patientDataElement = document.getElementById('info_paciente_content');
		let patient = {};
		if (patientDataElement && patientDataElement.dataset.patientData) {
			try {
				patient = JSON.parse(patientDataElement.dataset.patientData);
				//console.log('[Paso 5] Datos del paciente parseados', { patient });
			} catch (e) {
				console.error('[Paso 5] Error al parsear los datos del paciente', e);
			}
		} else {
			//console.log('[Paso 5] No hay datos de paciente en data-patient-data');
		}

		// Determinar si es un paciente existente o nuevo
		const isExistingPatient = (patient && !patient.error && id !== null);

		// --- Elementos del formulario de Información Personal ---
		const nombresDNIInput = document.getElementById('nombres_dni');
		const apellidosDNIInput = document.getElementById('apellidos_dni');
		const nombreAutopercibidoInput = document.getElementById('nombre_autopercibido');
		const dniRectificadoCheckbox = document.getElementById('dni_rectificado');
		const nombreAdministrativoFinalInput = document.getElementById('nombre_administrativo_final');
		const sexoDNISelect = document.getElementById('sexo_dni');
		const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
		const tipoDocumentoSelect = document.getElementById('tipo_documento_patient');
		const numeroDocumentoInput = document.getElementById('numero_documento');
		const identidadGeneroSelect = $('#identidad_genero');
		const nombreAutopercibidoContainer = document.getElementById('nombre_autopercibido_container');
		const dniRectificadoContainer = document.getElementById('dni_rectificado_container');
		const nombreAdministrativoRow = document.getElementById('nombre_administrativo_row');
		//console.log('[Paso 6] Elementos del formulario obtenidos', {
		//     nombresDNIInput, apellidosDNIInput, nombreAutopercibidoInput, dniRectificadoCheckbox, nombreAdministrativoFinalInput,
		//     sexoDNISelect, fechaNacimientoInput, tipoDocumentoSelect, numeroDocumentoInput, identidadGeneroSelect,
		//     nombreAutopercibidoContainer, dniRectificadoContainer, nombreAdministrativoRow
		// });

		function actualizarNombreAdministrativo() {
			const nombresDNI = nombresDNIInput.value.trim();
			const apellidosDNI = apellidosDNIInput.value.trim();
			const nombreAutopercibido = nombreAutopercibidoInput.value.trim();
			const dniRectificado = dniRectificadoCheckbox.checked;
			const identidadGeneroValue = identidadGeneroSelect.val();
			const isNonCis = (identidadGeneroValue && identidadGeneroValue !== 'Mujer cis' && identidadGeneroValue !== 'Varón cis');
			const isAutopercibidoNamePresent = nombreAutopercibido !== '';
			let nombreFinal = '';
			let nombreLegalBase = '';
			if (apellidosDNI) {
				nombreLegalBase += `${apellidosDNI}`;
			}
			if (nombresDNI) {
				if (nombreLegalBase) nombreLegalBase += ', ';
				const iniciales = nombresDNI.split(' ').filter(Boolean).map(word => word.charAt(0).toUpperCase()).join('');
				nombreLegalBase += `(${iniciales})`;
			}
			if (isNonCis && isAutopercibidoNamePresent) {
				if (dniRectificado) {
					nombreFinal = `${apellidosDNI}, ${nombresDNI}`.trim();
					if (nombreAutopercibido) {
						nombreFinal += ` (${nombreAutopercibido})`;
					}
				} else {
					nombreFinal = `${nombreLegalBase} ${nombreAutopercibido}`;
				}
			} else if (nombresDNI && apellidosDNI) {
				nombreFinal = `${apellidosDNI}, ${nombresDNI}`;
			} else if (apellidosDNI) {
				nombreFinal = apellidosDNI;
			} else if (nombresDNI) {
				nombreFinal = nombresDNI;
			} else {
				nombreFinal = '';
			}
			nombreAdministrativoFinalInput.value = nombreFinal;
			//console.log('[Paso 7] Nombre administrativo actualizado', { nombreFinal });
		}

		function toggleGenderIdentityFieldsVisibility() {
			const identidadGeneroValue = identidadGeneroSelect.val();
			const nombreAutopercibidoValue = nombreAutopercibidoInput.value.trim();
			const isNonCis = (identidadGeneroValue && identidadGeneroValue !== 'Mujer cis' && identidadGeneroValue !== 'Varón cis');
			const isAutopercibidoNamePresent = nombreAutopercibidoValue !== '';
			if (isNonCis || isAutopercibidoNamePresent) {
				nombreAutopercibidoContainer.style.display = 'flex';
			} else {
				nombreAutopercibidoContainer.style.display = 'none';
				nombreAutopercibidoInput.value = '';
			}
			if (isNonCis && isAutopercibidoNamePresent) {
				dniRectificadoContainer.style.display = 'flex';
				nombreAdministrativoRow.style.display = 'flex';
			} else {
				dniRectificadoContainer.style.display = 'none';
				dniRectificadoCheckbox.checked = false;
				nombreAdministrativoRow.style.display = 'none';
			}
			actualizarNombreAdministrativo();
			//console.log('[Paso 8] Visibilidad de campos de identidad de género actualizada', {
			//     isNonCis, isAutopercibidoNamePresent,
			//     nombreAutopercibidoContainer: nombreAutopercibidoContainer.style.display,
			//     dniRectificadoContainer: dniRectificadoContainer.style.display,
			//     nombreAdministrativoRow: nombreAdministrativoRow.style.display
			// });
		}

		nombresDNIInput.addEventListener('input', actualizarNombreAdministrativo);
		apellidosDNIInput.addEventListener('input', actualizarNombreAdministrativo);
		dniRectificadoCheckbox.addEventListener('change', actualizarNombreAdministrativo);
		identidadGeneroSelect.on('change', toggleGenderIdentityFieldsVisibility);
		nombreAutopercibidoInput.addEventListener('input', toggleGenderIdentityFieldsVisibility);

		function capitalizeFirstLetter(str) {
			if (!str) return '';
			return str.charAt(0).toUpperCase() + str.slice(1);
		}
		function capitalizeAllFirstLetters(str) {
			if (!str) return '';
			return str.split(' ').map(capitalizeFirstLetter).join(' ');
		}
		const inputsToCapitalize = [
			nombresDNIInput,
			apellidosDNIInput,
			nombreAutopercibidoInput
		];
		inputsToCapitalize.forEach(input => {
			if (input) {
				input.addEventListener('input', function (e) {
					const cursorPos = input.selectionStart;
					const capitalized = capitalizeAllFirstLetters(input.value);
					if (input.value !== capitalized) {
						input.value = capitalized;
						input.setSelectionRange(cursorPos, cursorPos);
					}
				});
			}
		});
		//console.log('[Paso 9] Capitalización automática configurada');

		$('#sexo_dni').select2({ placeholder: 'Seleccionar un sexo...' });
		$('#tipo_documento_patient').select2({ placeholder: 'Seleccionar un tipo...' });
		$('#identidad_genero').select2({ placeholder: 'Seleccionar una identidad...' });
		//console.log('[Paso 10] Select2 inicializado para sexo, tipo documento e identidad de género');

		// --- Elementos y lógica del formulario de Dirección (Georef) ---
		const paisSelect = $('#pais');
		const provinciaSelect = $('#provincia');
		const partidoSelect = $('#partido');
		const ciudadSelect = $('#ciudad');
		const codigoPostalInput = document.getElementById('codigo_postal');
		const calleInput = document.getElementById('calle');
		const numeroCalleInput = document.getElementById('numero');
		const pisoInput = document.getElementById('piso');
		const departamentoInput = document.getElementById('departamento');
		const barrioInput = document.getElementById('barrio');
		const GEOREF_API_BASE_URL = 'https://apis.datos.gob.ar/georef/api/';
		const RESTCOUNTRIES_API_BASE_URL = 'https://restcountries.com/v3.1/';

		// Inicializar Select2 de dirección (sin adjuntar listeners de 'change' aún)
		// Estos ya están inicializados en $(document).ready() del archivo principal.
		// Solo los inicializamos aquí si no lo están, para los casos de carga dinámica.
		if (!paisSelect.data('select2')) {
			paisSelect.select2({ placeholder: "Seleccione un país" });
		}
		if (!provinciaSelect.data('select2')) {
			provinciaSelect.select2({ placeholder: "Seleccione una provincia" });
		}
		if (!partidoSelect.data('select2')) {
			partidoSelect.select2({ placeholder: "Seleccione un partido" });
		}
		if (!ciudadSelect.data('select2')) {
			ciudadSelect.select2({ placeholder: "Seleccione una ciudad/localidad" });
		}
		//console.log('[Paso 11] Select2 inicializado para dirección');

		function clearAndDisableSelect2(selectElement, placeholderText) {
			// Solo destruir Select2 si ya está inicializado
			if (selectElement.data('select2')) {
				selectElement.select2('destroy');
			}
			selectElement.empty().append($('<option value="">' + placeholderText + '</option>')).val('').trigger('change');
			selectElement.prop('disabled', true);
			// Re-inicializar Select2 después de manipular el DOM
			selectElement.select2({ placeholder: placeholderText });
			//console.log('[Paso 12] clearAndDisableSelect2', { selectElement, placeholderText });
		}

		async function cargarPaises(selectedValue = null) {
			//console.log('[Paso 13] Cargando países...');
			// Destruir Select2 antes de vaciar, solo si ya está inicializado
			if (paisSelect.data('select2')) {
				paisSelect.select2('destroy');
			}
			paisSelect.empty().append($('<option value="">Cargando países...</option>'));
			paisSelect.prop('disabled', true);
			try {
				const response = await fetch(`${RESTCOUNTRIES_API_BASE_URL}all?fields=cca3,name`);
				const data = await response.json();
				paisSelect.empty().append($('<option value="">Seleccione un país</option>'));
				data.sort((a, b) => a.name.common.localeCompare(b.name.common)).forEach(country => {
					const option = new Option(country.name.common, country.cca3, false, false);
					paisSelect.append(option);
				});
				paisSelect.prop('disabled', false);
				paisSelect.select2({ placeholder: "Seleccione un país" }); // Re-inicializar
				if (selectedValue) {
					paisSelect.val(selectedValue).trigger('change');
				} else {
					paisSelect.trigger('change');
				}
				//console.log('[Paso 13] Países cargados', { data, selectedValue });
			} catch (error) {
				console.error('[Paso 13] Error cargando países', error);
				paisSelect.empty().append($('<option value="">Error al cargar países</option>'));
				paisSelect.select2({ placeholder: "Error al cargar países" }); // Re-inicializar en caso de error
			}
		}

		async function cargarProvincias(provinciaCodeOrName, selectedValue = null) {
			//console.log('[Paso 14] Cargando provincias...', { provinciaCodeOrName, selectedValue });
			// Destruir Select2 antes de vaciar, solo si ya está inicializado
			if (provinciaSelect.data('select2')) {
				provinciaSelect.select2('destroy');
			}
			clearAndDisableSelect2(provinciaSelect, 'Cargando provincias...');
			clearAndDisableSelect2(partidoSelect, 'Seleccione un partido');
			clearAndDisableSelect2(ciudadSelect, 'Seleccione una ciudad/localidad');
			try {
				const response = await fetch(`${GEOREF_API_BASE_URL}provincias?campos=id,nombre&max=100`);
				if (!response.ok) {
					console.error('[Paso 14] API responded with an error', { status: response.status, statusText: response.statusText });
					clearAndDisableSelect2(provinciaSelect, 'Error al cargar provincias');
					return;
				}
				const data = await response.json();
				if (data.provincias && Array.isArray(data.provincias)) {
					provinciaSelect.empty().append($('<option value="">Seleccione una provincia</option>'));
					data.provincias.sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(provincia => {
						const option = new Option(provincia.nombre, provincia.nombre, false, false);
						provinciaSelect.append(option);
					});
					provinciaSelect.prop('disabled', false);
					provinciaSelect.select2({ placeholder: "Seleccione una provincia" }); // Re-inicializar
					if (selectedValue) {
						provinciaSelect.val(selectedValue).trigger('change');
					} else {
						provinciaSelect.trigger('change');
					}
					//console.log('[Paso 14] Provincias cargadas', { data, selectedValue });
				} else {
					console.warn('[Paso 14] No se encontraron provincias', data);
					clearAndDisableSelect2(provinciaSelect, 'No se encontraron provincias');
				}
			} catch (error) {
				console.error('[Paso 14] Error cargando provincias', error);
				clearAndDisableSelect2(provinciaSelect, 'Error al cargar provincias');
			}
		}

		async function cargarPartidos(provinciaNombre, selectedValue = null) {
			//console.log('[Paso 15] Cargando partidos...', { provinciaNombre, selectedValue });
			// Destruir Select2 antes de vaciar, solo si ya está inicializado
			if (partidoSelect.data('select2')) {
				partidoSelect.select2('destroy');
			}
			clearAndDisableSelect2(partidoSelect, 'Cargando partidos...');
			clearAndDisableSelect2(ciudadSelect, 'Seleccione una ciudad/localidad');
			if (!provinciaNombre) {
				clearAndDisableSelect2(partidoSelect, 'Seleccione un partido');
				return;
			}
			try {
				const provResponse = await fetch(`${GEOREF_API_BASE_URL}provincias?nombre=${provinciaNombre}&campos=id`);
				const provData = await provResponse.json();
				if (!provData.provincias || provData.provincias.length === 0) {
					console.warn('[Paso 15] Provincia no encontrada por nombre', provinciaNombre);
					clearAndDisableSelect2(partidoSelect, 'Error: Provincia no encontrada');
					return;
				}
				const provinciaId = provData.provincias[0].id;
				const response = await fetch(`${GEOREF_API_BASE_URL}departamentos?provincia=${provinciaId}&campos=id,nombre&max=500`);
				if (!response.ok) {
					console.error('[Paso 15] API responded with an error', { status: response.status, statusText: response.statusText });
					clearAndDisableSelect2(partidoSelect, 'Error al cargar partidos');
					return;
				}
				const data = await response.json();
				if (data.departamentos && Array.isArray(data.departamentos)) {
					partidoSelect.empty().append($('<option value="">Seleccione un partido</option>'));
					data.departamentos.sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(partido => {
						const option = new Option(partido.nombre, partido.nombre, false, false);
						partidoSelect.append(option);
					});
					partidoSelect.prop('disabled', false);
					partidoSelect.select2({ placeholder: "Seleccione un partido" }); // Re-inicializar
					if (selectedValue) {
						partidoSelect.val(selectedValue).trigger('change');
					} else {
						partidoSelect.trigger('change');
					}
					//console.log('[Paso 15] Partidos cargados', { data, selectedValue });
				} else {
					console.warn('[Paso 15] No se encontraron partidos', data);
					clearAndDisableSelect2(partidoSelect, 'No se encontraron partidos');
				}
			} catch (error) {
				console.error('[Paso 15] Error cargando partidos', error);
				clearAndDisableSelect2(partidoSelect, 'Error al cargar partidos');
			}
		}

		async function cargarCiudades(provinciaNombre, partidoNombre = null, selectedValue = null) {
			//console.log('[Paso 16] Cargando ciudades/localidades...', { provinciaNombre, partidoNombre, selectedValue });
			// Destruir Select2 antes de vaciar, solo si ya está inicializado
			if (ciudadSelect.data('select2')) {
				ciudadSelect.select2('destroy');
			}
			clearAndDisableSelect2(ciudadSelect, 'Cargando ciudades/localidades...');
			if (!provinciaNombre) {
				clearAndDisableSelect2(ciudadSelect, 'Seleccione una ciudad/localidad');
				return;
			}
			let url = `${GEOREF_API_BASE_URL}localidades?provincia=${provinciaNombre}&campos=id,nombre&max=2000`;
			if (partidoNombre) {
				const partidoResponse = await fetch(`${GEOREF_API_BASE_URL}departamentos?nombre=${partidoNombre}&campos=id`);
				const partidoData = await partidoResponse.json();
				if (!partidoData.departamentos || partidoData.departamentos.length === 0) {
					console.warn('[Paso 16] Partido no encontrado por nombre', partidoNombre);
					clearAndDisableSelect2(ciudadSelect, 'Error: Partido no encontrado');
					return;
				}
				const partidoId = partidoData.departamentos[0].id;
				url = `${GEOREF_API_BASE_URL}localidades?departamento=${partidoId}&campos=id,nombre&max=2000`;
			}
			try {
				const response = await fetch(url);
				if (!response.ok) {
					console.error('[Paso 16] API responded with an error', { status: response.status, statusText: response.statusText });
					clearAndDisableSelect2(ciudadSelect, 'Error al cargar ciudades/localidades');
					return;
				}
				const data = await response.json();
				if (data.localidades && Array.isArray(data.localidades)) {
					ciudadSelect.empty().append($('<option value="">Seleccione una ciudad/localidad</option>'));
					data.localidades.sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(localidad => {
						const option = new Option(localidad.nombre, localidad.nombre, false, false);
						ciudadSelect.append(option);
					});
					ciudadSelect.prop('disabled', false);
					ciudadSelect.select2({ placeholder: "Seleccione una ciudad/localidad" }); // Re-inicializar
					if (selectedValue) {
						ciudadSelect.val(selectedValue).trigger('change');
					} else {
						ciudadSelect.trigger('change');
					}
					//console.log('[Paso 16] Ciudades/localidades cargadas', { data, selectedValue });
				} else {
					console.warn('[Paso 16] No se encontraron ciudades/localidades', data);
					clearAndDisableSelect2(ciudadSelect, 'No se encontraron ciudades/localidades');
				}
			} catch (error) {
				console.error('[Paso 16] Error cargando ciudades', error);
				clearAndDisableSelect2(ciudadSelect, 'Error al cargar ciudades/localidades');
			}
		}

		// --- Lógica para inicializar y precargar datos ---
		async function initializeFormElements(patient) {
			//console.log('[Paso 21] Inicializando formulario con datos de paciente', { patient });
			if (patient && !patient.error) {
				// Rellenar campos de texto y Select2 básicos
				$('#sexo_dni').val(patient.gender || '').trigger('change');
				$('#tipo_documento_patient').val(patient.document_type || '').trigger('change');
				$('#identidad_genero').val(patient.gender_identity || '').trigger('change');
				toggleGenderIdentityFieldsVisibility();
				actualizarNombreAdministrativo();

				// --- Rellenar campos de Dirección ---
				calleInput.value = patient.calle || '';
				numeroCalleInput.value = patient.numero || '';
				pisoInput.value = patient.piso || '';
				departamentoInput.value = patient.departamento || '';
				barrioInput.value = patient.barrio || '';
				// Asignar el código postal temprano, ya que no depende de Georef
				codigoPostalInput.value = patient.codigo_postal || '';
				//console.log('[Paso 21] Código postal asignado (inicialmente)', { codigo_postal: codigoPostalInput.value });


				// --- Carga de Georef en secuencia ---
				await cargarPaises(patient.country); // Siempre carga países y selecciona el guardado o ARG
				console.log(patient)

				if (patient.provincia) {
					await cargarProvincias(patient.provincia, patient.provincia); // Carga y selecciona provincia
				}

				if (patient.partido) {
					await cargarPartidos(patient.provincia, patient.partido); // Carga y selecciona partido
				}

				if (patient.ciudad) {
					await cargarCiudades(patient.provincia, patient.partido, patient.ciudad); // Carga y selecciona ciudad
				}

				//console.log('[Paso 21] Formulario inicializado con datos de paciente');
			} else if (patient && patient.error) {
				console.warn('[Paso 21] Error al cargar paciente en initializeFormElements', patient.error);
				alert(patient.error);
			} else {
				//console.log('[Paso 21] No se pasaron datos de paciente para cargar. Inicializando formulario vacío.');
				cargarPaises();
				toggleGenderIdentityFieldsVisibility();
				codigoPostalInput.value = ''; // Asegura que esté vacío si no hay paciente
			}
		}

		// Llama a la función de inicialización con los datos del paciente
		await initializeFormElements(patient); // Usamos await aquí para que termine antes de adjuntar listeners

		// ******** ADJUNTAR LISTENERS DE CAMBIO DESPUÉS DE LA INICIALIZACIÓN COMPLETA ********
		// Esto evita que los eventos 'change' se disparen durante la carga inicial del formulario.

		paisSelect.off('change').on('change', function () {
			//console.log('[Paso 17] Cambio de país', { value: paisSelect.val() });
			if (paisSelect.val() === 'ARG') {
				provinciaSelect.prop('disabled', false);
				cargarProvincias();
			} else {
				clearAndDisableSelect2(provinciaSelect, 'Seleccione una provincia');
				clearAndDisableSelect2(partidoSelect, 'Seleccione un partido');
				clearAndDisableSelect2(ciudadSelect, 'Seleccione una ciudad/localidad');
				// Limpiar CP si se cambia de país y no es ARG, o si no hay ciudad seleccionada
				codigoPostalInput.value = '';
			}
		});

		provinciaSelect.off('change').on('change', function () {
			const selectedProvinciaNombre = provinciaSelect.val();
			//console.log('[Paso 18] Cambio de provincia', { selectedProvinciaNombre });
			if (selectedProvinciaNombre) {
				partidoSelect.prop('disabled', false);
				cargarPartidos(selectedProvinciaNombre);
				cargarCiudades(selectedProvinciaNombre);
			} else {
				clearAndDisableSelect2(partidoSelect, 'Seleccione un partido');
				clearAndDisableSelect2(ciudadSelect, 'Seleccione una ciudad/localidad');
			}
			// Limpiar CP aquí, ya que al cambiar la provincia, la ciudad y partido se limpian
			codigoPostalInput.value = '';
		});

		partidoSelect.off('change').on('change', function () {
			const selectedProvinciaNombre = provinciaSelect.val();
			const selectedPartidoNombre = partidoSelect.val();
			//console.log('[Paso 19] Cambio de partido', { selectedProvinciaNombre, selectedPartidoNombre });
			if (selectedPartidoNombre) {
				ciudadSelect.prop('disabled', false);
				cargarCiudades(selectedProvinciaNombre, selectedPartidoNombre);
			} else if (selectedProvinciaNombre) {
				ciudadSelect.prop('disabled', false);
				cargarCiudades(selectedProvinciaNombre);
			} else {
				clearAndDisableSelect2(ciudadSelect, 'Seleccione una ciudad/localidad');
			}
			// Limpiar CP aquí, ya que al cambiar el partido, la ciudad se limpia
			codigoPostalInput.value = '';
		});

		ciudadSelect.off('change').on('change', function () {
			//console.log('[Paso 20] Cambio de ciudad/localidad', { value: ciudadSelect.val() });
			// El código postal solo se limpia si NO hay una ciudad seleccionada
			if (!ciudadSelect.val()) {
				codigoPostalInput.value = '';
			}
			// No hacemos nada si hay valor, ya que este input no se llena automáticamente desde la API Georef
		});

		// ******** LÓGICA PARA GUARDAR EL FORMULARIO DEL PACIENTE ********
		$(document).off('click', '#savePatientBtn').on('click', '#savePatientBtn', async function (e) {
			e.preventDefault();
			const patientId = patientDataElement ? (JSON.parse(patientDataElement.dataset.patientData || '{}')).id : null;
			const formData = {
				id: patientId,
				last_name: $('#apellidos_dni').val(),
				name: $('#nombres_dni').val(),
				gender: $('#sexo_dni').val(),
				document_type: $('#tipo_documento_patient').val(),
				document: $('#numero_documento').val(),
				birth_date: $('#fecha_nacimiento').val(),
				phone_number: $('#phone_number').val(),
				family_phone_number: $('#family_phone_number').val(),
				email: $('#email').val(),
				country: $('#pais').val(),
				provincia: $('#provincia').val(),
				partido: $('#partido').val(),
				ciudad: $('#ciudad').val(),
				codigo_postal: $('#codigo_postal').val(),
				calle: $('#calle').val(),
				numero: $('#numero').val(),
				piso: $('#piso').val(),
				departamento: $('#departamento').val(),
				barrio: $('#barrio').val(),
				health_insurance: $('#health_insurance').val(),
				health_insurance_number: $('#health_insurance_number').val(),
				administrative_name: $('#nombre_administrativo_final').val(),
				gender_identity: $('#identidad_genero').val(),
				self_perceived_name: $('#nombre_autopercibido').val(),
				dni_rectified: $('#dni_rectificado').is(':checked'),
				phone_number_alt: $('#phone_number_alt').val(),
				family_phone_number_alt: $('#family_phone_number_alt').val()
			};
			//console.log('[Paso 22] Datos del formulario recolectados', { formData });
			if (!formData.name || !formData.name.trim() || !formData.last_name || !formData.last_name.trim() || !formData.document_type || !formData.document_type.trim() || !formData.document || !formData.document.trim()) {
				alert('Por favor, complete los campos obligatorios: Nombres, Apellidos, Tipo y Número de Documento.');
				//console.warn('[Paso 22] Validación fallida', { formData });
				return;
			}
			const endpoint = patientId ? 'controllers/update_patient.php' : 'controllers/create_patient.php';
			const method = 'POST';
			//console.log('[Paso 23] Enviando datos al endpoint', { endpoint, method, formData });
			try {
				const response = await fetch(endpoint, {
					method: method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(formData),
				});
				const result = await response.json();
				if (response.ok) {
					toast(result.message || 'Datos guardados exitosamente.', 'success');
					// Bloquear los campos y ajustar la visibilidad de los botones
					togglePatientFormFields(false);
					$('#savePatientBtn').hide();
					$('#editPatientBtn').show();
					$('#cancelPatientBtn').hide();
					// Mostrar el botón de ingresar paciente si la cama está disponible
					const currentCamaId = document.getElementById('info_paciente').dataset.camaId;
					if (currentCamaId && patientId) { // Solo si hay cama y paciente existente
						$('#ingresarPaciente').show();
					}
					//console.log('[Paso 24] Datos guardados exitosamente', { result });
				} else {
					toast('Error al guardar los datos: ' + (result.message || 'Error desconocido.'), 'error');
					console.error('[Paso 24] Error del servidor', { result });
				}
			} catch (error) {
				console.error('[Paso 24] Error de red o JS al guardar', error);
				toast('Ocurrió un error al intentar guardar los datos. Por favor, intente de nuevo.', 'error');
			}
		});
		//console.log('[Paso 25] Lógica de guardado de paciente configurada');

		// Configurar el estado inicial de los campos y botones del paciente
		if (isExistingPatient) {
			$('#savePatientBtn').hide(); // Ocultar "Guardar" inicialmente para paciente existente
			$('#editPatientBtn').show(); // Mostrar "Editar"
			$('#cancelPatientBtn').hide(); // Ocultar "Cancelar"
			togglePatientFormFields(false); // Deshabilitar campos para paciente existente
			// Mostrar botón de ingresar paciente si hay cama asociada
			const currentCamaId = document.getElementById('info_paciente').dataset.camaId;
			if (currentCamaId) {
				$('#ingresarPaciente').show();
			} else {
				$('#ingresarPaciente').hide();
			}
		} else {
			$('#savePatientBtn').show(); // Mostrar "Nuevo paciente"
			$('#editPatientBtn').hide(); // Ocultar "Editar"
			$('#cancelPatientBtn').show(); // Mostrar "Cancelar" para nuevo paciente
			$('#ingresarPaciente').hide(); // Ocultar botón de ingresar paciente para nuevo paciente
			togglePatientFormFields(true); // Habilitar campos para nuevo paciente
		}

	} catch (error) {
		console.error('[Paso 99] Error al cargar la información del paciente', error);
		alert('No se pudo cargar la información del paciente. Por favor, intente de nuevo.');
		document.getElementById('back').style.display = 'none';
		document.getElementById('info_paciente').style.display = 'none';
	}
};

// --- Función para inicializar las acciones de la cama (si fuera necesario) ---
// Esta función debe ser llamada desde el PHP en info_cama.php
window.initBedActions = function (camaId, camaComplexity) {
	//console.log("Inicializando acciones de cama", { camaId, camaComplexity });

	const $complejidadSelect = $('#complejidad');

	// Re-inicializar Select2 para complejidad cada vez que se carga info_cama.php
	if ($complejidadSelect.data('select2')) {
		$complejidadSelect.select2('destroy');
	}
	$complejidadSelect.select2({
		placeholder: 'Seleccionar una complejidad...'
	});

	// Ocultar todos los botones de acción de cama por defecto
	$('#newBtnBed').hide();
	$('#editBtnBed').hide();
	$('#deleteBtnBed').hide();
	$('#saveBtnBed').hide();
	$('#canBtnBed').hide();

	// Habilitar/deshabilitar campos según si es una cama existente o nueva
	if (!camaId) { // Es una cama nueva
		$('#newBtnBed').show(); // Mostrar solo el botón de "Nuevo"
		$('#cama_name, #description, #complejidad').prop('disabled', false).prop('readonly', false);
		// Asegúrate de que 'unidad_select_selector-container' también esté habilitado si es un nuevo registro
		const unidadSelectContainer = document.getElementById('unidad_select_selector-container');
		if (unidadSelectContainer) {
			$(unidadSelectContainer).prop('disabled', false);
		}
	} else { // Es una cama existente
		$('#editBtnBed').show();
		$('#deleteBtnBed').show();

		// Precargar la complejidad si viene de la cama
		if (camaComplexity) {
			$complejidadSelect.val(camaComplexity).trigger('change');
			//console.log("Select2 de complejidad establecido a:", camaComplexity);
		} else {
			// Si camaComplexity no viene, Select2 leerá la opción con 'selected' del HTML
			//console.warn("No se pudo obtener el valor de complejidad para precargar. Asegúrate que la opción tiene 'selected'.");
		}

		// Deshabilitar campos por defecto para edición
		$('#cama_name, #description, #complejidad').prop('disabled', true).prop('readonly', true);
		const unidadSelectContainer = document.getElementById('unidad_select_selector-container');
		if (unidadSelectContainer) {
			$(unidadSelectContainer).prop('disabled', true); // Deshabilitar si es una cama existente
		}
	}
};

/**
 * Carga y muestra visualmente las camas de una ubicación específica.
 * @param {number} locationId El ID de la ubicación (habitación) cuyas camas se van a mostrar.
 */
async function loadBedsForLocation(locationId) {
    const bedsContainer = $('#beds_result');
    const submitPaseBtn = $('#submit_pase_btn');
    const newBedIdInput = $('#new_bed_id');
    const camaOrigenId = parseInt($('#cama_id').val()); // Obtener ID de la cama de origen, si existe

    bedsContainer.empty(); // Limpiar camas anteriores
    submitPaseBtn.prop('disabled', true); // Deshabilitar botón de submit al cargar nuevas camas
    newBedIdInput.val(''); // Limpiar la selección de cama de destino

    // Si tienes alguna funcionalidad de generación de notas con LLM, también deshabilitarla aquí
    // $('#generateNotesBtn').prop('disabled', true);
    // $('#generated_notes_display').hide().empty();

    if (!locationId) {
        bedsContainer.append('<div class="text-muted">Seleccione una unidad para ver las camas disponibles.</div>');
        return;
    }

    try {
        // Tu endpoint PHP para obtener camas. Asegúrate de que esta ruta sea correcta.
        const response = await fetch(`/SGH/public/layouts/modules/gestion_camas/controllers/get_beds.php?location_id=${locationId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        console.log('Datos de camas recibidos:', data);

        if (data && data.beds && data.beds.length > 0) {
            data.beds.forEach(bed => {
                const isSelectable = (bed.bed_status === 'Libre' || bed.bed_status === 'Reservada');

                const btn = $('<button>')
                    .addClass(`cama_${bed.bed_status} mini-bed`)
                    .attr('type', 'button')
                    .attr('data-bed-id', bed.id)
                    .html(`<i class="fa-solid fa-bed"></i><span style="font-size: 1.1vw;">${bed.name}</span>`);
                
                // Deshabilitar si no es seleccionable o si es la cama de origen
                if (!isSelectable || bed.id === camaOrigenId) {
                    btn.prop('disabled', true);
                    btn.css({'cursor': 'not-allowed', 'opacity': '0.6'});
                }

                bedsContainer.append(btn);
            });
        } else {
            bedsContainer.append('<div class="text-muted">No hay camas disponibles en esta unidad.</div>');
        }
    } catch (error) {
        console.error('Error al obtener datos de camas:', error);
        bedsContainer.empty().append('<div class="text-muted">Error al cargar las camas. Intente de nuevo.</div>');
    }
}

// Este listener de 'change' para '#unidad_select_selector-pase' ahora llama a la función loadBedsForLocation.
$(document).on('change', '#unidad_select_selector-pase', function () {
    const selectedUnidad = $(this).val();
    console.log('Unidad seleccionada para pase (desde change event):', selectedUnidad);
    
    // Llama a la función para cargar las camas de la unidad seleccionada
    loadBedsForLocation(selectedUnidad);
});

// Este listener de 'change' para '#unidad_select_selector-pase' ahora llama a la función loadBedsForLocation.
$(document).on('click', '.unidad-btn', function () {
    const selectedUnidad = $(this).val();
    console.log('Unidad seleccionada para pase (desde change event):', selectedUnidad);
    
    // Llama a la función para cargar las camas de la unidad seleccionada
    loadBedsForLocation(selectedUnidad);
});

// ESTE LISTENER DE CLIC DEBE ESTAR FUERA DEL LISTENER DE 'CHANGE'
// Se adjunta una sola vez al cargar la página para todos los elementos '.mini-bed' (incluso los que se añaden dinámicamente)
$(document).on('click', '.mini-bed', function () {
    const bedId = $(this).data('bed-id');
    const bedStatus = $(this).attr('class').split(' ').find(cls => cls.startsWith('cama_')).replace('cama_', '');

    // Solo permitir la selección si la cama es 'Libre' o 'Reservada'
    if (bedStatus === 'Libre' || bedStatus === 'Reservada') {
        console.log('ID de la cama seleccionada:', bedId);

        // Remover la clase 'selected' de todas las camas y añadirla a la actual
        $('#beds_result').find('.mini-bed').removeClass('selected');
        $(this).addClass('selected');

        // Establecer el valor del input oculto y habilitar el botón de submit
        $('#new_bed_id').val(bedId); // CORRECTO: Usando jQuery para acceder al input
        $('#submit_pase_btn').prop('disabled', false); // CORRECTO: Usando jQuery para acceder al botón

        // Si tienes la funcionalidad de generación de notas con LLM, habilitala aquí también
        // $('#generateNotesBtn').prop('disabled', false);
    } else {
        console.log('La cama seleccionada no está disponible para pase.');
        $('#submit_pase_btn').prop('disabled', true);
        // $('#generateNotesBtn').prop('disabled', true); // Deshabilitar si LLM está presente
        $('#new_bed_id').val(''); // Limpiar la selección
        $('#beds_result').find('.mini-bed').removeClass('selected'); // Asegurarse de que ninguna cama esté "seleccionada" visualmente
    }
});