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

$query = $pdo->prepare("SELECT `donors`.`name` AS `donor`, `email`, `affiliation`, `description`, `gifts`.`name`, `donors`.`created_at` FROM `gifts` LEFT JOIN `donors` ON `donors`.`id` = `donor` WHERE `donor` IS NOT NULL ORDER BY `gifts`.`updated_at` DESC");
$query->execute();

$gifts = $query->fetchAll();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Weihnachtsbaumaktion: Geschenke</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="font-sans antialiased text-gray-900">

<div class="px-5 py-6 max-w-screen-xl mx-auto">
    <table class="table-auto w-full">
        <tr>
            <th class="px-4 py-2">Person</th>
            <th class="px-4 py-2">E-Mail</th>
            <th class="px-4 py-2">Team</th>
            <th class="px-4 py-2">Geschenk</th>
            <th class="px-4 py-2">Datum</th>
        </tr>
        <?php foreach ($gifts as $gift): ?>
            <tr>
                <td class="border px-4 py-2"><?= htmlentities($gift->donor); ?></td>
                <td class="border px-4 py-2"><?= htmlentities($gift->email); ?></td>
                <td class="border px-4 py-2"><?= htmlentities($gift->affiliation); ?></td>
                <td class="border px-4 py-2"><?= htmlentities($gift->description); ?> für <?= htmlentities($gift->name); ?></td>
                <td class="border px-4 py-2"><?= (new Carbon($gift->created_at))->diffForHumans(); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
