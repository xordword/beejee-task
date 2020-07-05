document.getElementById('form_auth').addEventListener('submit', e => {
	const form = e.currentTarget;
	const name = form.querySelector('[name="name"]').value.trim();
	const pass = form.querySelector('[name="pass"]').value.trim();
	let errors = new Map();

	form.querySelectorAll('input.is-invalid').forEach(el => el.classList.remove('is-invalid'));
	form.querySelectorAll('div.invalid-feedback').forEach(el => el.remove());

	if (!name.length) errors.set('name', 'Please enter name.');
	if (!pass.length) errors.set('pass', 'Please enter pass.');

	if (errors.size) {
		errors.forEach((val, key) => {
			const input = form.querySelector(`[name="${key}"]`);
			const div = document.createElement('div');
			div.classList.add('invalid-feedback');
			div.textContent = val;
			input.classList.add('is-invalid');
			input.after(div);
		});
		e.preventDefault();
	}
});

