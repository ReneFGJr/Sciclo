<?php
echo view('layout/header');
echo view('layout/navbar');
?>

<!-- Hero Section -->
<?php echo view('welcome_brand'); ?>


<!-- Templates Section -->
<section class="container py-5" id="portfolio">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Comece com um template de portfólio</h2>
        <p class="text-muted">Personalize cada página para mostrar seu trabalho da melhor forma.</p>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <img src="https://media-www.sqspcdn.com/images/pages/flagship/portfolios/hero/hero-desktop-1500w.webp" class="card-img-top" alt="Template 1">
                <div class="card-body">
                    <h5 class="card-title">Template Moderno</h5>
                    <p class="card-text">Layout responsivo, visual elegante e fácil de personalizar para qualquer projeto.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <img src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Template 2">
                <div class="card-body">
                    <h5 class="card-title">Template Criativo</h5>
                    <p class="card-text">Ideal para destacar projetos visuais, portfolios de arte, design e fotografia.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <img src="https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Template 3">
                <div class="card-body">
                    <h5 class="card-title">Template Profissional</h5>
                    <p class="card-text">Perfeito para consultores, freelancers e profissionais que querem atrair clientes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <div class="mb-3">
                    <span class="display-5 text-primary"><i class="bi bi-palette"></i></span>
                </div>
                <h5 class="fw-bold">Personalizável</h5>
                <p class="text-muted">Edite cada detalhe do seu portfólio para refletir sua identidade visual.</p>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <span class="display-5 text-primary"><i class="bi bi-envelope-paper"></i></span>
                </div>
                <h5 class="fw-bold">Capte Contatos</h5>
                <p class="text-muted">Formulários de contato para gerar leads e oportunidades de negócio.</p>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <span class="display-5 text-primary"><i class="bi bi-tools"></i></span>
                </div>
                <h5 class="fw-bold">Fácil de Editar</h5>
                <p class="text-muted">Arraste, solte e publique projetos rapidamente com ferramentas intuitivas.</p>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <span class="display-5 text-primary"><i class="bi bi-graph-up"></i></span>
                </div>
                <h5 class="fw-bold">Cresça com Vendas</h5>
                <p class="text-muted">Venda serviços, agende reuniões e gerencie clientes em um só lugar.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="container py-5" id="contato">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 border-0 shadow-sm">
                <h2 class="mb-3">Fale com a equipe</h2>
                <p>Quer saber mais ou criar seu portfólio? Envie um e-mail para <a href="mailto:contato@sciclo.org">contato@sciclo.org</a> ou preencha o formulário abaixo:</p>
                <form>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" placeholder="Seu nome">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" placeholder="seu@email.com">
                    </div>
                    <div class="mb-3">
                        <label for="mensagem" class="form-label">Mensagem</label>
                        <textarea class="form-control" id="mensagem" rows="3" placeholder="Como podemos ajudar?"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php echo view('layout/footer'); ?>