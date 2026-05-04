==><?= $repo_link ?>

<?php for ($i = 0; $i < 10; $i++): ?>
    <p>Processando o repositório... (Passo <?= $i + 1 ?> de 10)</p>
    <?php
    // Simula um processo demorado
    sleep(1);
    ob_flush();
    flush();
    ?>
<?php endfor; ?>