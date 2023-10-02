<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.E.H. - Login</title>
    <!-- Sistema de emergentes hospitalarios -->
    <link rel="icon" href="/SEH/node_modules/@fortawesome/fontawesome-free/svg/notes-medical-solid.svg" type="image/svg+xml">

    <link rel="stylesheet" href="/SEH/public/resources/css/base.css">
    <link rel="stylesheet" href="/SEH/public/resources/css/login.css">

    <!-- FontAwesome -->
  <script src="/SEH/node_modules/@fortawesome/fontawesome-free/js/all.js"></script>
</head>
<body>

    <div class="container">
        <i class="fa-solid fa-notes-medical fa-shake iconoLogo"></i>
        <div class="wrapper">
          <div class="title">
            <span>Iniciar sesión</span>
            <p>Sistema de Emergentes hospitalarios</p>
          </div>
          <p id="malLogin"><?php echo $errorLogin ?></p>
          <form method="post">
            <div class="row">
              <div class="icon">
                <i class="fa-solid fa-user"></i>
              </div>
              <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="row">
              <div class="icon">
                <i class="fas fa-lock icono"></i>
              </div>
              <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="row button">
              <input type="submit" value="Iniciar sesión">
            </div>
          </form>
        </div>
      </div>

</body>
</html>