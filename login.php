<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.G.H. - Login</title>
    <!-- Sistema de gestión hospitalarios -->
    <link rel="icon" href="/SGH/node_modules/@fortawesome/fontawesome-free/svgs/solid/notes-medical.svg" type="image/svg+xml">

    <link rel="stylesheet" href="/SGH/public/resources/css/base.css">
    <link rel="stylesheet" href="/SGH/public/resources/css/login.css">

    <!-- FontAwesome -->
  <script src="/SGH/node_modules/@fortawesome/fontawesome-free/js/all.js"></script>
</head>
<body>

    <div class="container">
        <i class="fa-solid fa-notes-medical iconoLogo"></i>
        <div class="wrapper">
          <div class="title">
            <span>Iniciar sesión</span>
            <p>Sistema de Gestion Hospitalaria</p>
          </div>
          <p id="malLogin"><?php echo $errorLogin ?></p>
          <form method="post">
            <div class="row">
              <div class="icon">
                <i class="fa-solid fa-address-card"></i>
              </div>
              <input type="text" name="dni" placeholder="D.N.I." oninput="formatNumber(this)" required>
            </div>
            <div class="row">
              <div class="icon">
                <i class="fas fa-lock icono"></i>
              </div>
              <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <div class="row button">
              <input type="submit" value="Iniciar sesión">
            </div>
          </form>
        </div>
      </div>

      <script>
        function formatNumber(input) {
            // Eliminar caracteres que no son números
            const inputValue = input.value.replace(/\D/g, '');

            // Formatear el número con puntos si no está vacío, de lo contrario, dejar en blanco
            const formattedNumber = inputValue !== '' ? Number(inputValue).toLocaleString('es-AR') : '';

            // Actualizar el valor del campo de entrada
            input.value = formattedNumber;
        }
    </script>

</body>
</html>