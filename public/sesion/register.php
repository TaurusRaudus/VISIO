<?php
// CU - 001 Registrarse
session_start();
// Recupera los valores antiguos del formulario y errores si existen
$old  = $_SESSION['old'] ?? [];
$errs = $_SESSION['errors'] ?? [];
// Limpia los valores antiguos y errores de la sesion
unset($_SESSION['old'], $_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VISIO - Registro</title>
  <link rel="stylesheet" href="../css/sesion/register.css">
</head>
<body>
  <div class="main-layout">
    <!-- FORMULARIO DE REGISTRO -->
    <div class="register-container">
      <h2>VISIO</h2>
      <p>¡Estás a un paso de registrarte!</p>
      <!-- Formulario de registro de usuario -->
      <form action="register_procesar.php" method="POST">
        <div class="form-group">
          <label for="username">Nickname:</label>
          <input type="text" id="username" name="username"
                 value="<?php echo htmlspecialchars($old['username'] ?? ''); ?>"
                 class="<?php echo isset($errs['username']) ? 'error' : ''; ?>" required>
          <?php if (isset($errs['username'])): ?>
            <span class="error-message"><?php echo $errs['username']; ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="email">Correo electrónico:</label>
          <input type="email" id="email" name="email"
                 value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                 class="<?php echo isset($errs['email']) ? 'error' : ''; ?>" required>
          <?php if (isset($errs['email'])): ?>
            <span class="error-message"><?php echo $errs['email']; ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" name="password"
                 class="<?php echo isset($errs['password']) ? 'error' : ''; ?>" required>
          <?php if (isset($errs['password'])): ?>
            <span class="error-message"><?php echo $errs['password']; ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="confirm_password">Repetir contraseña:</label>
          <input type="password" id="confirm_password" name="confirm_password"
                 class="<?php echo isset($errs['confirm_password']) ? 'error' : ''; ?>" required>
          <?php if (isset($errs['confirm_password'])): ?>
            <span class="error-message"><?php echo $errs['confirm_password']; ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group terms">
          <input type="checkbox" id="terms" name="terms" <?php echo isset($old['terms']) ? 'checked' : ''; ?>>
          <label for="terms">
            Acepto los <a href="terminos.html" target="_blank">Términos y Condiciones</a>
          </label>
          <?php if (isset($errs['terms'])): ?>
            <span class="error-message"><?php echo $errs['terms']; ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group button-group">
          <button type="submit">Registrarse</button>
          <a href="../index.html"><button type="button">Volver</button></a>
        </div>
      </form>
    </div>
    <!-- IMAGEN A LA DERECHA -->
    <div class="image-box">
      <img src="../assets/placeholder_usuario.jpg" alt="Imagen decorativa">
    </div>
  </div>
</body>
</html>
