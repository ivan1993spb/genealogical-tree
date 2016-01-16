<?php

require_once "common.php";

if (!isset($_GET['sex'], $_GET['id'])) {
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

$output = [
	'target_id'  => $_GET['id'],
	'target_sex' => $_GET['sex']
];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	$sql = sprintf("SELECT `fps`.`id`, `fps`.`lft`, `fps`.`rgt`
			FROM `%s` AS `ps` LEFT JOIN `family_pairs` AS `fps`
			ON `fps`.`id`=`ps`.`parents_pair_id`
			WHERE `ps`.`id` = %d",
		$table, intval($_GET['id']));

	$sth = $dbFamilies->query($sql);

	if ($sth->rowCount() === 1) {

		$parentsInfo = $sth->fetch(PDO::FETCH_ASSOC);

		if (empty($parentsInfo['id'])) {
			// Person has not parents
			$output['error'] = "person has not parents";
		} else {
			// Person has parents

			// Getting family tree pairs
			$sth = $dbFamilies->prepare("SELECT `id`, `man_id`, `woman_id`
				FROM `family_pairs` WHERE `lft` BETWEEN ? AND ?");
			$sth->execute([$parentsInfo['lft'], $parentsInfo['rgt']]);
			$output['family_pairs'] = $sth->fetchAll(PDO::FETCH_ASSOC);

			$parents_pairs_ids = [];
			$mans_ids          = [];
			$womans_ids        = [];
			foreach ($output['family_pairs'] as $family_pair) {
				array_push($parents_pairs_ids, $family_pair['id']);
				array_push($mans_ids, $family_pair['man_id']);
				array_push($womans_ids, $family_pair['woman_id']);
			}

			$parents_pairs_ids = implode(", ", $parents_pairs_ids);
			$mans_ids = implode(", ", $mans_ids);
			$womans_ids = implode(", ", $womans_ids);

			$sql = "SELECT `id`, `name` FROM `%s` WHERE `parents_pair_id` IN (%s) OR `id` IN (%s)";
			
			// Getting mans
			$sth = $dbFamilies->prepare(sprintf($sql, "mans", $parents_pairs_ids, $mans_ids));
			$sth->execute();
			$output['mans'] = $sth->fetchAll(PDO::FETCH_ASSOC);

			// Getting womans
			$sth = $dbFamilies->prepare(sprintf($sql, "womans", $parents_pairs_ids, $womans_ids));
			$sth->execute();
			$output['womans'] = $sth->fetchAll(PDO::FETCH_ASSOC);
		}
	} else {
		$output['error'] = "cannot find person: passed invalid sex or id";
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
