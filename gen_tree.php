<?php

require_once "common.php";

if (!isset($_GET['id'])) {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

$output = ['target_id' => $_GET['id']];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	// Getting parents info: pair id, lft and rgt
	$sql = "SELECT `fps`.`id`, `fps`.`lft`, `fps`.`rgt`
			FROM `persons` AS `ps` LEFT JOIN `family_pairs` AS `fps`
			ON `fps`.`id`=`ps`.`parents_pair_id`
			WHERE `ps`.`id` = ".intval($_GET['id']);
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

			// Getting all people from gen tree...

			$parents_pairs_ids = []; // It is necessary to get children
			$persons_ids       = []; // To get dads and moms

			foreach ($output['family_pairs'] as $family_pair) {
				array_push($parents_pairs_ids, $family_pair['id']);
				array_push($persons_ids, $family_pair['man_id'], $family_pair['woman_id']);
			}

			$parents_pairs_ids = implode(", ", $parents_pairs_ids);
			$persons_ids       = implode(", ", $persons_ids);

			$sql = "SELECT `id`, `name`, `sex` FROM `persons` WHERE `parents_pair_id` IN (%s) OR `id` IN (%s)";
			
			$sth = $dbFamilies->prepare(sprintf($sql, $parents_pairs_ids, $persons_ids));
			if ($sth->execute()) {
				$output['persons'] = $sth->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$output['error'] = "cannot get dads and moms";
			}
		}
	} else {
		$output['error'] = "passed invalid person id";
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
