<section class="container py-5" style="background-color: #f8f9fa; border-radius: 8px;">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Selos de Certificação</h2>
        <p class="text-muted">Conheça os selos e seus critérios.</p>
    </div>
    <div class="row g-4 justify-content-center">
        <?php if (!empty($seals) && is_array($seals)): ?>
            <?php foreach ($seals as $seal): ?>
                <div class="col-md-4 col-lg-2">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <img src="<?= esc(base_url($seal['image'])) ?>" class="card-img-top" alt="<?= esc($seal['name']) ?>" style="width: 100%; height: auto; padding: 0; margin: 0;">
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?= esc($seal['name']) ?></h5>
                            <p class="card-text small text-muted"><?= esc($seal['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="text-muted">Nenhum selo cadastrado.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
