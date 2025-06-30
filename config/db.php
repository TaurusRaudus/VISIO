<?php
// Aqui manejamos la conexion a la base de datos
$host = "localhost";
$dbname = "visiobd2"; // <- ESTE debe coincidir con el nombre de tu base en pgAdmin
$user = "postgres";
$password = "idk123...";
// Okay, supuestamente lo de arriba debe sacarlo de .env, para eso
// hay que instalar una libreria llamada vlucas/phptoenv, pero
// aun estoy viendo mas o menos de que trata, tambien se puede
// leer el .env linea por linea, no se, revisenlo ustedes y digan
// Una vez decidido, borren este comentario.

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    // echo "Conectado correctamente a viciobd";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}
?>