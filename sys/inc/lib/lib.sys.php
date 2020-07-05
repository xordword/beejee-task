<?PHP
function sys_error($msg) {
	echo $msg;
	sys_end();
}
function sys_no_cache() {
	$y = date('Y');
	header('Expires: Mon, 1 Jan '.($y-1).' 00:00:00 GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
}
function sys_disable_magic_quotes_gpc() {
	if (get_magic_quotes_gpc()==1) {
		$in = array(&$_GET, &$_POST, &$_COOKIE);
		while (list($k, $v) = each($in)) {
			foreach ($v as $key => $val) {
				if (!is_array($val)) {
					$in[$k][$key] = stripslashes($val);
					continue;
				}
				$in[] = &$in[$k][$key];
			}
		}
		unset($in);
	}
}
function sys_redir($url='') {
	header('Location: '.$url);
	sys_end();
}
function sys_end() {
	global $_k;

	$_k['db']->close();
	exit;
}
?>