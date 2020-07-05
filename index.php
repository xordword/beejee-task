<?php

require_once './sys/inc/include.inc';


$_k['s'] = array(
	'req'       => null,
	'interface' => null,
	'head'      => '',
	'script'    => '',
	'content'   => '',
	'menu'      => '',
	'auth'      => isset($_COOKIE['auth_key']) && $_COOKIE['auth_key'] == common_auth_key(CFG_ADMIN_NAME, CFG_ADMIN_PASS)
);

if ($_k['s']['auth'] && isset($_GET['logout'])) {
	setcookie('auth_key', null);
	sys_redir($_k['i']['www_root']);
}

if (isset($_GET['ajax'])) {
	require_once CFG_INC_DIR.'/ajax.inc.php';
	sys_end();
}

$pages = ['index' => 'Main page', 'newtask' => 'New task', 'auth' => 'Authorization'];

$_k['s']['page'] = isset($_GET['page']) && isset($pages[$_GET['page']])?$_GET['page']:'index';
$_k['s']['link'] = $_k['i']['www_root'].($_k['s']['page'] != 'index'?'?page='.$_k['s']['page']:'');

foreach ($pages as $k=>$v) {
	if ($k == 'auth' && $_k['s']['auth']) continue;
	$_k['s']['menu'] .= ($_k['s']['menu']?' | ':'').
		($k == $_k['s']['page']?$v:'<a href="'.$_k['i']['www_root'].($k != 'index'?'?page='.$k:'').'">'.$v.'</a>');
}
if ($_k['s']['auth']) $_k['s']['menu'] .= ' :: <a href="'.$_k['i']['www_root'].'?logout=1">Logout</a>';

//да, админский js подгрузится и для обычного юзера, просто не стал с этим заморачиваться)
if (file_exists(CFG_FILES_DIR.'/js/page.'.$_k['s']['page'].'.js')) $_k['s']['script'] .= '
<script src="'.$_k['i']['www_root'].'files/js/page.'.$_k['s']['page'].'.js"></script>';

require_once CFG_INC_DIR.'/'.$_k['s']['page'].'.inc.php';

$_k['tpl']->assign([
	'www_root'    => $_k['i']['www_root'],
	'title'       => $pages[$_k['s']['page']],
	'head'        => $_k['s']['head'],
	'content'     => $_k['s']['content'],
	'script'      => $_k['s']['script'],
	'menu'        => $_k['s']['menu']
]);

header('Content-Type: text/html; charset=UTF-8');
echo $_k['tpl']->parse('default');

sys_end();