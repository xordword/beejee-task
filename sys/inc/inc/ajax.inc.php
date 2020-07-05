<?php

$result = false;
$data = 'unhandled ajax query';

//получаю JSON из stdin
$post = json_decode(file_get_contents('php://input'), true);

if ($_k['s']['auth']) {
	//админские ajax-запросы (других пока и нет)

	if (isset($post['toggle_done']) && $task = task_by_id($post['toggle_done'])) {
		task_upd($task['id'], ['done' => $done = $task['done']=='y'?'n':'y']);
		$result = true;
		$data = $done;
	} elseif (isset($_GET['get_task_text']) && $task = task_by_id($_GET['get_task_text'])) {
		//поскольку это просто чтение данных то использую GET
		$result = true;
		$data = $task['text'];
	} elseif (isset($post['save_task_text']) && $task = task_by_id($post['save_task_text'])) {
		task_upd($task['id'], ['text' => $text = trim($post['text'])]);
		$result = true;
		$data = htmlspecialchars($text);
	}
}


header('Content-type: application/json');
echo json_encode(['result' => $result, 'data' => $data]);