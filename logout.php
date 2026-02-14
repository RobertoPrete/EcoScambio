<?php
session_start();
foreach ($_COOKIE as $nome => $valore) { // Cicla attraverso tutti i cookie presenti
    setcookie($nome, "", time() - 3600, "/"); // e li elimina impostando una data di scadenza passata (1 ora fa) e il percorso "/"
}
session_destroy();
header('Location: home.php');
exit;
?>