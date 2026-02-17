
<?php
    include "header.php";
    if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
        session_start();

    // Recupera i dati inviati dal form
    // $ids contiene gli ID dei materiali selezionati
    // $quantita contiene le quantità selezionate per ogni materiale
    // $costo_totale rappresenta il costo totale dell'acquisto
    $ids = isset($_POST['id']) ? $_POST['id'] : [];
    $quantita = isset($_POST['quantita']) ? $_POST['quantita'] : [];
    $costo_totale = isset($_POST['costo_totale']) ? $_POST['costo_totale'] : 0;

    // Connessione al database
    // Crea una connessione al database per aggiornare i dati relativi ai materiali e al credito dell'utente
    $conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
    if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

    // Avvia una transazione
    // La transazione garantisce che tutte le operazioni vengano eseguite correttamente o annullate in caso di errore
    $conn->begin_transaction();

    foreach ($ids as $id) {
        $qta = $quantita[$id]; // Recupera la quantità selezionata per il materiale corrente

        // Aggiorna la quantità del materiale
        // Riduce la quantità disponibile del materiale in base alla quantità acquistata
        $stmt1 = $conn->prepare("UPDATE MATERIALI SET QUANTITA = QUANTITA - ? WHERE ID = ?");
        $stmt1->bind_param("ii", $qta, $id); // Associa i parametri alla query
        $stmt1->execute();
    }

    // Aggiorna il credito dell'utente
    // Riduce il credito dell'utente in base al costo totale dell'acquisto
    $stmt2 = $conn->prepare("UPDATE DATI_ARTIGIANI SET CREDIT = CREDIT - ? WHERE ID_UTENTE = ?");
    $stmt2->bind_param("di", $costo_totale, $_SESSION['id']); // Associa i parametri alla query
    $stmt2->execute();

    // Conferma la transazione
    // Conferma tutte le modifiche al database
    $conn->commit();

    // Aggiorna il credito nella sessione
    // Aggiorna il valore del credito dell'utente nella sessione per riflettere il nuovo credito
    $_SESSION['credito'] -= $costo_totale;

    //Contenuto principale della pagina fine.php che contiene il messaggio di successo per aver completato l'acquisto e permette di tornare alla pagina di acquisto o al menù principale
    echo "<main>"; 
    echo "<h2>Conclusione</h2>";
    echo "<p class='successo'>Acquisto completato con successo!</p>";
    echo "<form name='formTornaPaginaAcquisto' action='domanda.php'>";
    echo "<input type='submit' value='Torna alla pagina di acquisto'>";
    echo "</form>";
    echo "<form name='formTornaHome' action='home.php'>";
    echo "<input type='submit' value='Torna alla pagina principale'>";
    echo "</form>";
    echo "</main>";

    // Chiude gli statement e la connessione al database
    $stmt1->close();
    $stmt2->close();
    $conn->close();

    include "footer.php";
?>