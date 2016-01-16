<?php

require_once "common.php";

define("PAGE_SIZE", 2);

if (!isset($_GET['sex'])) {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

// DB table name for mans or womans
$table = "";
if ($_GET['sex'] === "man") {
	$table = TABLE_MANS;
} elseif ($_GET['sex'] === "woman") {
	$table = TABLE_WOMANS;
} else {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

$page = 0;
if (isset($_GET['page'])) {
	$page = intval($_GET['page']);
}

$output = [
	'page' => $page,
	'sex'  => $_GET['sex']
];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	$page_count = ceil($dbFamilies->query("SELECT 1 FROM `$table`")->rowCount() / PAGE_SIZE);
	$output['page_count'] = $page_count;

	if ($page < $page_count) {
		$sql = sprintf("SELECT id, name FROM `$table` LIMIT %d, %d", $page*PAGE_SIZE, PAGE_SIZE);
		$output['persons'] = $dbFamilies->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	} else {
		$output['error'] = "invalid page count";
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
