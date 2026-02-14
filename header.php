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
if (isset($_SESSION["credito"]))
    $credito=$_SESSION["credito"];
else
    $credito=$_SESSION["credito"]= 0.00;

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
        <script>
            function confermaLogout() { 
                return confirm('Sei sicuro di voler uscire?')
            }
        </script>
    </head>
    <body>
        <!--Genera l'intestazione della pagina con il titolo del sito!-->
        <header>
                <?php
                //Mostra il nome dell'utente e il credito disponibile in alto a destra
                echo "<div style='text-align:right; font-size:0.9em;'>Utente: $utente | Credito: € " . number_format($credito, 2) . "</div>";
                ?>
                <h1><a href="home.php"><img id="logo" src="img/logo1.jpg" alt="logo ECOnnectify"></a></h1>
                <p class='slogan'>"Dallo scarto al valore: il futuro è sostenibile."</p>             
        </header>

        <!--Genera il menu di navigazione con i link alle pagine principali del sito!-->
        <nav>
            <h2><strong>Menù</strong></h2>            
            <ul class='menu'> <!-- Inizio dell'elenco puntato per il menu di navigazione -->
                <li><a href='home.php'>HOME</a> <!-- Link alla pagina principale --></li>
                <li><a href='lista.php'>LISTA</a> <!-- Link alla pagina con la lista dei materiali --></li>
                <li><a href='offerta.php'>OFFERTA</a> <!-- Link alla pagina per gestire le offerte --></li>
                <li><a href='domanda.php'>DOMANDA</a> <!-- Link alla pagina per gestire le domande --></li>
                <li><a href='registrazione.php'>REGISTRA</a> <!-- Link alla pagina di registrazione --></li>
                <li><a href='login.php'>LOGIN</a><!-- Link alla pagina di login --></li>
                <?php
                    if ($utente!=="non loggato")
                        echo "<li><a href='logout.php' onclick='return confermaLogout()'>LOGOUT</a></li>"; // mostro l'opzione per fare il logout sono se è stato fatto il login
                ?>
            </ul>
        </nav> <!--Aggiunge una linea orizzontale sotto il menu di navigazione-->

        <!--Commento per un link opzionale alla pagina "CONFERMA", da riattivare se necessario
        <a href='conferma.php'>CONFERMA</a> |  per lo spazio CONFERMA, se dovesse essere necessario riaggiungerlo -->

    </body>
</html>