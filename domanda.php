<?php
    // Include l'intestazione della pagina e avvia la sessione
    include "header.php";
    if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
        session_start();

    // Controlla se l'utente è loggato e ha i permessi per accedere alla pagina
    if (!isset($_SESSION['id']) || !isset($_SESSION['utente']) || !isset($_SESSION['credito'])) { // fare un controllo successivo quando faccio login con tipo utente uguale ad aziedn
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Devi essere loggato come artigiano per accedere al contenuto di questa pagina.');
                window.location.href = 'login.php';
            });
            </script>";
        include "footer.php";
        exit;
    }

    // Connessione al database MySQL
    $conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
    if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

    // Controlla se l'utente è un artigiano
    $stmt = $conn->prepare("SELECT ARTIGIANO FROM UTENTI WHERE ID = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($isArtigiano);
    $stmt->fetch();
    $stmt->close();

    if (!$isArtigiano) {
        echo "<p>Solo gli artigiani possono accedere a questa pagina.</p>";
        include "footer.php";
        exit;
    }

    // Filtro per la data 
    // Se il parametro 'data' è presente nella query string, lo utilizza per filtrare i materiali
    $filter_date = isset($_GET['data']) ? $_GET['data'] : "";
    $materiali = [];

    // Prepara la query SQL per recuperare i materiali
    // Se è presente un filtro per la data, aggiunge la condizione alla query
    if ($filter_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
        $stmt = $conn->prepare("SELECT ID, NOME, DESCRIZIONE, DATA, QUANTITA, COSTO FROM MATERIALI WHERE DATA >= ?");
        $stmt->bind_param("s", $filter_date); // Associa il filtro della data alla query
    } else {
        $stmt = $conn->prepare("SELECT ID, NOME, DESCRIZIONE, DATA, QUANTITA, COSTO FROM MATERIALI");
    }

    // Esegue la query e associa i risultati alle variabili
    $stmt->execute();
    $stmt->bind_result($id, $nome, $descr, $data, $qta, $costo);

    // Recupera i risultati della query e li memorizza nell'array $materiali
    while ($stmt->fetch()) {
        $materiali[] = ["id"=>$id, "nome"=>$nome, "descr"=>$descr, "data"=>$data, "qta"=>$qta, "costo"=>$costo];
    }
    $stmt->close();

    // Variabile per memorizzare i messaggi di feedback
    $feedbackSuccess = "";
    $feedbackError = "";

    // Gestione dell'acquisto di un materiale
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['buy'])) {
        // Recupera i dati inviati dal form
        $id_mat = $_POST['id'];
        $qta_richiesta = $_POST['quantita'];
        $id_utente = $_SESSION['id'];

        // Preleva le informazioni sul materiale selezionato
        $stmt = $conn->prepare("SELECT QUANTITA, COSTO FROM MATERIALI WHERE ID = ?");
        $stmt->bind_param("i", $id_mat); // Associa l'ID del materiale alla query
        $stmt->execute();
        $stmt->bind_result($qta_disp, $prezzo);

        if ($stmt->fetch()) {
            // Calcola il costo totale dell'acquisto
            $totale = $qta_richiesta * $prezzo;

            // Controlla se la quantità richiesta è disponibile e se l'utente ha credito sufficiente
            if ($qta_disp >= $qta_richiesta && $_SESSION['credito'] >= $totale) {
                $stmt->close();
                $conn->begin_transaction(); // Avvia una transazione per garantire la coerenza dei dati

                // Aggiorna la quantità del materiale nel database
                $stmt1 = $conn->prepare("UPDATE MATERIALI SET QUANTITA = QUANTITA - ? WHERE ID = ?");
                $stmt1->bind_param("ii", $qta_richiesta, $id_mat);
                $stmt1->execute();

                // Aggiorna il credito dell'utente nel database
                $stmt2 = $conn->prepare("UPDATE DATI_ARTIGIANI SET CREDIT = CREDIT - ? WHERE ID_UTENTE = ?");
                $stmt2->bind_param("di", $totale, $id_utente);
                $stmt2->execute();

                $conn->commit(); // Conferma la transazione
                $_SESSION['credito'] -= $totale; // Aggiorna il credito nella sessione
                $feedbackSuccess = "Acquisto effettuato con successo.";
            } else {
                $feedbackError = "Quantità non disponibile o credito insufficiente.";
            }
        } else {
            $feedback = "Materiale non trovato.";
        }
        $stmt->close();
    }
?>
<!---->
<main>
    <h2>Domanda</h2>
    <p>Benvenuto nella sezione Domanda. Qui puoi visualizzare i materiali di scarto disponibili per l'acquisto, filtrare i risultati in base alla data di inserimento e procedere all'acquisto dei materiali che ti interessano. Assicurati di avere credito sufficiente per completare l'acquisto e di selezionare solo le quantità che desideri acquistare.</p>

    <!-- Mostra messaggi di feedback -->
    <?php if ($feedbackSuccess) echo "<p class='successo'>$feedbackSuccess</p>"; ?>
    <?php if ($feedbackError) echo "<p class='errori'>$feedbackError</p>"; ?>

    <!-- Form per filtrare i materiali in base alla data -->
    <form method="get">
        <fieldset>
            <legend>Filtro di ricerca</legend>
            <label>Visualizza materiali dal: <input type="date" name="data" value="<?= htmlspecialchars($filter_date) ?>"></label>
            <input type="submit" value="Filtra">
        </fieldset>
    </form>

    <!-- Form principale per selezionare i materiali -->
    <?php
    // Recupera le quantità selezionate, se presenti
    $quantita_preselezionata = isset($_POST['quantita']) ? $_POST['quantita'] : [];
    ?>
    <form method="post" action="conferma.php">
        <fieldset>
            <legend>Materiali disponibili</legend>
            <?php if (empty($materiali)): ?>
                <p>Nessun materiale trovato.</p>
            <?php else: ?>
                <!-- Tabella per visualizzare i materiali trovati -->
                <table class="materialiDomanda">
                    <tr>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th>Data</th>
                        <th>Quantità disponibile</th>
                        <th>Costo per pezzo (€)</th>
                        <th>Quantità da acquistare</th>
                    </tr>
                    <?php foreach ($materiali as $mat): ?>
                    <tr>
                        <td><?= htmlspecialchars($mat['nome']) ?></td>
                        <td><?= htmlspecialchars($mat['descr']) ?></td>
                        <td><?= $mat['data'] ?></td>
                        <td><?= $mat['qta'] ?></td>
                        <td><?= $mat['costo'] ?></td>
                        <td>
                            <!-- Campo per selezionare la quantità da acquistare -->
                            <input type="number" name="quantita[<?= $mat['id'] ?>]" id="quantita_<?= $mat['id'] ?>" min="0" max="<?= $mat['qta'] ?>" value="<?= isset($quantita_preselezionata[$mat['id']]) ? $quantita_preselezionata[$mat['id']] : 0 ?>">
                            <!-- Pulsante Annulla per azzerare la quantità selezionata -->
                            <input type="button" value="Deseleziona Prodotto" onclick="document.getElementById('quantita_<?= $mat['id'] ?>').value = 0;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <br>
                <!-- Pulsanti per annullare o procedere all'acquisto -->
                <input type="reset" value="Annulla">
                <input type="submit" value="Acquista">
            <?php endif; ?>
        </fieldset>
    </form>
</main>

<!-- Include il footer della pagina -->
<?php include "footer.php"; ?>