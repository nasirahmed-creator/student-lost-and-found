// assets/js/script.js
document.addEventListener('DOMContentLoaded', function () {

  // ---- Image preview for any input[type=file].js-photo-input ----
  document.querySelectorAll('.js-photo-input').forEach(function (input) {
    input.addEventListener('change', function () {
      var previewId = input.dataset.preview;
      var preview = document.getElementById(previewId);
      if (!preview) return;

      var file = input.files && input.files[0];
      if (!file) { preview.style.display = 'none'; return; }

      if (file.size > 5 * 1024 * 1024) {
        alert('Image is too large. Maximum size is 5MB.');
        input.value = '';
        preview.style.display = 'none';
        return;
      }

      var reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });
  });

  // ---- Click-to-upload areas that wrap a hidden file input ----
  document.querySelectorAll('.upload-drop').forEach(function (drop) {
    var input = drop.querySelector('input[type=file]');
    if (!input) return;
    drop.addEventListener('click', function () { input.click(); });
    drop.addEventListener('dragover', function (e) { e.preventDefault(); drop.classList.add('border-primary'); });
    drop.addEventListener('dragleave', function () { drop.classList.remove('border-primary'); });
    drop.addEventListener('drop', function (e) {
      e.preventDefault();
      drop.classList.remove('border-primary');
      if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
      }
    });
  });

  // ---- Bootstrap client-side validation ----
  document.querySelectorAll('.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  // ---- Auto-dismiss alerts after 5s ----
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      var instance = bootstrap.Alert.getOrCreateInstance(alert);
      instance.close();
    }, 5000);
  });

});
