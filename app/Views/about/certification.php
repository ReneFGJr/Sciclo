<?php
echo view('layout/header');
echo view('layout/navbar');
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="fw-bold mb-4">Sobre os Selos de Certificação</h1>
            <?php foreach ($seals as $seal): ?>
                <div class="card mb-4 shadow-sm border-0">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-3 text-center p-4">
                            <img src="<?= base_url(); ?><?= $seal['image']; ?>" alt="<?= $seal['name']; ?>" class="img-fluid" style="max-height: 150px;">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?= $seal['name']; ?></h5>
                                <p class="card-text"><?= $seal['description']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
echo view('layout/footer');
