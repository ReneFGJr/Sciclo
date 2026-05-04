<?php echo view('layout/header'); ?>
<?php echo view('layout/navbar'); ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm p-4">
        <h2 class="mb-4 text-center">Cadastrar-se</h2>
        <form method="post" action="/register">
          <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" id="name" name="name" required autofocus>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-success w-100">Cadastrar</button>
        </form>
        <div class="mt-3 text-center">
          <a href="/login" class="small">Já tem conta? Entrar</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo view('layout/footer'); ?>
