<?php
// Avvia la sessione per accedere ai dati dell'utente memorizzati nella sessione
session_start();

// Recupera il nome utente dalla sessione, se disponibile, altrimenti imposta "non loggato"
//$user = isset($_SESSION['user']) ? $_SESSION['user'] : 'non loggato';
if (isset($_SESSION["utente"]))
    $utente=$_SESSION["utente"];
else
    $utente=$_SESSION["utente"]= "non loggato";

// Recupera il credito dell'utente dalla sessione, se disponibile, altrimenti imposta 0.00
//$credit = isset($_SESSION['credit']) ? $_SESSION['credit'] : 0.00;
if (isset($_SESSION["saldo"]))
    $saldo=$_SESSION["saldo"];
else
    $saldo=$_SESSION["saldo"]= 0.00;

//$typeutente = isset($_SESSION['type_utente']) ? $_SESSION['type_utente'] : '';
if (isset($_SESSION["tipoUtente"]))
    $tipoUtente=$_SESSION["tipoUtente"];
else
    $tipoUtente=$_SESSION["tipoUtente"]= "";

?>

<!--Inizio del documento HTML e meta tag!-->
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8"> <!-- Specifica la codifica dei caratteri -->
  <meta name="author" content="Roberto Prete">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Rende il sito responsive -->
  <meta name="description" content="Pagina di intestazione della piattaforma ECOnnectify"> <!-- Descrizione del sito -->
  <meta name="keywords" content="eco, economia circolare, sostenibilità, riuso, riciclo, aziende, artigiani"> <!-- Parole chiave -->
  <title>ECOnnectify</title> <!-- Titolo della pagina -->
  <link rel="stylesheet" href="style.css"> <!-- Collegamento al file CSS -->
  <!--<link rel="icon" type="image/jpeg" href="img/poli.jpeg">!--> <!-- Inserisce la favicon -->
</head>
    <body>';

    <!--Genera l'intestazione della pagina con il titolo del sito!-->
        <header>
                <h1><img id="logo" src="img/logo1.jpg" alt="ECOnnectify"></h1>
                <p class='slogan'>"Dallo scarto al valore: il futuro è sostenibile."</p>
        </header>
        <?php
            //Mostra il nome dell'utente e il credito disponibile in alto a destra
            echo "<div style='text-align:right; font-size:0.9em;'>Utente: $utente | Credito: € " . number_format($saldo, 2) . "</div>";
        ?>

        <!--Genera il menu di navigazione con i link alle pagine principali del sito!-->
        <nav>
            <a href='home.php'>HOME</a> | <!-- Link alla pagina principale -->
            <a href='lista.php'>LISTA</a> | <!-- Link alla pagina con la lista dei materiali -->
            <a href='offerta.php'>OFFERTA</a> | <!-- Link alla pagina per gestire le offerte -->
            <!-- CONTROLLA TIPO UTENTE  -->
            <a href='domanda.php'>DOMANDA</a> | <!-- Link alla pagina per gestire le domande -->
            <a href='registrazione.php'>REGISTRA</a> | <!-- Link alla pagina di registrazione -->
            <a href='login.php'>LOGIN</a> | <!-- Link alla pagina di login -->
            <!--<a href='logout.php'>LOGOUT</a>  // io lo attiverei solo se l'utente è loggato -->
        </nav><hr> // Aggiunge una linea orizzontale sotto il menu di navigazione

        <!--Commento per un link opzionale alla pagina "CONFERMA", da riattivare se necessario
        <a href='conferma.php'>CONFERMA</a> |  per lo spazio CONFERMA, se dovesse essere necessario riaggiungerlo -->

    </body>
</html>