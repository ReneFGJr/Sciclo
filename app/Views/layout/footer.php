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
    document.querySelectorAll('.evidence-form').forEach(function (wrapperEl) {
      var modalEl = wrapperEl.closest('.modal');
      if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
      }
    });

    function initTooltips() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        if (tooltipTriggerEl.getAttribute('data-tooltip-ready') === '1') {
          return;
        }

        new bootstrap.Tooltip(tooltipTriggerEl, {
          trigger: 'hover focus',
          customClass: 'tooltip-glossario'
        });

        tooltipTriggerEl.setAttribute('data-tooltip-ready', '1');
      });
    }

    initTooltips();
    document.querySelectorAll('.questionnaire-sn-card').forEach(function (cardEl) {
      var checkedInput = cardEl.querySelector('.questionnaire-sn-input:checked');
      cardEl.dataset.savedAnswer = checkedInput ? checkedInput.value : '';
    });

    document.querySelectorAll('.questionnaire-sn-input').forEach(function (inputEl) {
      inputEl.addEventListener('change', function () {
        if (!inputEl.checked) {
          return;
        }

        var questionnaireForm = inputEl.closest('form');
        var cardEl = inputEl.closest('.questionnaire-sn-card');
        var saveUrl = questionnaireForm ? (questionnaireForm.dataset.answerSaveUrl || '') : '';
        var questionId = (inputEl.name || '').replace('questao_', '');

        if (!saveUrl || !cardEl || !questionId) {
          return;
        }

        var statusEl = cardEl.querySelector('.questionnaire-answer-status');
        if (!statusEl) {
          statusEl = document.createElement('div');
          statusEl.className = 'questionnaire-answer-status small fw-semibold mt-3';
          var optionsEl = cardEl.querySelector('.questionnaire-sn-options');
          if (optionsEl) {
            optionsEl.insertAdjacentElement('afterend', statusEl);
          }
        }

        var radioInputs = cardEl.querySelectorAll('.questionnaire-sn-input');
        radioInputs.forEach(function (radioEl) {
          radioEl.disabled = true;
        });

        statusEl.className = 'questionnaire-answer-status small fw-semibold mt-3 text-primary';
        statusEl.textContent = 'Salvando resposta...';

        var requestData = {
          question_id: questionId,
          resposta: inputEl.value
        };

        console.groupCollapsed('AJAX: salvando resposta');
        console.log('Endpoint:', saveUrl);
        console.table(requestData);
        console.groupEnd();

        fetch(saveUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new URLSearchParams(requestData).toString()
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Falha ao salvar a resposta.');
            }
            return response.json();
          })
          .then(function (result) {
            console.log('AJAX: resposta recebida', result);
            if (!result.saved) {
              throw new Error(result.message || 'Falha ao salvar a resposta.');
            }
            cardEl.dataset.savedAnswer = inputEl.value;
            statusEl.className = 'questionnaire-answer-status small fw-semibold mt-3 text-success';
            statusEl.textContent = 'Resposta salva.';
          })
          .catch(function (error) {

            console.error('AJAX: erro ao salvar resposta', error);
            var savedInput = cardEl.querySelector('.questionnaire-sn-input[value="' + cardEl.dataset.savedAnswer + '"]');
            inputEl.checked = false;
            if (savedInput) {
              savedInput.checked = true;
            }
            statusEl.className = 'questionnaire-answer-status small fw-semibold mt-3 text-danger';
            statusEl.textContent = 'Nao foi possivel salvar. Tente novamente.';
          })
          .finally(function () {
            radioInputs.forEach(function (radioEl) {
              radioEl.disabled = false;
            });
          });
      });
    });

    document.querySelectorAll('.evidence-edit-btn').forEach(function (buttonEl) {
      buttonEl.addEventListener('click', function () {
        var modalSelector = buttonEl.getAttribute('data-modal-target') || '';
        if (!modalSelector) {
          return;
        }

        var modalEl = document.querySelector(modalSelector);
        if (!modalEl) {
          return;
        }

        var editIdInput = modalEl.querySelector('.evidence-edit-id');
        var existingSelect = modalEl.querySelector('.evidence-existing-select');
        var urlInput = modalEl.querySelector('.evidence-url');
        var descInput = modalEl.querySelector('.evidence-description');
        var titleEl = modalEl.querySelector('.modal-title');

        if (editIdInput) {
          editIdInput.value = buttonEl.getAttribute('data-evidence-id') || '';
        }
        if (existingSelect) {
          existingSelect.value = '';
        }
        if (urlInput) {
          urlInput.value = buttonEl.getAttribute('data-evidence-url') || '';
        }
        if (descInput) {
          descInput.value = buttonEl.getAttribute('data-evidence-descricao') || '';
        }
        if (titleEl) {
          titleEl.textContent = 'Editar evidência';
        }

        initTooltips();

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      });
    });

    document.querySelectorAll('.evidence-delete-btn').forEach(function (buttonEl) {
      buttonEl.addEventListener('click', function () {
        var action = buttonEl.getAttribute('data-delete-action') || '';
        var axis = buttonEl.getAttribute('data-axis') || '';

        if (!action) {
          return;
        }

        var confirmed = window.confirm('Deseja realmente excluir esta evidência?');
        if (!confirmed) {
          return;
        }

        var postForm = document.createElement('form');
        postForm.method = 'post';
        postForm.action = action;
        postForm.style.display = 'none';

        var axisInput = document.createElement('input');
        axisInput.type = 'hidden';
        axisInput.name = 'axis';
        axisInput.value = axis;
        postForm.appendChild(axisInput);

        document.body.appendChild(postForm);
        postForm.submit();
      });
    });

    document.querySelectorAll('.evidence-existing-select').forEach(function (selectEl) {
      selectEl.addEventListener('change', function () {
        var modal = selectEl.closest('.modal');
        if (!modal) {
          return;
        }

        var editIdInput = modal.querySelector('.evidence-edit-id');
        if (editIdInput) {
          editIdInput.value = '';
        }

        var titleEl = modal.querySelector('.modal-title');
        if (titleEl) {
          titleEl.textContent = 'Inserir evidência';
        }
      });
    });

    document.querySelectorAll('.modal').forEach(function (modalEl) {
      modalEl.addEventListener('hidden.bs.modal', function () {
        var editIdInput = modalEl.querySelector('.evidence-edit-id');
        var existingSelect = modalEl.querySelector('.evidence-existing-select');
        var urlInput = modalEl.querySelector('.evidence-url');
        var descInput = modalEl.querySelector('.evidence-description');
        var titleEl = modalEl.querySelector('.modal-title');

        if (editIdInput) {
          editIdInput.value = '';
        }
        if (existingSelect) {
          existingSelect.value = '';
        }
        if (urlInput) {
          urlInput.value = '';
        }
        if (descInput) {
          descInput.value = '';
        }
        if (titleEl) {
          titleEl.textContent = 'Inserir evidência';
        }
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

    document.querySelectorAll('.evidence-submit-btn').forEach(function (buttonEl) {
      buttonEl.addEventListener('click', function () {
        var modal = buttonEl.closest('.modal');
        if (!modal) {
          return;
        }

        var wrapper = modal.querySelector('.evidence-form');
        var action = wrapper ? (wrapper.getAttribute('data-action') || '') : '';
        var questionInput = modal.querySelector('.evidence-question-id');
        var axisInput = modal.querySelector('.evidence-current-axis');
        var existingSelect = modal.querySelector('.evidence-existing-select');
        var urlInput = modal.querySelector('.evidence-url');
        var descInput = modal.querySelector('.evidence-description');
        var editIdInput = modal.querySelector('.evidence-edit-id');

        if (!action || !questionInput || !axisInput || !urlInput || !descInput) {
          return;
        }

        if (!urlInput.checkValidity()) {
          urlInput.reportValidity();
          return;
        }

        var postForm = document.createElement('form');
        postForm.method = 'post';
        postForm.action = action;
        postForm.style.display = 'none';

        var fields = [
          { name: 'questao_id', value: questionInput.value || '' },
          { name: 'current_axis', value: axisInput.value || '' },
          { name: 'edit_id', value: editIdInput ? (editIdInput.value || '') : '' },
          { name: 'evidence_id', value: existingSelect ? (existingSelect.value || '') : '' },
          { name: 'url', value: urlInput.value || '' },
          { name: 'descricao', value: descInput.value || '' }
        ];

        fields.forEach(function (field) {
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = field.name;
          input.value = field.value;
          postForm.appendChild(input);
        });

        document.body.appendChild(postForm);
        postForm.submit();
      });
    });
  });
</script>
</body>

</html>