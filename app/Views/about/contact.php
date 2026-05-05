<?php
echo view('layout/header');
echo view('layout/navbar');
?>
<?php
// app/Views/contact.php
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-4">Fale Conosco</h1>
            <p class="mb-4">Entre em contato pelo e-mail <a href="mailto:contato@sciclo.org">contato@sciclo.org</a> ou utilize o formulário abaixo:</p>
            <form method="post" action="#" class="card p-4 shadow-sm border-0">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="mensagem" class="form-label">Mensagem</label>
                    <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary px-5">Enviar</button>
            </form>
        </div>
    </div>
</div>
<?php
echo view('layout/footer');
