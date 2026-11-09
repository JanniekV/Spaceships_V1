<?php
// De classes moeten ingeladen zijn VOOR session_start(), anders weet PHP niet hoe hij de opgeslagen
// objecten uit de sessie weer moet terugbouwen.
include_once 'Spaceship.php';
include_once 'Game.php';

session_start();

// Een webpagina "vergeet" alles na elke klik. Daarom bewaren we het Game-object in de sessie.
// Bestaat er nog geen spel? Dan maken we een nieuwe instantie aan.
if (!isset($_SESSION['game']) || !($_SESSION['game'] instanceof Game)) {
    $_SESSION['game'] = new Game();
}
$game = $_SESSION['game'];

// Is er op een knop gedrukt? Dan roepen we de juiste method van het Game-object aan.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $shipId = $_POST['ship'] ?? '';

    switch ($action) {
        case 'shoot':
            $game->shoot($shipId);
            break;
        case 'move':
            $game->move($shipId);
            break;
        case 'reset':
            $_SESSION['game'] = new Game();
            break;
    }

    // Post/Redirect/Get: na de actie laden we de pagina opnieuw, zodat F5 de actie niet herhaalt.
    header('Location: index.php');
    exit;
}

// Hulpfunctie: zet een waarde om naar een percentage (max 100) voor de statusbalken.
function percentage(int $value, int $max = 100): int
{
    return max(0, min(100, (int) round($value / $max * 100)));
}

// Weergave-instellingen per schip (alleen uiterlijk, de echte data zit in de objecten).
$shipStyles = [
    'ship1' => ['name' => 'Ship 1', 'img' => 'img/ship1.svg', 'mod' => 'blue'],
    'ship2' => ['name' => 'Ship 2', 'img' => 'img/ship2.svg', 'mod' => 'red'],
];

// Is het gevecht voorbij? Dan mag niemand meer schieten.
$gameOver = false;
foreach ($game->getShips() as $ship) {
    if (!$ship->isAlive) {
        $gameOver = true;
    }
}
?>

<!doctype html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Spaceship Battle</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="stars" aria-hidden="true"></div>
    <div class="stars stars--slow" aria-hidden="true"></div>

    <header class="header">
        <h1 class="title">Spaceship <span>Battle</span></h1>
        <p class="subtitle">Object Georiënteerd Programmeren in PHP</p>
    </header>

    <main class="dashboard">

        <!-- Scheepskaarten met actieknoppen -->
        <section class="ships">
            <?php foreach ($game->getShips() as $shipId => $ship) {
                $style = $shipStyles[$shipId];
            ?>
                <article class="panel ship ship--<?= $style['mod'] ?> <?= $ship->isAlive ? '' : 'ship--dead' ?>">
                    <div class="ship__top">
                        <img class="ship__img" src="<?= $style['img'] ?>" alt="<?= $style['name'] ?>">
                        <div>
                            <h2 class="ship__name"><?= $style['name'] ?></h2>
                            <span class="badge <?= $ship->isAlive ? 'badge--alive' : 'badge--dead' ?>">
                                <?= $ship->isAlive ? 'Online' : 'Destroyed' ?>
                            </span>
                        </div>
                    </div>

                    <div class="stat">
                        <div class="stat__label"><span>Hitpoints</span><span><?= $ship->hitPoints ?></span></div>
                        <div class="bar"><div class="bar__fill bar__fill--hp" style="width: <?= percentage($ship->hitPoints) ?>%"></div></div>
                    </div>
                    <div class="stat">
                        <div class="stat__label"><span>Ammo</span><span><?= $ship->ammo ?></span></div>
                        <div class="bar"><div class="bar__fill bar__fill--ammo" style="width: <?= percentage($ship->ammo) ?>%"></div></div>
                    </div>
                    <div class="stat">
                        <div class="stat__label"><span>Fuel</span><span><?= $ship->fuel ?></span></div>
                        <div class="bar"><div class="bar__fill bar__fill--fuel" style="width: <?= percentage($ship->fuel) ?>%"></div></div>
                    </div>

                    <!-- Elke knop stuurt de actie en het schip mee naar de server -->
                    <form method="post" class="actions">
                        <input type="hidden" name="ship" value="<?= $shipId ?>">
                        <button type="submit" name="action" value="shoot" class="btn btn--shoot"
                            <?= ($gameOver || $ship->ammo === 0) ? 'disabled' : '' ?>>
                            Schieten
                        </button>
                        <button type="submit" name="action" value="move" class="btn btn--move"
                            <?= (!$ship->isAlive || $ship->fuel === 0) ? 'disabled' : '' ?>>
                            Bewegen
                        </button>
                    </form>
                </article>
            <?php } ?>
        </section>

        <!-- Logboek: wat er tot nu toe is gebeurd (nieuwste bovenaan) -->
        <section class="panel log">
            <div class="log__header">
                <h2 class="panel__title">Mission log</h2>
                <form method="post">
                    <button type="submit" name="action" value="reset" class="btn btn--reset">Opnieuw</button>
                </form>
            </div>
            <div class="log__screen">
                <span class="cursor" aria-hidden="true"></span>
                <?php foreach ($game->getLog() as $entry) { ?>
                    <p class="log__line <?= $entry['type'] ? 'log__line--' . $entry['type'] : '' ?>">
                        <?= htmlspecialchars($entry['message']) ?>
                    </p>
                <?php } ?>
            </div>
        </section>

    </main>
</body>

</html>
