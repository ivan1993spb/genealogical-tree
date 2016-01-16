<?php

require_once "common.php";

define("PAGE_SIZE", 20);

$page = 0;
if (isset($_GET['page'])) {
	$page = intval($_GET['page']);
}

$output = ['page' => $page];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	$page_count = ceil($dbFamilies->query("SELECT 1 FROM `persons`")->rowCount() / PAGE_SIZE);
	$output['page_count'] = $page_count;

	if ($page < $page_count) {
		$sql = sprintf("SELECT `id`, `name`, `sex` FROM `persons`
			LIMIT %d, %d", $page*PAGE_SIZE, PAGE_SIZE);
		$output['persons'] = $dbFamilies->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	} else {
		$output['error'] = "passed invalid page";
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
