<?php
    // Include l'intestazione della pagina
    include "header.php";

    if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
        session_start();


    if (isset($_SESSION['id']) && isset($_SESSION['utente'])) { // Controlla se si è all'interno di un account mamostra un avviso all'utente loggato
        echo "<p>Ehi! Ti sei già registrato e loggato come {$_SESSION['utente']}. Se vuoi registrarti con un altro account, esegui prima il <span><a href='logout.php' onclick='return confermaLogout()'>LOGOUT</a></span>.</p>";
        include "footer.php";
        exit;
    }

    // Inizializza le variabili per gestire errori e messaggi di successo
    $errors = [];
    $success = "";

    // Controlla se il modulo è stato inviato
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Connessione al database MySQL
        $conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
        if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

        // Recupera i dati inviati dal modulo.
        $type = $_POST['type']; // Tipo di utente (azienda o artigiano)
        $username = $_POST['nick']; // Username
        $password = $_POST['password']; // Password

        // Validazione di username e password tramite le regular expression
        if (!preg_match("/^[a-zA-Z][a-zA-Z0-9_-]{3,9}$/", $username))
            $errors[] = "Username non valido. Username deve essere una stringa lunga da 4 a 10 caratteri, con solo lettere, numeri e - o _ come valori ammessi e deve cominciare con un carattere alfabetico.";
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.;+=])[A-Za-z\d.;+=]{8,16}$/", $password))
            $errors[] = "Password non valida. Deve essere una stringa lunga da 8 a 16 caratteri, che può contenere lettere, numeri e caratteri speciali, e deve contenere almeno 1 lettera maiuscola, 1 lettera minuscola, 1 numero e 1 caratteri speciale tra i seguenti (.;+=).";

        // Validazione dati specifici di azienda o artigiano. Qualora i dati non rispettino le caratteristiche delle regular expression viene memorizzato un messaggio di errore nell'array $errors
        // Se l'utente è un'azienda recupera i dati aziendali dal form (dai campi relativi alle aziende)
        if ($type === "azienda") {
            $ragioneSociale = $_POST['ragione']; // Ragione sociale
            $indirizzoAziendale = $_POST['address2']; // Indirizzo aziendale

            //Validazione dati aziendali
            if (!preg_match("/^[A-Z][A-Za-z0-9 &]{0,29}$/", $ragioneSociale))
                $errors[] = "Ragione sociale non valida. Deve essere una stringa di massimo 30 caratteri, con lettere numeri ed i caratteri “&” e spazio come caratteri accettabili e deve necessariamente iniziare con una lettera maiuscola.";
            if (!preg_match("/^(Via|Corso) [a-zA-Z ]+ \d{1,3}, [A-Za-z ]+$/", $indirizzoAziendale))
                $errors[] = "Indirizzo aziendale non valido. Deve essere nella forma “Via/Corso nome numeroCivico, Città”, dove nome può contenere caratteri alfabetici e spazi, numeroCivico deve essere un numero naturale composto da 1 a 3 cifre decimali, Città il nome di una città (o presunta tale).";
        } else{// Se l'utente è un artigiano recupera i dati degli artigiani dal form (dai campi relativi agli artigiani)
            $name = $_POST['name']; // Nome
            $surname = $_POST['surname']; // Cognome
            $birthdate = $_POST['birthdate']; // Data di nascita
            $credit = $_POST['credit']; // Credito iniziale
            $address = $_POST['address']; // Indirizzo

            // Validazione dati artigiano
            if (!preg_match("/^[A-Za-z ]{4,14}$/", $name)) 
                $errors[] = "Nome non valido. Deve essere una stringa di minimo 4 e massimo 14 caratteri, con solo lettere ed il carattere spazio come caratteri accettabili."; 
            if (!preg_match("/^[A-Za-z' ]{4,16}$/", $surname)) 
                $errors[] = "Cognome non valido. Deve essere una stringa di minimo 4 e massimo 16 caratteri, con solo lettere ed i caratteri spazio o “’” (apostrofo) come caratteri accettabili.";
            if (!preg_match("/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/", $birthdate))
                $errors[] = "Data di nascita non valida. Deve essere nella forma “aaaa-mm-gg”.";
            if (!preg_match("/^\d+(\.\d{1,2})?$/", $credit))
                $errors[] = "Credito non valido. Deve essere un numero che rappresenta il credito in euro, caricato dagli utenti, con precisione dei centesimi (ma che nei centesimi deve considerare variazioni da 5 unità per volta).";
            else
                if ((int)($credit * 100) % 5 === 0)
                    $errors[]="Il credito deve avere centesimi multipli di 5.";               
            if (!preg_match("/^(Via|Corso) [a-zA-Z ]+ \d{1,3}, [A-Za-z ]+$/", $address)) 
                $errors[] = "Indirizzo non valido. Deve essere nella forma “Via/Corso nome numeroCivico, Città”, dove nome può contenere caratteri alfabetici e spazi, numeroCivico deve essere un numero naturale composto da 1 a 3 cifre decimali, Città il nome di una città (o presunta tale).";
        }

        //Inserimento dei dati nel database solo se non ci sono errori
        if (empty($errors)) {
            if ($type === "artigiano")
                $isArtigiano = 1;
            else 
                $isArtigiano = 0;

            //Inserimento dati utente
            $stmt = $conn->prepare("INSERT INTO UTENTI (NICK, PASSWORD, ARTIGIANO) VALUES (?, ?, ?)"); //Prepared Statement utile per prevenire SQL injection
            $stmt->bind_param("ssi", $username, $password, $isArtigiano); // Associa i parametri alla query

            if ($stmt->execute()) {// Esegue la query e verifica se è stata eseguita con successo.
                $userId = $stmt->insert_id; // Recupera l'ID dell'utente appena inserito

                //Inserimento dati specifici (di azienda o artigiano)
                if ($type === "azienda") {
                    //Inserimento dati specifici azienda
                    $stmtAzienda = $conn->prepare("INSERT INTO DATI_AZIENDE (ID_UTENTE, RAGIONE, ADDRESS2) VALUES (?, ?, ?)");
                    $stmtAzienda->bind_param("iss", $userId, $ragioneSociale, $indirizzoAziendale);
                    $stmtAzienda->execute();
                    $stmtAzienda->close();

                    $success = "Registrazione azienda completata."; 
                }else{
                    //Inserimento dati specifici artigiano
                    $stmtArtigiano = $conn->prepare("INSERT INTO DATI_ARTIGIANI (ID_UTENTE, NAME, SURNAME, BIRTHDATE, CREDIT, ADDRESS) VALUES (?, ?, ?, ?, ?, ?)");
                    $birthdate_sql = new DateTime($birthdate); // Crea un oggetto DateTime per formattare la data correttamente
                    $stmtArtigiano->bind_param("isssds", $userId, $name, $surname, $birthdate_sql->format('Y-m-d'), $credit, $address);
                    $stmtArtigiano->execute();
                    $stmtArtigiano->close();

                    $success = "Registrazione artigiano completata.";  
                }
            }else {
                $errors[] = "Errore! Utente già esistente o errore di connessione al database.";
            }

            //Chiusura statement e connessione
            $stmt->close();
            $conn->close();
        }
    }
