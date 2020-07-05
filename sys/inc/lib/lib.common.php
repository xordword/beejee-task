<?php
function common_rmdir($dir) {
	if (!file_exists($dir) || !is_dir($dir) || !is_readable($dir) || !is_writable($dir)) return false;

	$d = opendir($dir);
	while (($f=readdir($d))!==false) {
		if ($f=='.' || $f=='..') continue;
		if (is_dir($dir.'/'.$f)) common_rmdir($dir.'/'.$f);
		else unlink($dir.'/'.$f);
	}
	closedir($d);
	clearstatcache();
	rmdir($dir);

	return true;
}
function common_cldir($dir, $i = 0) {
	if (!file_exists($dir) || !is_dir($dir) || !is_readable($dir) || !is_writable($dir)) return false;

	$d = opendir($dir);
	while (($f=readdir($d))!==false) {
		if ($f=='.' || $f=='..') continue;
		if (is_dir($dir.'/'.$f)) common_cldir($dir.'/'.$f, $i + 1);
		else unlink($dir.'/'.$f);
	}
	closedir($d);
	clearstatcache();
	if ($i) rmdir($dir);

	return true;
}
function common_str_between($buf, $str1, $str2, $offset = 0) {
	if (($p=strpos($buf, $str1, $offset))===false) return false;
	$p += strlen($str1);
	if (($p1=strpos($buf, $str2, $p))===false) return false;
	return substr($buf, $p, $p1-$p);
}

function common_client_ip() {
	$ip = empty($_SERVER['HTTP_CLIENT_IP'])?
		(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?
			(empty($_SERVER['REMOTE_ADDR'])?null:$_SERVER['REMOTE_ADDR']):
			$_SERVER['HTTP_X_FORWARDED_FOR']):
		$_SERVER['HTTP_CLIENT_IP'];
	return $ip && strlen($ip)>6?$ip:'0.0.0.0';
}

function common_auth_key($name, $pass) {
	return md5(md5($name).md5($pass)); //md5 для простоты, если надо защиту серьезнее то скажите)
}

function common_pagination_get_page($tot_pages, $p = 'p') {
	$p = isset($_GET[$p])?abs(intval($_GET[$p])):1;
	if (!$p || $p>$tot_pages) $p=1;

	return $p;
}

function common_pagination($link, $p, $tot_pages, $page = 1) {
	$result = '
<nav aria-label="...">
  <ul class="pagination pagination-sm">';
	if ($page > 1) $result .= '
    <li class="page-item">
      <a class="page-link" href="'.$link.($page - 1).'">Prev</a>
    </li>';
	else $result .= '
    <li class="page-item disabled">
      <a class="page-link" tabindex="-1" aria-disabled="true">Prev</a>
    </li>';
	if ($tot_pages > 1) for ($i = 0, $j = 1; $i < $tot_pages; $i++, $j++)
		if ($page == $j) $result .= '
    <li class="page-item active" aria-current="page">
      <a class="page-link">'.$j.' <span class="sr-only">(current)</span></a>
    </li>';
		else $result .= '
    <li class="page-item"><a class="page-link" href="'.$link.$j.'">'.$j.'</a></li>';
	else $result .= '
    <li class="page-item active" aria-current="page">
      <a class="page-link">1 <span class="sr-only">(current)</span></a>
    </li>';
	if ($page < $tot_pages) $result .= '
    <li class="page-item">
      <a class="page-link" href="'.$link.($page + 1).'">Next</a>
    </li>';
	else $result .= '
    <li class="page-item disabled">
      <a class="page-link" tabindex="-1" aria-disabled="true">Next</a>
    </li>';
	$result .= '
  </ul>
</nav>';
	return $result;
}