/* Aura Interiors — Admin panel interactions */
(function () {
  'use strict';

  var toggle = document.getElementById('adminSidebarToggle');
  var sidebar = document.getElementById('adminSidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () { sidebar.classList.toggle('is-open'); });
  }
  if (window.innerWidth <= 960 && toggle) {
    toggle.style.display = 'block';
  }

  /* Confirm destructive actions */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      var msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  /* Live preview for single image upload inputs */
  document.querySelectorAll('.js-image-input').forEach(function (input) {
    input.addEventListener('change', function () {
      var box = input.closest('.image-upload-box');
      if (!box || !input.files || !input.files[0]) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        var img = box.querySelector('img');
        if (!img) {
          img = document.createElement('img');
          box.insertBefore(img, box.firstChild);
        }
        img.src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
    });
  });

  /* Auto-slugify title -> slug fields when the slug hasn't been hand-edited */
  document.querySelectorAll('[data-slug-source]').forEach(function (titleInput) {
    var slugField = document.querySelector(titleInput.getAttribute('data-slug-source'));
    if (!slugField) return;
    var edited = slugField.value.trim() !== '';
    slugField.addEventListener('input', function () { edited = true; });
    titleInput.addEventListener('input', function () {
      if (edited) return;
      slugField.value = titleInput.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
    });
  });

  /* Simple client-side table search filter */
  document.querySelectorAll('[data-table-search]').forEach(function (input) {
    var table = document.querySelector(input.getAttribute('data-table-search'));
    if (!table) return;
    input.addEventListener('input', function () {
      var q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
      });
    });
  });
})();
