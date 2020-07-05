document.getElementById('form_task').addEventListener('submit', e => {
	const form = e.currentTarget;
	const name = form.querySelector('[name="name"]').value.trim();
	const email = form.querySelector('[name="email"]').value.trim();
	const text = form.querySelector('[name="text"]').value.trim();
	let errors = new Map();

	form.querySelectorAll('input.is-invalid').forEach(el => el.classList.remove('is-invalid'));
	form.querySelectorAll('div.invalid-feedback').forEach(el => el.remove());

	if (!name.length) errors.set('name', 'Please enter name.');
	if (!email.length) errors.set('email', 'Please enter email.');
	else if (!/^[\w\d\._-]+@([\w\d-]+(\.[\w\-]+)+)$/i.test(email))
		errors.set('email', 'Please enter correct email.');
	if (!text.length) errors.set('text', 'Please enter text.');

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

