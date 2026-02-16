<?php
    // Include l'intestazione della pagina
    include "header.php";

    if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
        session_start();

    // Controlla se l'utente è loggato se non lo è mostra un alert e reinderizza alla pagina di login
    if (!isset($_SESSION['id']) || !isset($_SESSION['utente'])) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('Attenzione! Questa pagina è riservata alle aziende registrate. Inserisci le credenziali prima di procedere a modificare o inserire nuove offerte di materiali di scarto.');
            window.location.href = 'login.php';
        });
        </script>";
        include "footer.php";
        exit;
    }

    // Connessione al database MySQL
    $conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
    if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

    // Controlla se l'utente è un'azienda recuperando i dati dal database secondo l'id memorizzato nella sessione
    $stmt = $conn->prepare("SELECT ARTIGIANO FROM UTENTI WHERE ID = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($isArtigiano);
    $stmt->fetch();
    $stmt->close();

    if ($isArtigiano) { //se l'utente è un artigiano mostra un messaggio all'utente senza mostrare il contenuto della pagina
        echo "<p>Solo le aziende possono accedere a questa pagina.</p>";
        include "footer.php";
        exit;
    }

    // Variabili per gestire messaggi di successo ed errori
    $success = "";
    $errors = [];

    // Gestione dell'inserimento di un nuovo materiale
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new'])) {
        // Recupera i dati dal form
        $nome = $_POST['nome'];
        $descrizione = $_POST['descrizione'];
        $data = $_POST['data']; 
        $quantita = $_POST['quantita'];
        $costo = $_POST['costo'];

        // Validazione dei dati ed eventuale memorizzazione di messaggi di errore nell'array $errors
        if (!preg_match('/^[A-Za-z0-9 ]{10,40}$/', $nome)) $errors[] = "Nome non valido. Nome deve essere una stringa di minimo 10 caratteri e massimo 40 caratteri, con solo lettere, numeri e spazi.";
        if (strlen($descrizione) > 250) $errors[] = "Descrizione troppo lunga.";
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $data)) $errors[] = "Data non valida. Inserire la data nel formato aaaa-mm-gg.";
        if (!filter_var($quantita, FILTER_VALIDATE_INT)) $errors[] = "Quantità non valida. Quantità deve essere un numero intero.";
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $costo) || ((int)($costo * 100) % 5 != 0)) $errors[] = "Costo non valido. Costo deve essere espressocon la precisione dei centesimi (ma come valori ammissibili nei centesimi sono ammissibili solo multipli di 5)";

        // Inserisce il materiale nel database se non ci sono errori
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO MATERIALI (NOME, DESCRIZIONE, DATA, QUANTITA, COSTO, ID_UTENTE) VALUES (?, ?, ?, ?, ?, ?)");
            $data_sql = new DateTime($data); // Crea un oggetto DateTime per formattare la data correttamente
            $stmt->bind_param("sssidi", $nome, $descrizione, $data_sql->format('Y-m-d'), $quantita, $costo, $_SESSION['id']);
            $stmt->execute();
            $stmt->close();
            $success = "Materiale inserito con successo.";
        }
    }

    // Gestione dell'aggiornamento di un materiale esistente
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
        // Recupera i dati dal form
        $id_mat = $_POST['id'];
        $descrizione = $_POST['descrizione'];
        $quantita = $_POST['quantita'];
        $costo = $_POST['costo'];

        // Validazione dei dati ed eventuale memorizzazione di messaggi di errore nell'array $errors
        if (strlen($descrizione) > 250) $errors[] = "Descrizione troppo lunga.";
        if (!filter_var($quantita, FILTER_VALIDATE_INT)) $errors[] = "Quantità non valida.";
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $costo) || ((int)($costo * 100) % 5 != 0)) $errors[] = "Costo non valido.";

        // Aggiorna il materiale nel database se non ci sono errori
        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE MATERIALI SET DESCRIZIONE = ?, QUANTITA = ?, COSTO = ? WHERE ID = ? AND ID_UTENTE = ?");
            $stmt->bind_param("sddii", $descrizione, $quantita, $costo, $id_mat, $_SESSION['id']);
            $stmt->execute();
            $stmt->close();
            $success = "Materiale aggiornato.";
        }
    }

    // Recupera l'elenco dei materiali dell'azienda interrogando il database e memorizzando i risultati nell'array $materiali
    $materiali = [];
    $res = $conn->prepare("SELECT ID, NOME, DESCRIZIONE, DATA, QUANTITA, COSTO FROM MATERIALI WHERE ID_UTENTE = ?");
    $res->bind_param("i", $_SESSION['id']);
    $res->execute();
    $res->bind_result($id, $nome, $descr, $data, $qta, $costo);
    while ($res->fetch()) {
        $materiali[] = ["id" => $id, "nome" => $nome, "descr" => $descr, "data" => $data, "qta" => $qta, "costo" => $costo];
    }
    $res->close();
