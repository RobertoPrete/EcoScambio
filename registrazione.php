<?php
// Include l'intestazione della pagina
include "header.php";

// Inizializza le variabili per gestire errori e messaggi di successo
$errors = [];
$success = "";

// Controlla se il modulo è stato inviato
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Connessione al database MySQL
    $conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
    if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

    // Recupera i dati inviati dal modulo
    $type = $_POST['type']; // Tipo di utente (azienda o artigiano)
    $username = $_POST['nick']; // Username
    $password = $_POST['password']; // Password

    // Validazione di username e password
    if (!preg_match("/^[a-zA-Z][a-zA-Z0-9_-]{3,9}$/", $username))
        $errors[] = "Username non valido. Username deve essere una stringa lunga da 4 a 10 caratteri, con solo lettere, numeri e - o _ come valori ammessi e deve cominciare con un carattere alfabetico.";
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.;+=])[A-Za-z\d.;+=]{8,16}$/", $password))
        $errors[] = "Password non valida. Deve essere una stringa lunga da 8 a 16 caratteri, che può contenere lettere, numeri e caratteri speciali, e deve contenere almeno 1 lettera maiuscola, 1 lettera minuscola, 1 numero e 1 caratteri speciale tra i seguenti (.;+=).";

    // Se non ci sono errori, inserisce l'utente nel database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO UTENTI (NICK, PASSWORD, ARTIGIANO) VALUES (?, ?, ?)"); //Utile per prevenire SQL injection
        if ($type === "artigiano")
            $isArtigiano = 1;
        else 
            $isArtigiano = 0;
        // $isArtigiano = $type === "artigiano" ? 1 : 0; // Determina se l'utente è un artigiano
        $stmt->bind_param("ssi", $username, $password, $isArtigiano); // Associa i parametri alla query

        if ($stmt->execute()) {// Esegue la query e verifica se è stata eseguita con successo.
            $userId = $stmt->insert_id; // Recupera l'ID dell'utente appena inserito

            // Se l'utente è un'azienda, inserisce i dati aziendali
            if ($type === "azienda") {
                $ragioneSociale = $_POST['ragione']; // Ragione sociale
                $indirizzoAziendale = $_POST['address2']; // Indirizzo aziendale

                // Validazione dei dati aziendali
                if (preg_match("/^[A-Z][A-Za-z0-9 &]{0,29}$/", $ragioneSociale) &&
                    preg_match("/^(Via|Corso) [a-zA-Z ]+ \d{1,3}, [A-Za-z ]+$/", $indirizzoAziendale)) {
                    $stmtAzienda = $conn->prepare("INSERT INTO DATI_AZIENDE (ID_UTENTE, RAGIONE, ADDRESS2) VALUES (?, ?, ?)");
                    $stmtAzienda->bind_param("iss", $userId, $ragioneSociale, $indirizzoAziendale);
                    $stmtAzienda->execute();
                    $stmtAzienda->close();
                    $success = "Registrazione azienda completata.";
                }else if (!preg_match("/^[A-Z][A-Za-z0-9 &]{0,29}$/", $ragioneSociale)) {
                    $errors[] = "Ragione sociale non valida. Deve essere una stringa di massimo 30 caratteri, con lettere numeri ed i caratteri “&” e spazio come caratteri accettabili e deve necessariamente iniziare con una lettera maiuscola.";
                }else if (!preg_match("/^(Via|Corso) [a-zA-Z ]+ \d{1,3}, [A-Za-z ]+$/", $indirizzoAziendale)) {
                    $errors[] = "Indirizzo aziendale non valido. Deve essere nella forma “Via/Corso nome numeroCivico, Città”, dove nome può contenere caratteri alfabetici e spazi, numeroCivico deve essere un numero naturale composto da 1 a 3 cifre decimali, Città il nome di una città (o presunta tale).";
                }else {
                    $errors[] = "Dati azienda non validi. La ragione sociale deve essere una stringa di massimo 30 caratteri, con lettere numeri ed i caratteri “&” e spazio come caratteri accettabili e deve necessariamente iniziare con una lettera maiuscola. Indirizzo deve essere nella forma “Via/Corso nome numeroCivico, Città”, dove nome può contenere caratteri alfabetici e spazi, numeroCivico deve essere un numero naturale composto da 1 a 3 cifre decimali, Città il nome di una città (o presunta tale).";
                }
            } 
            // Se l'utente è un artigiano, inserisce i dati personali
            else {
                $name = $_POST['name']; // Nome
                $surname = $_POST['surname']; // Cognome
                $birthdate = $_POST['birthdate']; // Data di nascita
                $credit = $_POST['credit']; // Credito iniziale
                $address = $_POST['address']; // Indirizzo

                // Validazione dei dati personali
                if (preg_match("/^[A-Za-z ]{4,14}$/", $name) &&
                    preg_match("/^[A-Za-z' ]{4,16}$/", $surname) &&
                    preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", $birthdate) &&
                    preg_match("/^\d+(\.\d{1,2})?$/", $credit) &&
                    ((int)($credit * 100) % 5 === 0)) {
                    $stmtArtigiano = $conn->prepare("INSERT INTO DATI_ARTIGIANI (ID_UTENTE, NAME, SURNAME, BIRTHDATE, CREDIT, ADDRESS) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtArtigiano->bind_param("isssds", $userId, $name, $surname, $birthdate, $credit, $address);
                    $stmtArtigiano->execute();
                    $stmtArtigiano->close();
                    $success = "Registrazione artigiano completata.";
                }else if (!preg_match("/^[A-Za-z ]{4,14}$/", $name)) {
                    $errors[] = "Nome non valido. Deve essere una stringa di minimo 4 e massimo 14 caratteri, con solo lettere ed il carattere spazio come caratteri accettabili.";
                }else if (!preg_match("/^[A-Za-z' ]{4,16}$/", $surname)) {
                    $errors[] = "Cognome non valido. Deve essere una stringa di minimo 4 e massimo 16 caratteri, con solo lettere ed i caratteri spazio o “’” (apostrofo) come caratteri accettabili.";
                }else if (!preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", $birthdate)) {
                    $errors[] = "Data di nascita non valida. Deve essere nella forma “aaaa-mm-gg” (dove il valore 0 in posizione più significativa nel mese e nel giorno può eventualmente essere omesso).";
                }else if (!preg_match("/^\d+(\.\d{1,2})?$/", $credit) || ((int)($credit * 100) % 5 !== 0)) {
                    $errors[] = "Credito non valido. Deve essere un numero che rappresenta il credito in euro, caricato dagli utenti, con precisione dei centesimi (ma che nei centesimi deve considerare variazioni da 5 unità per volta).";
                }else {
                    $errors[] = "Dati artigiano non validi. Nome deve essere una stringa di minimo 4 e massimo 14 caratteri, con solo lettere ed il carattere spazio come caratteri accettabili. Cognome deve essere una stringa di minimo 4 e massimo 16 caratteri, con solo lettere ed i caratteri spazio o “’” (apostrofo) come caratteri accettabili. Data di nascita deve essere nella forma “aaaa-mm-gg” (dove il valore 0 in posizione più significativa nel mese e nel giorno può eventualmente essere omesso), credito un numero che rappresenta il credito in euro, caricato dagli utenti, con precisione dei centesimi (ma che nei centesimi deve considerare variazioni da 5 unità per volta). ";
                }
            }
        } else {
            $errors[] = "Errore! Utente già esistente o errore di connessione al database.";
        }

        // Chiude lo statement e la connessione
        $stmt->close();
        $conn->close();
    }
}
?>
<main>
    <h2>Pagina di registrazione</h2>

    <!-- Mostra eventuali errori -->
    <?php foreach ($errors as $err) echo "<p class='errori'>$err</p>"; ?>

    <!-- Mostra messaggio di successo -->
    <?php if ($success) echo "<p class='successo'>$success</p>"; ?>

    <!-- Form di registrazione -->
    <form name="form_registrazione" id="form_registrazione" method="post">
        <fieldset> 
            <legend>Utente</legend>
            <label for="type">Seleziona tipo di utente</label>
            <select name="type" id="type" required>
                <option value="" disabled selected>-- Seleziona --</option>
                <option value="azienda">Azienda</option>
                <option value="artigiano">Artigiano</option>
            </select>

            <div id="azienda" style="display:none;">
                <label>Ragione Sociale: <input type="text" name="ragione"></label><br>
                <label>Indirizzo (Via/Corso ...): <input type="text" name="address2"></label><br>
                <label>Username: <input type="text" name="nick" required></label><br>
                <label>Password: <input type="password" name="password" required></label><br>
            </div>

            <div id="artigiano" style="display:none;">
                <label>Nome: <input type="text" name="name"></label><br>
                <label>Cognome: <input type="text" name="surname"></label><br>
                <label>Data di nascita: <input type="date" name="birthdate"></label><br>
                <label>Credito iniziale: <input type="text" name="credit"></label><br>
                <label>Indirizzo: <input type="text" name="address"></label><br>
                <label>Username: <input type="text" name="nick" required></label><br>
                <label>Password: <input type="password" name="password" required></label><br>
            </div>

        <input type="submit" value="REGISTRA">
    </form>

    <!-- Script per mostrare i campi in base al tipo di utente selezionato -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("type").addEventListener("change", function(){
                document.getElementById("azienda").style.display = this.value === "azienda" ? "block" : "none";
                document.getElementById("artigiano").style.display = this.value === "artigiano" ? "block" : "none";
            })
        })
    </script>
</main>

<!-- Include il footer della pagina -->
<?php include 'footer.php'; ?>