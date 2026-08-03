<footer class="bg-dark text-light py-4 mt-5">
  <div class="container">
    <div class="row align-items-center justify-content-center">
      <div class="col-12 col-md-4 mb-3 mb-md-0 text-center text-md-end">
        <img src="<?= base_url(); ?>/assets/logo/logo-ufrgs-branco.webp" class="me-5" alt="Logo CNPq" style="max-height: 70px;">
        <img src="<?= base_url(); ?>/assets/logo/logo_sciclo_pb.png" class="me-5" alt="Logo Sciclo" style="max-width: 120px;">
      </div>
      <div class="col-12 col-md-4 text-center my-2">
        <p class="mb-1 fw-bold">&copy; 2026 Sciclo</p>
        <small class="d-block mb-2">Desenvolvido para promover interoperabilidade, transparência e sustentabilidade na comunicação científica.</small>
        <small class="text-secondary">Todos os direitos reservados.</small>
      </div>
      <div class="col-12 col-md-4 mb-3 mb-md-0 text-center text-md-start">
        <img src="<?= base_url(); ?>/assets/footer/ibict-mctic.png" alt="Logo IBICT-MCTIC" style="max-height: 70px;">
      </div>
    </div>
  </div>
</footer>
<style>
  .glossario-term {
    border-bottom: 1px dotted currentColor;
    cursor: help;
    font-weight: 600;
  }

  .tooltip.tooltip-glossario .tooltip-inner {
    max-width: 600px;
    width: 600px;
    text-align: left;
  }

  .evidence-list-link {
    text-decoration: none;
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: 'hover focus',
        customClass: 'tooltip-glossario'
      });
    });

    document.querySelectorAll('.evidence-existing-select').forEach(function (selectEl) {
      selectEl.addEventListener('change', function () {
        var modal = selectEl.closest('.modal');
        if (!modal) {
          return;
        }

        var urlInput = modal.querySelector('.evidence-url');
        var descInput = modal.querySelector('.evidence-description');
        var selectedOption = selectEl.options[selectEl.selectedIndex];

        if (!urlInput || !descInput || !selectedOption) {
          return;
        }

        var url = selectedOption.getAttribute('data-url') || '';
        var descricao = selectedOption.getAttribute('data-descricao') || '';

        if (url) {
          urlInput.value = url;
        }

        if (descricao) {
          descInput.value = descricao;
        }
      });
    });
  });
</script>
</body>

</html>