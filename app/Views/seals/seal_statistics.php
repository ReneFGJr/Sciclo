<div class="container py-5">
	<div class="text-center mb-5">
		<h2 class="fw-bold">Repositórios Avaliados</h2>
		<p class="text-muted">Acompanhe o total de repositórios avaliados e a distribuição por selo.</p>
	</div>
	<div class="row justify-content-center mb-5">
		<div class="col-md-6">
			<div class="card shadow border-0 bg-light text-center py-4">
				<div class="d-flex flex-column align-items-center">
					<div class="rounded-circle bg-primary text-white mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2.5rem;">
						<i class="bi bi-bar-chart-fill"></i>
					</div>
					<div class="display-4 fw-bold mb-2" style="letter-spacing: 2px;">
						<?= $totalRepositorios; ?>
					</div>
					<div class="text-muted">Total de repositórios avaliados</div>
				</div>
			</div>
		</div>
	</div>
</div>
