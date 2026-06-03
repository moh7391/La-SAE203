document.addEventListener('DOMContentLoaded', function () {
  var fontKey = 'eillusion-font-size';
  var contrastKey = 'eillusion-contrast';
  var body = document.body;
  var panel = document.querySelector('.accessibility-panel');

  function applyFontSize(value) {
    body.classList.remove('font-large');
    if (value === 'large') {
      body.classList.add('font-large');
    }
    localStorage.setItem(fontKey, value);
    updatePressed('[data-font-size]', value);
  }

  function applyContrast(value) {
    body.classList.toggle('contrast-high', value === 'high');
    localStorage.setItem(contrastKey, value);
    updatePressed('[data-contrast]', value);
  }

  function updatePressed(selector, value) {
    document.querySelectorAll(selector).forEach(function (button) {
      var active = button.getAttribute(selector === '[data-font-size]' ? 'data-font-size' : 'data-contrast') === value;
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  applyFontSize(localStorage.getItem(fontKey) || 'normal');
  applyContrast(localStorage.getItem(contrastKey) || 'normal');

  if (!panel) {
    return;
  }

  panel.addEventListener('click', function (event) {
    var button = event.target.closest('button');
    if (!button) {
      return;
    }
    if (button.dataset.fontSize) {
      applyFontSize(button.dataset.fontSize);
    }
    if (button.dataset.contrast) {
      applyContrast(button.dataset.contrast);
    }
  });
});
