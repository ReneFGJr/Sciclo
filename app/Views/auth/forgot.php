<?php echo view('layout/header'); ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm p-4">
        <h2 class="mb-4 text-center">Recuperar Senha</h2>
        <form method="post" action="/forgot">
          <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required autofocus>
          </div>
          <button type="submit" class="btn btn-warning w-100">Enviar link de recuperação</button>
        </form>
        <div class="mt-3 text-center">
          <a href="/login" class="small">Voltar ao login</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo view('layout/footer'); ?>
