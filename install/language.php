<?php

function install_language($mysqli, $language)
{
	if (!in_array($language, ['en', 'fr'], TRUE))
	{
		throw new InvalidArgumentException('Unsupported installation language.');
	}

	$result = $mysqli->query("SELECT `id`, `name`, `data` FROM `addon` WHERE `type_id` = 4 AND `name` IN ('en', 'fr')");
	$stmt = $mysqli->prepare('UPDATE `addon` SET `data` = ? WHERE `id` = ?');
	while ($row = $result->fetch_assoc())
	{
		$data = json_decode($row['data'], TRUE);
		$data['order'] = $row['name'] === $language ? 1 : 2;
		$data['enabled'] = TRUE;
		$json = json_encode($data);
		$stmt->bind_param('si', $json, $row['id']);
		$stmt->execute();
	}
	$stmt->close();
	$result->close();
}
