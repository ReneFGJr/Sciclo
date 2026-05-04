<div class="container py-5">
	<div class="text-center mb-5">
		<h2 class="fw-bold">Estatísticas dos Selos</h2>
		<p class="text-muted">Acompanhe o total de repositórios avaliados e a distribuição por selo.</p>
	</div>
	<div class="row justify-content-center mb-5">
		<div class="col-md-6">
			<div class="card shadow-sm p-4 text-center">
				<h4>Total de Repositórios Avaliados</h4>
				<span id="totalRepositorios" class="display-3 fw-bold text-primary">0</span>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card shadow-sm p-4">
				<h4 class="text-center mb-4">Total por Selo</h4>
				<canvas id="selosChart" height="120"></canvas>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Exemplo de dados fictícios, substitua por dados reais do backend se desejar
const totalRepositorios = 120;
const selos = ['Sciclo Verde', 'Sciclo Azul', 'Selo 3', 'Selo 4', 'Selo 5'];
const selosData = [40, 30, 20, 15, 15];

document.getElementById('totalRepositorios').textContent = totalRepositorios;

const ctx = document.getElementById('selosChart').getContext('2d');
const selosChart = new Chart(ctx, {
	type: 'bar',
	data: {
		labels: selos,
		datasets: [{
			label: 'Total de Repositórios',
			data: selosData,
			backgroundColor: [
				'#43a047', '#1976d2', '#ffa000', '#8e24aa', '#d32f2f'
			],
		}]
	},
	options: {
		responsive: true,
		plugins: {
			legend: { display: false },
		},
		scales: {
			y: {
				beginAtZero: true,
				ticks: { stepSize: 5 }
			}
		}
	}
});
</script>
