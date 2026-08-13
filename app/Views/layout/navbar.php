<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= base_url(); ?>/">
      <img src="<?= base_url(); ?>/assets/logo/logo_sciclo.png" alt="Logo Sciclo" height="40" class="me-2">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="<?= base_url(); ?>">Início</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownSobre" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Sobre
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownSobre">
            <li><a class="dropdown-item" href="<?= base_url(); ?>/about">Sobre nós</a></li>
            <li><a class="dropdown-item" href="<?= base_url(); ?>/about/certification">Sobre a certificação</a></li>
            <li><a class="dropdown-item" href="<?= base_url(); ?>/about/faq">FAQ</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url(); ?>/contact">Contato</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url(); ?>/admin/guide-requirements">Guia de certificação</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url(); ?>/faq">FAQ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url(); ?>/glossary">Glossário</a>
        </li>
        <?php if (!session('logged_in')): ?>
        <li class="nav-item ms-2">
          <a class="nav-link d-flex align-items-center px-3 py-1 border border-2 border-secondary rounded-pill text-secondary" href="<?= base_url(); ?>/login" title="Login" style="gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
              <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
              <path d="M2 14s-1 0-1-1 1-4 7-4 7 3 7 4-1 1-1 1H2z" />
            </svg>
            <span>Login</span>
          </a>
        </li>
        <?php endif; ?>
        <?php if (session('logged_in')): ?>
        <li class="nav-item dropdown ms-2">
          <a class="nav-link dropdown-toggle border border-2 border-primary rounded-pill px-3 py-1 text-primary" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Administrador
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
            <li><a class="dropdown-item" href="<?= base_url(); ?>/admin/questions">Editar questões</a></li>
            <li><a class="dropdown-item" href="<?= base_url(); ?>/admin/glossario">Editar Glossário</a></li>
            <li><a class="dropdown-item" href="<?= base_url(); ?>/admin/faq">Editar FAQ</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>