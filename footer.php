<?php
// Aggiunge una linea orizzontale per separare il contenuto dal footer
//echo "<hr>";

// Genera il footer della pagina
// Mostra l'autore del file e il nome del file corrente utilizzando la funzione basename($_SERVER['SCRIPT_NAME'])
echo "<footer>Autore: Roberto Prete - File: " . basename($_SERVER['SCRIPT_NAME']) . "</footer>";
?>