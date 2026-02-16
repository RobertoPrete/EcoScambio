<?php
    // Include l'intestazione della pagina
    include "header.php";
    if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
        session_start();

    // Controlla se l'utente è loggato se non lo è mostra un alert e reinderizza alla pagina di login 
    if (!isset($_SESSION['id']) || !isset($_SESSION['utente']) || !isset($_SESSION['credito'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Attenzione! Questa pagina è riservata agli artigiani registrati. Inserisci le credenziali prima di procedere all\'acquisto.');
                window.location.href = 'login.php';
            });
            </script>";
        include "footer.php";
        exit;
    }

    // Connessione al database MySQL
    $conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
    if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

    // Controlla se l'utente è un artigiano interrogando il database
    $stmt = $conn->prepare("SELECT ARTIGIANO FROM UTENTI WHERE ID = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($isArtigiano);
    $stmt->fetch();
    $stmt->close();

    if (!$isArtigiano) { // se l'utente è un' azienda mostra un messaggio all'utente senza mostrare il contenuto della pagina
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

    //Verifica se è stato fornito un filtro per la data e se è valido
    if ($filter_date && preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $filter_date)) {
        $stmt = $conn->prepare("SELECT ID, NOME, DESCRIZIONE, DATA, QUANTITA, COSTO FROM MATERIALI WHERE DATA >= ?");
        $filter_date_sql = new DateTime($filter_date); // Crea un oggetto DateTime per formattare la data correttamente
        $stmt->bind_param("s", $filter_date_sql->format('Y-m-d')); // Associa il filtro della data alla query
    }else if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $filter_date) && $filter_date !== "") {
        // Se è stato fornito un filtro per la data ma non è in un formato valido, mostra un messaggio di errore e reindirizza alla stessa pagina
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Formato data non valido. Inserire la data nel formato aaaa-mm-gg.');
                window.location.href = 'domanda.php';
            });
            </script>";
    }else { //Se non è sato fornito il filtro della data va a recuperare dal database tutti i materiali
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

    // Variabili per memorizzare i messaggi di feedback
    $feedbackSuccess = "";
    $feedbackError = "";

    // Gestione dell'acquisto di un materiale
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['buy'])) {
        // Recupera i dati inviati dal form
        $id_mat = $_POST['id'];
        $qta_richiesta = $_POST['quantita'];
        $id_utente = $_SESSION['id'];

        // Preleva dal database tramite query le informazioni sul materiale selezionato
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

<main><!-- Contenuto principale della pagina domanda.php. Contiene una presentazione della pagina, eventuali messaggi di errore o successo e due raggruppamenti con all'interno dei form. Un raggruppamento è dedicato ad un form che contiene il filtro di ricerca e l'altro contiene un form dove si visualizzano i materiali disponibili che puoi selezionare e acquistare.-->
    <h2>Domanda</h2>

    <!-- Presentazione della pagina-->
    <p>Benvenuto nella sezione Domanda. Qui puoi visualizzare i materiali di scarto disponibili per l'acquisto, filtrare i risultati in base alla data di inserimento e procedere all'acquisto dei materiali che ti interessano. Assicurati di avere credito sufficiente per completare l'acquisto e di selezionare solo le quantità che desideri acquistare.</p>

    <!-- Mostra messaggi di feedback (errore o successo)-->
    <?php if ($feedbackSuccess) echo "<p class='successo'>$feedbackSuccess</p>"; ?>
    <?php if ($feedbackError) echo "<p class='errori'>$feedbackError</p>"; ?>

    <!-- Form per filtrare i materiali in base alla data -->
    <form method="get">
        <fieldset>
            <legend>Filtro di ricerca</legend>
            <label>Visualizza materiali dal (aaaa-mm-gg): <input type="text" name="data" value="<?= htmlspecialchars($filter_date) ?>"></label>
            <input type="submit" value="Filtra">
        </fieldset>
    </form>

    <!-- Form per selezionare i materiali disponibili se presenti altrimenti mostra un messaggio all'utente dicendo che non è stato trovato nessun materiale-->
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
                        <th>Data (aaaa-mm-gg)</th>
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