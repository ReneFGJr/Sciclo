<?php echo view('layout/header'); ?>
<?php echo view('layout/navbar'); ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm p-4">
        <h2 class="mb-4 text-center">Login</h2>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"> <?= $error ?> </div>
        <?php endif; ?>
        <form method="post" action="<?= base_url(); ?>/login">
          <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required autofocus>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= base_url(); ?>/forgot" class="small">Esqueceu a senha?</a>
            <a href="<?= base_url(); ?>/register" class="small">Cadastrar-se</a>
          </div>
          <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo view('layout/footer'); ?>
