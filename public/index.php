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

$pdo = new PDO(sprintf('mysql:dbname=%s;host=%s;port=%d;charset=utf8mb4', $settings['db']['database'], $settings['db']['host'], $settings['db']['port']), $settings['db']['username'], $settings['db']['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

if (!empty($_POST)) {
    $gift = intval($_POST['gift']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $affiliation = trim($_POST['affiliation']);

    if ($gift <= 0 || empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($affiliation)) {
        http_response_code(400);
        exit;
    }

    $query = $pdo->prepare("INSERT INTO `donors` (`name`, `email`, `affiliation`) VALUES (:name, :email, :affiliation)");
    $query->bindValue('name', $name, PDO::PARAM_STR);
    $query->bindValue('email', $email, PDO::PARAM_STR);
    $query->bindValue('affiliation', $affiliation, PDO::PARAM_STR);
    $query->execute();

    $donor = $pdo->lastInsertId();

    $query = $pdo->prepare("UPDATE `gifts` SET `donor` = :donor WHERE `id` = :id");
    $query->bindValue('id', $gift, PDO::PARAM_INT);
    $query->bindValue('donor', $donor, PDO::PARAM_INT);
    $query->execute();
}

$query = $pdo->prepare("SELECT * FROM `gifts` WHERE `donor` IS NULL ORDER BY `created_at`");
$query->execute();

$gifts = $query->fetchAll();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Weihnachtsbaumaktion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<form action="/" method="post">

    <div class="max-w-screen-xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
        <h1 class="text-3xl leading-9 font-extrabold tracking-tight text-gray-900 sm:text-4xl sm:leading-10">
            RWTH Weihnachtsbaumaktion<br>
            <span class="text-red-800">Läuft ab <?= Carbon::createFromDate(2020, 12, 5)->diffForHumans(); ?>.</span>
        </h1>
    </div>

    <div class="mt-5 bg-chrstms">
        <div class="max-w-screen-xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
            <h2 class="text-2xl leading-6 font-extrabold tracking-tight text-red-800 bg-gray-100 px-5 py-6 sm:text-3xl sm:leading-8 mb-4 rounded shadow-lg">
                Geschenke
                <span class="block text-gray-800 text-lg mt-3 font-normal">Bitte ein Geschenk auswählen und anschließend Kontaktdaten angeben:</span>
            </h2>
            <div class="gifts">
                <?php foreach ($gifts as $gift): ?>
                    <div class="gift">
                        <label class="gift-label" for="gift-<?= $gift->id; ?>">
                            <h3 class="gift-title"><?= htmlentities($gift->name); ?>, <?= htmlentities($gift->age); ?> Jahre</h3>
                            <p class="gift-description"><?= htmlentities($gift->description); ?></p>
                            <p class="text-center"><input type="radio" name="gift" value="<?= $gift->id; ?>" id="gift-<?= $gift->id; ?>" required></p>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bg-gray-100">
        <?php if (isset($name)): ?>
            <div class="max-w-screen-xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
                <h2 class="text-2xl leading-7 font-extrabold tracking-tight text-red-800">Vielen Dank, <?= htmlentities($name); ?>!</h2>
            </div>
        <?php else: ?>
            <div class="max-w-screen-xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
                <h2 class="text-2xl leading-7 font-extrabold tracking-tight sm:text-4xl sm:leading-10 mb-6">
                    Kontaktdaten
                </h2>
                <div class="w-full max-w-lg">
                    <div class="form-group">
                        <div class="md:w-1/4">
                            <label class="form-label" for="name">Name:</label>
                        </div>
                        <div class="md:w-3/4">
                            <input class="form-input" name="name" id="name" type="text" placeholder="Vor- und Nachname" autocomplete="name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="md:w-1/4">
                            <label class="form-label" for="email">E-Mail:</label>
                        </div>
                        <div class="md:w-3/4">
                            <input class="form-input" name="email" id="email" type="email" placeholder="E-Mail-Adresse" autocomplete="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="md:w-1/4">
                            <label class="form-label" for="affiliation">Team:</label>
                        </div>
                        <div class="md:w-3/4">
                            <div class="inline-block relative w-full">
                                <select class="form-select" name="affiliation" id="affiliation" required>
                                    <option selected disabled>Team wählen</option>
                                    <option value="digitalHUB">digitalHUB</option>
                                    <option value="Lehrstuhl">WIN-Lehrstuhl</option>
                                    <option value="ZHV">Zentrale Hochschulverwaltung</option>
                                    <option value="Andere Einrichtung">Andere Einrichtung</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="muted">Mit dem Abschicken des Formulars erkläre ich mich einverstanden, dass meine angegebenen Daten für die Organisation der Weihnachtsbaumaktion gespeichert und verarbeitet werden. Die Daten werden nach Abschluss der Aktion gelöscht.</p>
                    <button class="form-button" type="submit">
                        Ich möchte mitschenken
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

</form>

</body>
</html>
