<!-- filepath: /Applications/XAMPP/xamppfiles/htdocs/eco_site_esame_v1.7/conferma.php -->
<?php
    include "header.php";
    if (session_status() === PHP_SESSION_NONE) // Verifica lo stato attuale: se la sessione non esiste (PHP_SESSION_NONE), la avvia; altrimenti, non fa nulla ed evita errori.
        session_start();

    // Recupera i dati inviati dal form
    // L'array $quantita_selezionata contiene le quantità selezionate per ogni materiale
    $quantita_selezionata = isset($_POST['quantita']) ? $_POST['quantita'] : [];

    // Connessione al database
    // Crea una connessione al database per recuperare i dettagli dei materiali selezionati
    $conn = new mysqli("localhost", "modificatore", "Str0ng#Admin9", "eco_scambio");
    if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

    $riepilogo = []; // Array per memorizzare i dettagli dei materiali selezionati
    $costo_totale = 0; // Variabile per calcolare il costo totale dell'acquisto

    // Recupera i dettagli dei materiali selezionati
    foreach ($quantita_selezionata as $id => $qta) {
        if ($qta > 0) { // Considera solo i materiali con quantità maggiore di 0
            // Prepara una query per recuperare i dettagli del materiale
            $stmt = $conn->prepare("SELECT NOME, COSTO, QUANTITA FROM MATERIALI WHERE ID = ?");
            $stmt->bind_param("i", $id); // Associa l'ID del materiale alla query
            $stmt->execute();
            $stmt->bind_result($nome, $prezzo, $qta_disp);
            $stmt->fetch();
            $stmt->close();

            // Aggiungi al riepilogo solo se la quantità richiesta è disponibile
            if ($qta <= $qta_disp) {
                $riepilogo[] = [
                    'id' => $id,
                    'nome' => $nome,
                    'qta' => $qta,
                    'prezzo' => $prezzo,
                    'totale' => $qta * $prezzo
                ];
                $costo_totale += $qta * $prezzo; // Aggiorna il costo totale
            }
            else{ //non so se funziona questo else ma ci provo. Se la quantità richiesta è maggiore di quella disponibile, mostra un messaggio di errore e reindirizza alla pagina domanda.php
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        alert('Quantità richiesta per il materiale \"$nome\" non disponibile. Quantità massima disponibile: $qta_disp.');
                        window.location.href = 'domanda.php';
                    });
                    </script>";
            }
        }
    }

    echo "<main>";
        // Mostra il riepilogo
        echo "<h2>Conferma acquisto</h2>";
        echo "<fieldset><legend>Riepilogo:</legend>";
        if (empty($riepilogo)) {
            // Se non ci sono materiali selezionati, mostra un messaggio e un link per tornare indietro
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    alert('Nessun materiale selezionato. Seleziona almeno un materiale per procedere con l\'acquisto.');
                    window.location.href = 'domanda.php';
                });
                </script>";
        } else {
            // Mostra i dettagli dei materiali selezionati in una tabella
            echo "<table class='materialiAcquisto'>";
            echo "<thead><tr><th>Nome</th><th>Quantità</th><th>Prezzo unitario (€)</th><th>Totale (€)</th></tr></thead>";
            echo "<tbody>";
            foreach ($riepilogo as $item) {
                echo "<tr>
                        <td>{$item['nome']}</td>
                        <td>{$item['qta']}</td>
                        <td>{$item['prezzo']}</td>
                        <td>{$item['totale']}</td>
                    </tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "<p><strong>Costo totale: €$costo_totale</strong></p>";

            // Controlla se il credito è sufficiente
            if ($costo_totale > $_SESSION['credito']) {
                // Se il credito è insufficiente, mostra un messaggio di errore
                echo "<p class='errori'>Credito insufficiente per completare l'acquisto.</p>";
                // Form per tornare indietro alla pagina domanda.php con i dati delle quantità selezionate
                echo "<form method='post' action='domanda.php'>";
                foreach ($quantita_selezionata as $id => $qta) { 
                    if ($qta > 0) {
                        echo "<input type='hidden' name='quantita[{$id}]' value='{$qta}'>";
                    }
                } 
                echo "<input type='submit' value='Indietro'>";
                echo "</form>";
            } else {
                // Se il credito è sufficiente, mostra un form per concludere l'acquisto
                echo "<form method='post' action='fine.php'>";
                foreach ($riepilogo as $item) {
                    echo "<input type='hidden' name='id[]' value='{$item['id']}'>";
                    echo "<input type='hidden' name='quantita[{$item['id']}]' value='{$item['qta']}'>";
                }
                echo "<input type='hidden' name='costo_totale' value='$costo_totale'>";
                echo "<input type='submit' value='Concludi'>";
                echo "</form>";
                // Form per annullare l'acquisto e tornare alla pagina domanda.php
                echo "<form method='post' action='domanda.php'>";
                foreach ($quantita_selezionata as $id => $qta) { 
                    if ($qta > 0) {
                        echo "<input type='hidden' name='quantita[{$id}]' value='{$qta}'>";
                    }
                } 
                echo "<input type='submit' value='Annulla'>";
                echo "</form>";
            }
        }
        echo "</fieldset>";
    echo "</main>";
    include "footer.php";
?>