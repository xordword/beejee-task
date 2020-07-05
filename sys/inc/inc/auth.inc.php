<?php

$errors = [];
$name = $pass = '';
if (isset($_POST['name'], $_POST['pass'])) {
	$name = trim($_POST['name']);
	$pass = trim($_POST['pass']);

	if (!strlen($name)) $errors['name'] = 'Please enter name.';
	if (!strlen($pass)) $errors['pass'] = 'Please enter password.';
	if (!$errors && ($name != CFG_ADMIN_NAME || $pass != CFG_ADMIN_PASS))
		$errors['global'] = 'Incorrect name or password.';

	if (!$errors) {
		setcookie('auth_key', common_auth_key($name, $pass), isset($_POST['remember'])?$_k['i']['uts'] + 86400:0);
		sys_redir($_k['i']['www_root']);
	}
}

$_k['s']['content'].='
<form id="form_auth" method="post">
  '.(isset($errors['global'])?'<span class="badge badge-danger">'.$errors['global'].'</span>':'').'
  <div class="form-group">
    <label for="input_name">Admin name</label>
    <input type="text" name="name" value="'.htmlspecialchars($name).'" class="form-control'.(isset($errors['name'])?' is-invalid':'').'" id="input_name">
    '.(isset($errors['name'])?'<div class="invalid-feedback">'.$errors['name'].'</div>':'').'
  </div>
  <div class="form-group">
    <label for="input_pass">Admin password</label>
    <input type="password" name="pass" value="'.htmlspecialchars($pass).'" class="form-control'.(isset($errors['name'])?' is-invalid':'').'" id="input_pass">
    '.(isset($errors['pass'])?'<div class="invalid-feedback">'.$errors['pass'].'</div>':'').'
  </div>
  <div class="form-group form-check">
    <input type="checkbox" name="remember" value="1" class="form-check-input" id="input_remember">
    <label class="form-check-label" for="input_remember">Remember me</label>
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
</form>';