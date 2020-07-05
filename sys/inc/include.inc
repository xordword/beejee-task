<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('CFG_ROOT')) define('CFG_ROOT', str_replace('\\', '/', getcwd()));

$_k = array();

require_once CFG_ROOT.'/sys/inc/cfg.inc';
require_once CFG_LIB_DIR.'/lib.sys.php';
require_once CFG_LIB_DIR.'/lib.common.php';
require_once CFG_CLS_DIR.'/cls.template.php';
require_once CFG_CLS_DIR.'/cls.mysql.php';
require_once CFG_LIB_DIR.'/lib.task.php';

date_default_timezone_set('Europe/Moscow');
sys_no_cache();

$_k['i'] = [
	'uts'         => time(),
	'dt'          => date('Y-m-d H:i:s'),
	'dt_r'        => date('d.m.Y H:i:s'),
	'ip'          => common_client_ip(),
	'uri'         => $_SERVER['REQUEST_URI'],
	'www_root'    => substr(CFG_ROOT, strlen($_SERVER['DOCUMENT_ROOT'])).'/',
	'self'        => $_SERVER['PHP_SELF'],
	'script'      => basename($_SERVER['PHP_SELF']),
	'prot'        => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'?'https':'http',
];

$_k['tpl'] = new Template(CFG_TPL_DIR);
$_k['db']  = new MySQL;

$_k['tpl']->assign('www_root', $_k['i']['www_root']);

$_k['db']->connect(CFG_DB_HOST, CFG_DB_USER, CFG_DB_PASS, CFG_DB_NAME);
if ($err = $_k['db']->error()) sys_error($err);

$_k['db']->query('SET NAMES `utf8`, SQL_BIG_SELECTS=1, group_concat_max_len=4096');


header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: '.date('r'));