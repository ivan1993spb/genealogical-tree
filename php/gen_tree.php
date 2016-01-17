<?php
/*
	Script returns JSON lists of nodes for genealogical tree for a person
	Nodes are persons and family pairs

	Input: person id
*/
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// $ _
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
			$familyPairs = $sth->fetchAll(PDO::FETCH_ASSOC);

			// Getting all people from gen tree...

			$parents_pairs_ids = []; // It is necessary to get children
			$persons_ids       = []; // To get dads and moms

			foreach ($familyPairs as $family_pair) {
				array_push($parents_pairs_ids, $family_pair['id']);
				array_push($persons_ids, $family_pair['man_id'], $family_pair['woman_id']);
			}

			$parents_pairs_ids = implode(", ", $parents_pairs_ids);
			$persons_ids       = implode(", ", $persons_ids);

			$sql = "SELECT `id`, `name`, `sex`, `parents_pair_id` FROM `persons`
				WHERE `parents_pair_id` IN (%s) OR `id` IN (%s)";
			
			$sth = $dbFamilies->prepare(sprintf($sql, $parents_pairs_ids, $persons_ids));
			if ($sth->execute()) {
				$persons = $sth->fetchAll(PDO::FETCH_ASSOC);

				// Verify and clear tree...

				$person_ids_todo = [$_GET['id']];

				$persons_ids_done  = [];
				$trusted_pairs_ids = [];

				$output['persons']      = [];
				$output['family_pairs'] = [];

				while (count($person_ids_todo) > 0) {
					$person_id = array_shift($person_ids_todo);

					foreach ($persons as $person) {
						if ($person['id'] === $person_id) {
							array_push($output['persons'], $person);
							array_push($persons_ids_done, $person_id);

							foreach ($familyPairs as $pair) {
								if ($pair['id'] === $person['parents_pair_id']) {
									array_push($output['family_pairs'], $pair);
									// Pair is trusted so we can get their children
									array_push($trusted_pairs_ids, $person['parents_pair_id']);
									array_push($person_ids_todo, $pair['man_id'], $pair['woman_id']);

									break;
								}
							}

							break;
						}
					}
				}

				// Get children of trusted pairs
				foreach ($persons as $person) {
					// Push if person is child of one of the trusted pairs and was not added yet
					if (in_array($person['parents_pair_id'], $trusted_pairs_ids) &&
						!in_array($person['id'], $persons_ids_done)) {

						array_push($output['persons'], $person);
					}
				}
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
