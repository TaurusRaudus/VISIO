<?php
// CU - 002 Iniciar Sesion

// Recupera los datos anteriores y errores (si hubieren)
session_start();
$errs = $_SESSION['errors'] ?? [];
$old  = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
/////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Login</title>
  <meta charset="UTF-8">

  <!-- CSS VAN AQUI                         -->
  <link rel="stylesheet" href="../css/sesion/login.css">
  <!-- CSS VAN AQUI                             -->

</head>
<body>

  <div class="main-layout"> <!-- NUEVO CONTENEDOR FLEXIBLE -->

    <!-- FORMULARIO DE LOGIN -->
    <div class="login-container">
      <h2>VISIO</h2>
      <p>Bienvenido de vuelta amigo!</p>
      <form action="login_procesar.php" method="POST">
        <div class="form-group">
          <label for="email">Correo electronico:</label>
          <input type="text" id="email" name="email" 
                 value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" 
                 class="<?php echo isset($errs['email']) ? 'error' : ''; ?>" required>
          <?php if(isset($errs['email'])): ?>
            <span class="error-message"><?php echo $errs['email']; ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" name="password" 
                 class="<?php echo isset($errs['password']) ? 'error' : ''; ?>" required>
          <?php if(isset($errs['password'])): ?>
              <span class="error-message"><?php echo $errs['password']; ?></span>
          <?php endif; ?>
        </div>

        <input type="submit" value="Entrar">
      </form>

      <a href=" ../index.html"><button>Volver</button></a>
    </div>

    <!-- IMAGEN A LA DERECHA -->
    <div class="image-box">
      <img src="../uploads/intro.jpg" alt="Bienvenida al sistema">
    </div>

  </div>

</body>
</html>
