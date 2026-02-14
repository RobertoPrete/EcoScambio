<?php
// Include l'intestazione della pagina e avvia la sessione
include "header.php";

if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
    session_start();

// Controlla se l'utente è loggato
if (!isset($_SESSION['id']) || !isset($_SESSION['utente'])) {
    echo "<p>Accesso negato. Per accedere al contenuto di questa pagina devi essere loggato come 'Azienda'.</p>";
    include "footer.php";
    exit;
}

// Connessione al database MySQL
$conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

// Controlla se l'utente è un'azienda
$stmt = $conn->prepare("SELECT ARTIGIANO FROM UTENTI WHERE ID = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($isArtigiano);
$stmt->fetch();
$stmt->close();

if ($isArtigiano) {
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

    // Validazione dei dati
    if (!preg_match('/^[A-Za-z0-9 ]{10,40}$/', $nome)) $errors[] = "Nome non valido.";
    if (strlen($descrizione) > 250) $errors[] = "Descrizione troppo lunga.";
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) $errors[] = "Data non valida.";
    if (!filter_var($quantita, FILTER_VALIDATE_INT)) $errors[] = "Quantità non valida.";
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $costo) || ((int)($costo * 100) % 5 != 0)) $errors[] = "Costo non valido.";

    // Inserisce il materiale nel database se non ci sono errori
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO MATERIALI (NOME, DESCRIZIONE, DATA, QUANTITA, COSTO, ID_UTENTE) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssidi", $nome, $descrizione, $data, $quantita, $costo, $_SESSION['id']);
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

    // Validazione dei dati
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

// Recupera l'elenco dei materiali dell'azienda
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


<main>
    <h2>Gestione Materiali</h2>

    <!-- Mostra eventuali errori -->
    <?php foreach ($errors as $err) echo "<p class='errori'>$err</p>"; ?>

    <!-- Mostra messaggio di successo -->
    <?php if ($success) echo "<p class='successo'>$success</p>"; ?>

    <h3>Materiali Inseriti</h3>
    <!-- Form per aggiornare i materiali esistenti -->
    <?php foreach ($materiali as $mat): ?>
    <form method="post">
        <input type="hidden" name="id" value="<?= $mat['id'] ?>">
        <strong><?= htmlspecialchars($mat['nome']) ?></strong> (<?= $mat['data'] ?>)<br>
        Descrizione: <input type="text" name="descrizione" value="<?= htmlspecialchars($mat['descr']) ?>" size="40"><br>
        Quantità: <input type="text" name="quantita" value="<?= $mat['qta'] ?>"><br>
        Costo (€): <input type="text" name="costo" value="<?= $mat['costo'] ?>"><br>
        <input type="submit" name="update" value="Aggiorna">
    </form><hr>
    <?php endforeach; ?>

    <h3>Nuovo Materiale</h3>
    <!-- Form per inserire un nuovo materiale -->
    <form method="post">
        <input type="hidden" name="new" value="1">
        Nome: <input type="text" name="nome" required><br>
        Descrizione: <input type="text" name="descrizione" required><br>
        Data (YYYY-MM-DD): <input type="date" name="data" required><br>
        Quantità: <input type="number" name="quantita" required><br>
        Costo (€): <input type="text" name="costo" required><br>
        <input type="submit" value="Inserisci">
    </form>
</main>

<!-- Include il footer della pagina -->
<?php include "footer.php"; ?>