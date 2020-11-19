<?php

declare(strict_types=1);

use Carbon\Carbon;

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Berlin');
setlocale(LC_ALL, 'de_DE');
ini_set('error_reporting', '-1');
ini_set('expose_php', 'off');
header_remove('X-Powered-By');

require __DIR__ . '/../vendor/autoload.php';
$settings = require __DIR__ . '/../settings.php';

Carbon::setLocale('de_DE');

if (!isset($_GET['pwd']) || empty($_GET['pwd']) || $_GET['pwd'] !== $settings['pwd']) {
    http_response_code(403);
    exit;
}

$pdo = new PDO(sprintf('mysql:dbname=%s;host=%s;port=%d;charset=utf8mb4', $settings['db']['database'], $settings['db']['host'], $settings['db']['port']), $settings['db']['username'], $settings['db']['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

$query = $pdo->prepare("SELECT `gifts`.`id`, `donors`.`name` AS `donor`, `email`, `affiliation`, `description`, `gifts`.`name`, `gifts`.`age`, `donors`.`created_at` FROM `gifts` LEFT JOIN `donors` ON `donors`.`id` = `donor` WHERE `donor` IS NOT NULL ORDER BY `gifts`.`updated_at` DESC");
$query->execute();

$gifts = $query->fetchAll();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Weihnachtsbaumaktion: Geschenke</title>
    <link rel="stylesheet" href="style.css?v2">
</head>
<body>

<div class="px-5 py-6 max-w-screen-xl mx-auto">
    <table>
        <tr>
            <th>ID</th>
            <th>Person</th>
            <th>E-Mail</th>
            <th>Team</th>
            <th>Geschenk</th>
            <th>Datum</th>
        </tr>
        <?php foreach ($gifts as $gift): ?>
            <tr>
                <td><?= htmlentities($gift->id); ?></td>
                <td><?= htmlentities($gift->donor); ?></td>
                <td><a href="mailto:<?= htmlentities($gift->email); ?>?subject=Weihnachtsbaumaktion%202020&body=Hallo%20<?= htmlentities($gift->donor); ?>,%0A%0A%0A" class="text-red-800 hover:underline"><?= htmlentities($gift->email); ?></a></td>
                <td><?= htmlentities($gift->affiliation); ?></td>
                <td><?= htmlentities($gift->description); ?> (für <?= htmlentities($gift->name); ?>, <?= htmlentities($gift->age); ?>)</td>
                <td><?= (new Carbon($gift->created_at))->diffForHumans(); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
