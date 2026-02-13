<?php
// Include l'intestazione della pagina
include "header.php";

// Inizializza variabili per gestire errori e dati ricordati dai cookie
$error = "";
if (isset($_COOKIE["utente"]))
    $rememberedUser = $_COOKIE["utente"];
else
    $rememberedUser = "";
if (isset($_COOKIE["pwd"]))
    $rememberedPwd = $_COOKIE["pwd"];
else
    $rememberedPwd = "";
if (isset($_COOKIE["tipoUtente"]))
    $rememberType = $_COOKIE["tipoUtente"];
else
    $rememberedType = "";

// Controlla se il modulo è stato inviato
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recupera i dati inviati dal modulo
    $utente = $_POST['user']; // Username
    $password = $_POST['pwd'];   // Password
    $remember = isset($_POST['remember']); // Opzione "Ricordami"

    // Connessione al database MySQL con utente lettore
    $conn = new mysqli("localhost", "lettore", "P@ssw0rd!", "eco_scambio");

    // Controlla se la connessione al database è riuscita
    if ($conn->connect_error) {
        die("Errore connessione: " . $conn->connect_error);
    }

    // Prepara una query SQL per verificare le credenziali dell'utente
    $stmt = $conn->prepare("SELECT ID, ARTIGIANO FROM UTENTI WHERE NICK = ? AND PASSWORD = ?");
    $stmt->bind_param("ss", $utente, $password); // Associa i parametri alla query
    $stmt->execute();
    $stmt->store_result(); // Memorizza i risultati della query

    // Controlla se è stato trovato un utente con le credenziali fornite
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $artigiano); // Associa i risultati alle variabili
        $stmt->fetch(); // Recupera i dati dell'utente

        // Avvia una sessione per memorizzare i dati dell'utente
        session_start();
        $_SESSION['utente'] = $utente; // Salva l'username nella sessione
        $_SESSION['id'] = $id;     // Salva l'ID utente nella sessione
        $artigiano_VAR = "Azienda";
        $_SESSION['tipoUtente'] = $artigiano_VAR;
       
        // Se l'utente è un artigiano, recupera il credito associato
        if ($artigiano) {
            $creditQuery = $conn->prepare("SELECT CREDIT FROM DATI_ARTIGIANI WHERE ID_UTENTE = ?");
            $creditQuery->bind_param("i", $id); // Associa l'ID utente alla query
            $creditQuery->execute();
            $creditQuery->bind_result($credito); // Associa il risultato alla variabile
            $creditQuery->fetch(); // Recupera il credito
            $_SESSION['credito'] = $credito; // Salva il credito nella sessione
            $artigiano_VAR =  "Artigiano";
            $_SESSION['tipoUtente'] = $artigiano_VAR;
            $creditQuery->close(); // Chiude lo statement
        }


        // Se l'opzione "Ricordami" è selezionata, salva i dati nei cookie
        if ($remember) {
            setcookie("utente", $utente, time() + 72 * 3600); // Salva l'username per 72 ore
            setcookie("pwd", $password, time() + 72 * 3600);   // Salva la password per 72 ore
            setcookie("tipoUtente", $artigiano_VAR,  time() + 72 * 3600);
        }

        // Chiude lo statement e la connessione al database
        $stmt->close();
        $conn->close();

        // Reindirizza l'utente alla pagina appropriata in base al tipo di utente
        if ($artigiano) {
            header("Location: domanda.php"); // Reindirizza alla pagina domanda.php per artigiani
            exit; 
        } else {
            header("Location: offerta.php"); // Reindirizza alla pagina offerta.php per aziende
            exit;
        }
    } else {
        // Se le credenziali non sono valide, mostra un messaggio di errore
        $error = "Credenziali non valide.";
        $stmt->close();
        $conn->close();
    }
}
?>
<main>
    <h2>Login</h2>

    <!-- Mostra un messaggio di errore, se presente -->
    <?php if ($error) echo "<p class='errori'>$error</p>"; ?>

    <!-- Form di login -->
    <form method="post" id="login">
        <label for="user">Username:</label>
        <!-- Campo per l'username, precompilato se salvato nei cookie -->
        <input type="text" id="user" name="user" value="<?= htmlspecialchars($rememberedUser) ?>"><br>
        <!-- Trasforma i caratteri speciali (come < o >) in entità HTML (come &lt; e &gt;). 
         Questo impedisce attacchi di tipo XSS (Cross-Site Scripting), evitando che un utente malintenzionato 
         possa inserire del codice JavaScript maligno nel campo e farlo eseguire dal browser. -->

        <label for="pwd">Password:</label>
        <!-- Campo per la password, precompilato se salvata nei cookie -->
        <input type="password" id="pwd" name="pwd" value="<?= htmlspecialchars($rememberedPwd) ?>"><br>

        <!-- Checkbox per l'opzione "Ricordami" -->
        <label><input type="checkbox" name="remember">Rimani collegato</label><br>

        <!-- Pulsanti per inviare o resettare il modulo -->
        <input type="submit" id="bottoneInvia" value="INVIA" >
        <input type="reset" id="bottoneCancella" value="CANCELLA" >
    </form>
</main>

<!-- Include il footer della pagina -->
<?php include "footer.php"; ?>