(function () {
	'use strict';

	const wrappers = Array.from(document.querySelectorAll('.enyf-user-nav__wrapper.has-dropdown'));

	if (!wrappers.length) {
		return;
	}

	/**
	 * Close all open dropdowns.
	 */
	const closeAll = () => {
		wrappers.forEach((wrapper) => {
			wrapper.classList.remove('is-open');
			const trigger = wrapper.querySelector('.enyf-user-nav__trigger');
			if (trigger) {
				trigger.setAttribute('aria-expanded', 'false');
			}
		});
	};

	wrappers.forEach((wrapper) => {
		const trigger = wrapper.querySelector('.enyf-user-nav__trigger');
		const dropdown = wrapper.querySelector('.enyf-user-nav__dropdown');

		if (!trigger || !dropdown) {
			return;
		}

		trigger.addEventListener('click', function (event) {
			event.preventDefault();

			const isOpen = wrapper.classList.contains('is-open');
			closeAll();

			if (!isOpen) {
				wrapper.classList.add('is-open');
				trigger.setAttribute('aria-expanded', 'true');
			}
		});

		trigger.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeAll();
				trigger.focus();
			}
		});

		// Manage focus inside the dropdown.
		dropdown.addEventListener('keydown', function (event) {
			const items = Array.from(dropdown.querySelectorAll('[role="menuitem"]'));
			const currentIndex = items.indexOf(document.activeElement);

			if (event.key === 'ArrowDown') {
				event.preventDefault();
				const nextIndex = currentIndex >= 0 && currentIndex < items.length - 1 ? currentIndex + 1 : 0;
				items[nextIndex].focus();
			} else if (event.key === 'ArrowUp') {
				event.preventDefault();
				const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
				items[prevIndex].focus();
			} else if (event.key === 'Escape') {
				event.preventDefault();
				closeAll();
				trigger.focus();
			}
		});
	});

	// Close dropdowns when clicking outside.
	document.addEventListener('click', function (event) {
		if (event.target.closest('.enyf-user-nav__wrapper')) {
			return;
		}
		closeAll();
	});

	// Close dropdowns on Escape anywhere.
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeAll();
		}
	});
})();
