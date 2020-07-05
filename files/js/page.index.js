const table = document.getElementById('table_tasks');

if (table) {
	table.addEventListener('click', e => {
		if (e.target.tagName == 'A') {
			const a = e.target;
			const task_id = a.closest('tr').dataset.task_id;
			if (a.classList.contains('js_toggle_done')) {
				const span = document.createElement('span');
				span.textContent = 'loading...';
				a.style.display = 'none';
				a.after(span);
				fetch('?ajax=1', {
					method: 'POST',
					headers: {'Content-Type': 'application/json;charset=utf-8'},
					body: JSON.stringify({toggle_done: task_id})
				}).then(r => r.json())
				.then(data => {
					if (data.result) {
						a.classList.remove('btn-' + (data.data == 'y' ? 'primary' : 'success'));
						a.classList.add('btn-' + (data.data == 'y' ? 'success' : 'primary'));
						a.textContent = data.data == 'y' ? 'Done' : 'New';
					} else alert('error');
					span.remove();
					a.style.display = 'inline';
				});
			} else if (a.classList.contains('js_edit_text')) {
				const td = a.closest('tr').querySelector('.js_edit_text');
				const span = document.createElement('span');
				span.textContent = 'loading...';
				a.style.display = 'none';
				a.after(span);
				fetch('?ajax=1&get_task_text=' + task_id)
				.then(r => r.json())
				.then(data => {
					span.remove();
					if (data.result) {
						td.innerHTML = `<input type="text" value="${data.data}" class="form-control" />`;
						a.closest('td').innerHTML = '<a class="js_save_text btn btn-secondary btn-sm">save</a>' +
							'<a class="js_cancel_text btn btn-light btn-sm">cancel</a>';
					} else {
						a.style.display = 'inline';
						alert('error');
					}
				});
			} else if (a.classList.contains('js_save_text')) {
				const td = a.closest('tr').querySelector('.js_edit_text');
				const span = document.createElement('span');
				span.textContent = 'loading...';
				a.style.display = 'none';
				a.after(span);
				fetch('?ajax=1', {
					method: 'POST',
					headers: {'Content-Type': 'application/json;charset=utf-8'},
					body: JSON.stringify({
						save_task_text: task_id,
						text: td.querySelector('input').value
					})
				}).then(r => r.json())
				.then(data => {
					span.remove();
					if (data.result) {
						td.innerHTML = '';
						td.insertAdjacentHTML('afterbegin', data.data + '<br><small>edited</small>');
						a.closest('td').innerHTML = '<a class="js_edit_text btn btn-light btn-sm">edit</a>';
					} else {
						a.style.display = 'inline';
						alert('error');
					}
				});
			} else if (a.classList.contains('js_cancel_text')) {
				const td = a.closest('tr').querySelector('.js_edit_text');
				td.textContent = td.querySelector('input').value;
				if (td.classList.contains('js_edited')) td.insertAdjacentHTML('beforeend', '<br><small>edited</small>');
				a.closest('td').innerHTML = '<a class="js_edit_text btn btn-light btn-sm">edit</a>';
			}
		}
	});
}