?>
<main> <!-- Contenuto principale della pagina di registrazione che presenta eventuali messaggi di errore o successo di inserimento dei dati. Presenta inoltre il form di registrazione-->
    <h2>Pagina di registrazione</h2>

    <!-- Mostra eventuali errori -->
    <?php foreach ($errors as $err) echo "<p class='errori'>$err</p>"; ?>

    <!-- Mostra messaggio di successo -->
    <?php if ($success) echo "<p class='successo'>$success</p>"; ?>

    <!-- Form di registrazione -->
    <form name="form_registrazione" id="form_registrazione" method="post"> <!-- Importante uso del metodo POST e non GET per non mostrare in chiaro nell'URL dati che potrebbero essere sensibili o di grande volume-->
        <fieldset> <!-- raggruppamento per migliorare accessibilità e struttura visiva-->
            <legend>Utente</legend> <!-- legenda del raggruppamento-->

            <!-- campo per selezionare che tipo di utente si vuole registrare-->
            <label for="type">Seleziona tipo di utente</label>
            <select name="type" id="type" required>
                <option value="" disabled selected>-- Seleziona --</option>
                <option value="azienda">Azienda</option>
                <option value="artigiano">Artigiano</option>
            </select>
            
            <!-- campi di regsitrazione per un'azienda-->
            <div class="campiRegistrazione" id="azienda"> 
                <label>Ragione Sociale: <input type="text" name="ragione"></label><br>
                <label>Indirizzo (Via/Corso ...): <input type="text" name="address2"></label><br>
            </div>

            <!-- campi di registrazione per un artigiano-->
            <div class="campiRegistrazione" id="artigiano"> 
                <label>Nome: <input type="text" name="name"></label><br>
                <label>Cognome: <input type="text" name="surname"></label><br>
                <label>Data di nascita (aaaa-mm-gg): <input type="text" name="birthdate"></label><br>
                <label>Credito iniziale: <input type="text" name="credit"></label><br>
                <label>Indirizzo: <input type="text" name="address"></label><br>
            </div>

            <!-- campi in comune per azienda e artigiano, necessari per la registrazione-->
            <div class="campiRegistrazione" id="nickPassword">
                <label>Username: <input type="text" id="nick" name="nick" required></label><br>
                <label>Password: <input type="password" id="password" name="password" required></label><br>
            </div>

            <!--pulsante per confermare l'inserimento dei dati nel database dopo averne verificato la correttezza secondo le logiche di controllo attuate nella parte superiore del codice-->
            <input type="submit" value="REGISTRA">
        </fieldset>
    </form>

    <!-- Script per mostrare i campi di registrazione in base al tipo di utente selezionato -->
    <script>
        document.addEventListener("DOMContentLoaded", function() { // Permette di eseguire il codice all'interno solo quando il documento HTML è stato completamente analizzato e tutti gli script differiti sono stati scaricati ed eseguiti.
            document.getElementById("type").addEventListener("change", function(){
                document.getElementById("azienda").style.display = this.value === "azienda" ? "block" : "none";
                document.getElementById("artigiano").style.display = this.value === "artigiano" ? "block" : "none";
                document.getElementById("nickPassword").style.display = "block";
            })
        });
    </script>
</main>

<!-- Include il footer della pagina -->
<?php include 'footer.php'; ?>