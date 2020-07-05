<?php

$errors = [];
$task_added = false;
$name = $email = $text = '';
if (isset($_POST['name'], $_POST['email'], $_POST['text'])) {
	$name = trim($_POST['name']);
	$email = trim($_POST['email']);
	$text = trim($_POST['text']);

	if (!strlen($name)) $errors['name'] = 'please enter your name';
	elseif (!preg_match('/^[\d\w_]+$/i', $name))
		$errors['name'] = 'please enter correct name (eng chars and digits only)';
	if (!strlen($email)) $errors['email'] = 'please enter your email';
	elseif (!preg_match('/^[\w\d\._-]+@([\w\d-]+(\.[\w\-]+)+)$/i', $email))
		$errors['email'] = 'please enter correct email';
	if (!strlen($text)) $errors['text'] = 'please enter text';

	if (!$errors) {
		task_add($name, $email, $text);
		$name = $email = $text = '';
		$task_added = true;
	}
}

if ($task_added) {
	$_k['s']['content'] .= 'Your task was added.';
}

$_k['s']['content'].='
<form method="post" id="form_task">
  <div class="form-group row">
    <label for="input_name" class="col-sm-2 col-form-label">Name</label>
    <div class="col-sm-10">
      <input type="text" name="name" value="'.htmlspecialchars($name).'" class="form-control'.(isset($errors['name'])?' is-invalid':'').'" id="input_name">
      '.(isset($errors['name'])?'<div class="invalid-feedback">'.$errors['name'].'</div>':'').'
    </div>
  </div>
  <div class="form-group row">
    <label for="input_email" class="col-sm-2 col-form-label">Email</label>
    <div class="col-sm-10">
      <input type="text" name="email" value="'.htmlspecialchars($email).'" class="form-control'.(isset($errors['email'])?' is-invalid':'').'" id="input_email">
      '.(isset($errors['email'])?'<div class="invalid-feedback">'.$errors['email'].'</div>':'').'
    </div>
  </div>
  <div class="form-group row">
    <label for="input_text" class="col-sm-2 col-form-label">Text</label>
    <div class="col-sm-10">
      <input type="text" name="text" value="'.htmlspecialchars($text).'" class="form-control'.(isset($errors['text'])?' is-invalid':'').'" id="input_text">
      '.(isset($errors['text'])?'<div class="invalid-feedback">'.$errors['text'].'</div>':'').'
    </div>
  </div>
  <button type="submit" class="btn btn-primary">Create</button>
</form>';