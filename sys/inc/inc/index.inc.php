<?php

if (task_count()) {
	$sort_fields = array(
		's_name'  => '`name`',
		's_email' => '`email`',
		's_done'  => '`done`'
	);
	$sort = null;
	foreach ($sort_fields as $k=>$v) if (isset($_GET[$k])) {
		$param[$k] = $_GET[$k];
		$sort = $k;
		$sc = $_GET[$k];
		break;
	}
	if (!isset($sort)) {
		$sort = 's_name';
		$sc = 0;
	}

	$data = task_list_by_page($sort_fields[$sort].($sc?' DESC':''));

	$rows = &$data['rows'];

	$page_index = common_pagination_get_page($data['tot_pages']);

	$_k['s']['content'] .= '
<table class="table" id="table_tasks">
<thead>
<tr>
  <th scope="col" width="6%">ID</th>
  <th scope="col">Name '.($sort=='s_name'?($sc?'<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').$sort.'=0">&uarr;</a> &darr;':'&uarr; <a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').$sort.'=1">&darr;</a>'):'<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').'s_name=0">&uarr;</a>&nbsp;<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').'s_name=1">&darr;</a>').'</th>
  <th scope="col">Email '.($sort=='s_email'?($sc?'<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').$sort.'=0">&uarr;</a> &darr;':'&uarr; <a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').$sort.'=1">&darr;</a>'):'<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').'s_email=0">&uarr;</a>&nbsp;<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').'s_email=1">&darr;</a>').'</th>
  <th scope="col">Status '.($sort=='s_done'?($sc?'<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').$sort.'=0">&uarr;</a> &darr;':'&uarr; <a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').$sort.'=1">&darr;</a>'):'<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').'s_done=0">&uarr;</a>&nbsp;<a href="'.$_k['s']['link'].'?'.($page_index > 1?'p='.$page_index.'&':'').'s_done=1">&darr;</a>').'</th>
  <th scope="col">Task</th>
  '.($_k['s']['auth']?'<th width="10%"></th>':'').'
</tr>
</thead>
<tbody>';
	for ($i=0, $sz=sizeof($rows); $i<$sz; $i++) {
		$_k['s']['content'] .= '
<tr data-task_id="'.$rows[$i]['id'].'">
  <td>'.$rows[$i]['id'].'</td>
  <td>'.$rows[$i]['name'].'</td>
  <td>'.$rows[$i]['email'].'</td>';
		if ($_k['s']['auth']) $_k['s']['content'] .= '
  <td><a class="js_toggle_done btn btn-'.($rows[$i]['done']=='y'?'success':'primary').' btn-sm">'.($rows[$i]['done']=='y'?'Done':'New').'</a></td>';
		else $_k['s']['content'] .= '
  <td><span class="badge badge-'.($rows[$i]['done'] == 'y' ? 'success' : 'primary').'">'.($rows[$i]['done'] == 'y' ? 'Done' : 'New').'</span></td>';
		$_k['s']['content'] .= '
  <td class="js_edit_text'.($rows[$i]['udt']?' js_edited':'').'">
    '.htmlspecialchars($rows[$i]['text']).'
    '.($rows[$i]['udt']?'<br><small>edited</small>':'').'
  </td>
  '.($_k['s']['auth']?'<td><a class="js_edit_text btn btn-light btn-sm">edit</a></td>':'').'
</tr>';
	}
	$_k['s']['content'] .= '
</tbody>
<tfoot>
</tfoot>
</table>
'.($data['tot_pages'] > 1?common_pagination(
	$_k['s']['link'].'?'.($sort?$sort.'='.$sc.'&':'').'p=',
	'p',
	$data['tot_pages'],
	$page_index
):'');
} else $_k['s']['content'] .= '<br>There is no tasks yet.';