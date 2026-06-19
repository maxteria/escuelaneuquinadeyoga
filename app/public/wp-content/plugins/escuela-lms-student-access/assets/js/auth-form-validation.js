(function () {
	'use strict';

	function validateResetForm(form) {
		var input = form.querySelector('#user_login');
		var existingError = form.querySelector('.enyf-form-error');

		if (existingError) {
			existingError.remove();
		}

		if (!input) {
			return true;
		}

		var value = input.value.trim();
		var message = '';

		if (value === '') {
			message = 'Por favor, ingresá tu nombre de usuario o correo electrónico.';
		} else if (value.indexOf('@') !== -1 && !isValidEmail(value)) {
			message = 'Por favor, ingresá un correo electrónico válido.';
		} else if (value.length < 3) {
			message = 'Por favor, ingresá al menos 3 caracteres.';
		}

		if (message !== '') {
			var error = document.createElement('p');
			error.className = 'enyf-form-error';
			error.setAttribute('role', 'alert');
			error.textContent = message;
			input.parentElement.appendChild(error);
			input.focus();
			return false;
		}

		return true;
	}

	function isValidEmail(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	}

	function init() {
		var resetForms = document.querySelectorAll('.ld-registration__forgot-password-form');
		resetForms.forEach(function (form) {
			var input = form.querySelector('#user_login');
			if (input) {
				input.setAttribute('aria-required', 'true');
				input.setAttribute('minlength', '3');
			}

			form.addEventListener('submit', function (event) {
				if (!validateResetForm(form)) {
					event.preventDefault();
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