?>


<main> <!-- Contenuto principale della pagina offerta.php. Presentazione del contenuto della pagina, eventuali messaggi di errore o successo e due raggruppamenti con all'interno dei form. Un raggruppamento è dedicato a materiali (modificabili nelle caratteristiche) inseriti in precedenza dall'azienda loggata e uno dedicato all'inserimento di nuovo materiale da offrire.-->
    <h2>Offerta</h2>

    <!--Presentazione contenuto della pagina-->
    <p>Benvenuto nella sezione Offerta. Qui puoi inserire i materiali di scarto che la tua azienda desidera vendere oppure aggiornare quelli inseriti in precedenza. Assicurati di fornire informazioni accurate e dettagliate.</p>

    <!-- Mostra eventuali errori -->
    <?php foreach ($errors as $err) echo "<p class='errori'>$err</p>"; ?>

    <!-- Mostra messaggio di successo -->
    <?php if ($success) echo "<p class='successo'>$success</p>"; ?>

    <!--Primo raggruppamento che presenta form con i materiali inseriti in tempi precedenti dall'azienda loggata con la possibilità di modificarne le caratteristiche-->
    <fieldset>
        <legend>Materiali offerti</legend>
        <?php if (empty($materiali)): ?> <!-- mostra un messaggio all'azienda nel caso in cui non ha aggiunto materiali in passato-->
            <p>Non hai ancora inserito materiali. Usa il form sottostante per aggiungere la tua prima offerta.</p>
        <?php else: ?> <!-- caso in cui risultano nel database materiali aggiunti dall'azienda-->
            <!-- Form per aggiornare i materiali esistenti -->
            <?php foreach ($materiali as $mat): ?>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $mat['id'] ?>">
                    <strong><?= htmlspecialchars($mat['nome']) ?></strong> (<?= $mat['data']?>)<br>
                    Descrizione: <input type="text" name="descrizione" value="<?= htmlspecialchars($mat['descr']) ?>" size="40"><br>
                    Quantità: <input type="text" name="quantita" value="<?= $mat['qta'] ?>"><br>
                    Costo (€): <input type="text" name="costo" value="<?= $mat['costo'] ?>"><br>
                    <input type="submit" name="update" value="Aggiorna">
                </form><br>
            <?php endforeach; ?>
        <?php endif; ?>
    </fieldset>  

    <!-- Secondo raggruppamento che presenta un form per inserire un nuovo materiale di scarto da parte dell'azienda loggata-->
    <fieldset>
        <legend>Nuovo materiale</legend>
        <!-- Form per inserire un nuovo materiale -->
        <form method="post">
            <input type="hidden" name="new" value="1">
            Nome: <input type="text" name="nome" required><br>
            Descrizione: <input type="text" name="descrizione" required><br>
            Data (aaaa-mm-gg): <input type="text" name="data" required><br>
            Quantità: <input type="number" name="quantita" required><br>
            Costo (€): <input type="text" name="costo" required><br>
            <input type="submit" value="Inserisci">
        </form>
    </fieldset>
</main>

<!-- Include il footer della pagina -->
<?php include "footer.php"; ?>