<?php

const CFG_TASK_TABLE = CFG_DB_PREF.'task';
const CFG_TASK_COUNT = 3;


function task_add($name, $email, $text) {
	global $_k;

	$_k['db']->insert(
		'`'.CFG_TASK_TABLE.'`(`name`, `email`, `text`, `cdt`, `cdt_r`)',
		['%s, %s, %s, %s, %s', [$name, $email, $text, $_k['i']['dt'], $_k['i']['dt_r']]]
	);
}

function task_by_id($id) {
	global $_k;

	return $_k['db']->row('`'.CFG_TASK_TABLE.'`', $id);
}

function task_upd($id, $data) {
	global $_k;

	if (!$data || !($task = task_by_id($id))) return false;

	$fstr = '';
	$vars = [];
	$flag = false;

	$fields = ['name', 'email', 'text', 'done'];
	for ($i=0, $sz=sizeof($fields); $i<$sz; $i++) if (isset($data[$s = $fields[$i]])) {
		$fstr   .= ($flag?', ':'').'`'.$s.'`=%s';
		$vars[]  = $data[$s];
		$flag    = true;
		if ($s == 'text') {
			$fstr  .= ', `udt`=%s, `udt_r`=%s';
			$vars[] = $_k['i']['dt'];
			$vars[] = $_k['i']['dt_r'];
		}
	}

	if ($fstr) $_k['db']->upd('`'.CFG_TASK_TABLE.'`', [$fstr, $vars], $task['id']);

	return true;
}

function task_list_by_page($sort = '`name` ASC', $expr = null, $psize = CFG_TASK_COUNT, $pvar = 'p') {
	global $_k;

	$data = $_k['db']->rows_by_page('`'.CFG_TASK_TABLE.'`', [
		'page_var'  => $pvar,
		'page_size' => $psize,
		'expr'      => $expr, 
		'ob'        => $sort
	]);
	if ($data['rows']) return $data;

	return null;
}

function task_count() {
	global $_k;

	return $_k['db']->count('`'.CFG_TASK_TABLE.'`');
}