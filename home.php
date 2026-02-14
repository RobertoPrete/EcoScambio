<?php
include 'header.php'; // Include l'intestazione della pagina, che contiene il titolo, i link CSS e il menu di navigazione
?>
<main>
  <p class="introHome">Ogni anno, tonnellate di materiali tessili, caratacei, plastici o compositi vengono scartati dalle aziende di produzione e finiscono in una discarica, non perché siano inutilizzabili, ma perché non trovano la strada verso chi saprebbe dar loro nuova vita. Allo stesso tempo, migliaia di artigiani, designer e startup sostenibili lottano per accedere a materie prime a costi accessibili.</p>
  <h2><strong>Chi siamo e cosa facciamo</strong></h2>
  <p class="chiSiamo">ECOnnectify nasce proprio per colmare questo divario, creando un ponte tra chi ha materiali di scarto e chi è alla ricerca di risorse sostenibili per i propri progetti creativi. La nostra piattaforma è un luogo dove le aziende possono liberarsi in modo intelligente dei loro materiali inutilizzati, mentre artigiani, designer e creativi possono trovare tesori nascosti a prezzi accessibili.</p>
  <p class="chiSiamo"><span class="registratiAccedi"><a href="registrazione.php">Registrati</a></span> o <span class="registratiAccedi"><a href="login.php">accedi</a></span> per iniziare a scoprire offerte, pubblicare richieste e contribuire a un'economia più verde e collaborativa. Insieme, possiamo trasformare ciò che è "scarto" in nuova opportunità.</p>
  <h2><strong>Come funziona</strong></h2>
  <p class="comeFunziona">
    <ol>
        <li><span class="registratiAccedi"><a href="registrazione.php">Registrati</a></span> come azienda o artigiano.
        </li>
        <li>Se sei un azienda <span class="registratiAccedi"><a href="login.php">accedi</a></span> alla piattaforma per inserire nuovi materiali da vendere (<span class="offerta"><a href="offerta.php">OFFERTA</a></span>), se sei un artigiano accedi alla piattaforma e cerca i materiali che ti interessano(<span class="domanda"><a href="domanda.php">DOMANDA</a></span>).</li>
    </ol>
  </p>
  <p class="comeFunziona">La nostra piattaforma è semplice e intuitiva. Le aziende possono creare offerte dettagliate dei loro materiali di scarto, specificando quantità, condizioni e preferenze di scambio. Gli artigiani e i creativi possono cercare tra le offerte disponibili, filtrare per categoria o posizione e contattare direttamente le aziende per organizzare lo scambio. Ogni transazione è supportata da un sistema di feedback e valutazione, garantendo trasparenza e affidabilità all'interno della nostra comunità.</p>
</main>

<?php
// IMMAGINI CLICCABILI: codice per immagini cliccabili, che portano alla pagina domanda.php
?>
<!--
<section class="material-links">
  <h2>I nostri prodotti principali</h2>
  <div class="circles">

    <?php
    // Link alla pagina domanda.php con parametro mat=cemento
    ?>
    <div class="material">
      <a href="domanda.php?mat=cemento" title="Cemento rapido">
      <img src="img/cemento.jpg" alt="Immagine di Cemento rapido" aria-label="Cemento rapido, materiale da costruzione a presa rapida">
      </a>
      <details>
        <summary>Cemento Rapido</summary>
        <p>Materiale da costruzione a presa rapida, ideale per fissaggi e riparazioni veloci.</p>
      </details>
    </div>

    <?php
    // Link alla pagina domanda.php con parametro mat=pannello
    ?>
    <div class="material">
      <a href="domanda.php?mat=pannello" title="Pannello isolante 30 mm">
      <img src="img/pannello.jpg" alt="Immagine di Pannello isolante 30 mm" aria-label="Pannello isolante 30 mm, elemento isolante termico/acustico">
      </a>
      <details>
        <summary>Pannello Isolante 30 mm</summary>
        <p>Elemento isolante termico/acustico usato in edilizia, spesso in lana minerale o EPS.</p>
      </details>
    </div>

    <?php
    // Link alla pagina domanda.php con parametro mat=vernice
    ?>
    <div class="material">
      <a href="domanda.php?mat=vernice" title="Vernice antiruggine rossa">
        <img src="img/vernice.jpg" alt="Immagine di Vernice antiruggine rossa" aria-label="Vernice antiruggine rossa, ideale per prevenire la corrosione di metalli">
      </a>
      <details>
        <summary>Vernice Antiruggine Rossa</summary>
        <p>Vernice protettiva a base di ossidi, ideale per prevenire la corrosione di metalli.</p>
      </details>
    </div>

  </div>
</section>

<?php
    // parte dei materiali chimici presenti maggiormente nei materiali offerti
    ?>
<section class="chemical-materials">
  <h2>Materiali (elementi chimici)</h2>
  <p>Questi sono alcuni degli elementi chimici più comuni utilizzati nei materiali offerti:</p>
  <div class="elements">
    <div class="element">
      <img src="img/element_Ca.png" alt="Calcio (Ca)" aria-label="Simbolo chimico del Calcio (Ca)">
      <p>Ca</p>
    </div>
    <div class="element">
      <img src="img/element_Si.png" alt="Silicio (Si)" aria-label="Simbolo chimico del Silicio (Si)">
      <p>Si</p>
    </div>
    <div class="element">
      <img src="img/element_Fe.png" alt="Ferro (Fe)" aria-label="Simbolo chimico del Ferro (Fe)">
      <p>Fe</p>
    </div>
    <div class="element">
      <img src="img/element_C2H3Cl.png" alt="Cloruro di polivinile (PVC)" aria-label="Simbolo chimico del Cloruro di polivinile (PVC)">
      <p>C₂H₃Cl</p>
    </div>
  </div>
</section>
-->
<?php include 'footer.php'; ?>