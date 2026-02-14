<?php
    // Include l'intestazione della pagina, che contiene il titolo, i link CSS e il menu di navigazione
    include "header.php";

    // Connessione al database MySQL con credenziali specifiche
    $conn = new mysqli("localhost", "lettore", "P@ssw0rd!", "eco_scambio");

    // Controlla se la connessione al database è riuscita
    if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

    // Filtro opzionale per il nome del materiale
    // Se il parametro 'nome' è presente nella query string, lo utilizza per filtrare i risultati
    if (isset($_GET['nome'])) {
        $nomeFiltro = "%" . $_GET['nome'] . "%"; // Aggiunge i caratteri jolly per la ricerca parziale
    } else {
        $nomeFiltro = "%"; // Se non è stato fornito un filtro, mostra tutti i materiali
    }

    // Filtro opzionale per la data
    // Se il parametro 'data' è presente nella query string, lo utilizza per filtrare i risultati
    if (isset($_GET['data'])) {
        $dataFiltro = $_GET['data']; // Utilizza la data fornita per filtrare i risultati
    } else {
        $dataFiltro = ""; // Se non è stata fornita una data, non applica il filtro sulla data
    }

    // Array per memorizzare i materiali recuperati dal database
    $materiali = [];

    // Controlla se è stato fornito un filtro per la data e se è in un formato valido (YYYY-MM-DD)
    if ($dataFiltro && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFiltro)) {
        // Prepara una query SQL per selezionare i materiali filtrati per nome e data
        $stmt = $conn->prepare("SELECT NOME, DESCRIZIONE, DATA, QUANTITA, COSTO FROM MATERIALI WHERE NOME LIKE ? AND DATA >= ?");
        $stmt->bind_param("ss", $nomeFiltro, $dataFiltro); // Associa i parametri alla query
    } else {
        // Prepara una query SQL per selezionare i materiali filtrati solo per nome
        $stmt = $conn->prepare("SELECT NOME, DESCRIZIONE, DATA, QUANTITA, COSTO FROM MATERIALI WHERE NOME LIKE ?");
        $stmt->bind_param("s", $nomeFiltro); // Associa il parametro alla query
    }

    // Esegue la query SQL
    $stmt->execute();

    // Associa i risultati della query alle variabili
    $stmt->bind_result($nome, $descrizione, $data, $quantita, $costo);

    // Recupera i risultati della query e li memorizza nell'array $materiali
    while ($stmt->fetch()) {
        $materiali[] = ["nome"=>$nome, "descr"=>$descrizione, "data"=>$data, "qta"=>$quantita, "costo"=>$costo];
    }

    // Chiude lo statement
    $stmt->close();
?>


<main>
    <h2>Lista</h2>

    <!-- Form per filtrare i materiali in base al nome e alla data -->
    <form method="get">
        <fieldset>
            <legend>Filtri</legend>
            <label>Nome materiale: 
                <!-- Campo di input per il filtro sul nome -->
                <input type="text" name="nome" value="<?= isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : '' ?>">
            </label>
            <label>Data di inserimento: 
                <!-- Campo di input per il filtro sulla data -->
                <input type="date" name="data" value="<?= isset($_GET['data']) ? htmlspecialchars($_GET['data']) : '' ?>">
            </label>
            <input type="submit" value="Filtra">
        </fieldset>
    </form>

    <?php if (count($materiali) === 0): ?>
        <!-- Messaggio mostrato se non sono stati trovati materiali -->
        <p>Nessun materiale trovato.</p>
    <?php else: ?>
        <!-- Tabella per visualizzare i materiali trovati -->
         <fieldset>
            <legend>Materiali disponibili</legend>
        <table class="materiali">
            <tr>
                <th>Nome</th>
                <th>Descrizione</th>
                <th>Data</th>
                <?php
                if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
                    session_start();
                if (isset($_SESSION["tipoUtente"]) && $_SESSION["tipoUtente"] === "Artigiano") {
                    echo "<th>Quantità</th><th>Costo unitario €</th>";
                }
                ?>
            </tr>
            <?php foreach ($materiali as $m): ?>
            <tr>
                <!-- Mostra i dettagli di ogni materiale -->
                <td><?= htmlspecialchars($m['nome']) ?></td>
                <td><?= htmlspecialchars($m['descr']) ?></td>
                <td><?= $m['data'] ?></td>
                <?php
                if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
                    session_start();
                if (isset($_SESSION["tipoUtente"]) && $_SESSION["tipoUtente"] === "Artigiano") {
                    echo "<td>" . htmlspecialchars($m['qta']) . "</td><td>" . number_format($m['costo'], 2) . "</td>";
                }
                ?>
            </tr>
            <?php endforeach; ?>
        </table>
        </fieldset>
    <?php endif; ?>
</main>

<?php
// Include il footer della pagina
include "footer.php";
?>