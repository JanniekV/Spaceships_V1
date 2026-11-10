<?php
// De classes moeten ingeladen zijn VOOR session_start(), anders weet PHP niet hoe hij de opgeslagen
// objecten uit de sessie weer moet terugbouwen.
include_once 'Spaceship.php';
include_once 'Fighter.php';
include_once 'Tank.php';
include_once 'Game.php';

session_start();

// Een webpagina "vergeet" alles na elke klik. Daarom bewaren we het Game-object in de sessie.
// Bestaat er nog geen spel? Dan maken we een nieuwe instantie aan.
if (!isset($_SESSION['spaceshipGame']) || !($_SESSION['spaceshipGame'] instanceof Game)) {
    $_SESSION['spaceshipGame'] = new Game();
}
$game = $_SESSION['spaceshipGame'];

// Is er op een knop gedrukt? Dan roepen we de juiste method van het Game-object aan.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $shipId = $_POST['ship'] ?? '';
    $type = $_POST['type'] ?? '';

    switch ($action) {
        case 'choose':
            $game->chooseShip($shipId, $type);
            break;
        case 'start':
            $game->start();
            break;
        case 'shoot':
            $game->shoot($shipId);
            break;
        case 'reset':
            $_SESSION['spaceshipGame'] = new Game();
            break;
    }

    // Post/Redirect/Get: na de actie laden we de pagina opnieuw, zodat F5 de actie niet herhaalt.
    header('Location: index.php');
    exit;
}

// Hulpfunctie: zet een waarde om naar een percentage voor de statusbalken.
function percentage(int $value, int $max): int
{
    if ($max <= 0) {
        return 0;
    }
    return max(0, min(100, (int) round($value / $max * 100)));
}

// Hulpfunctie: het pad naar de afbeelding, bijv. img/tank-red.svg
function shipImage(Spaceship $ship, string $color): string
{
    return 'img/' . strtolower($ship->getType()) . '-' . $color . '.svg';
}

// Weergave-instellingen per speler (alleen uiterlijk, de echte data zit in de objecten).
$players = [
    'ship1' => ['name' => 'Speler 1', 'mod' => 'blue'],
    'ship2' => ['name' => 'Speler 2', 'mod' => 'red'],
];

$gameOver = $game->isGameOver();
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

        <!-- Status-indicator: kiezen, wie aan de beurt is, of voorbij -->
        <?php if (!$game->isStarted()) { ?>
            <p class="turn turn--select">Kies allebei een schip</p>
        <?php } elseif ($gameOver) { ?>
            <p class="turn turn--over">Gevecht voorbij</p>
        <?php } else {
            $turnPlayer = $players[$game->getCurrentTurn()];
        ?>
            <p class="turn turn--<?= $turnPlayer['mod'] ?>">
                Ronde <?= $game->getRound() ?> &middot; <strong><?= $turnPlayer['name'] ?></strong> is aan de beurt
            </p>
        <?php } ?>
    </header>

    <main class="dashboard">

        <section class="ships">
            <?php if (!$game->isStarted()) { ?>

                <!-- ===== Fase 1: schepen kiezen ===== -->
                <?php foreach ($players as $playerId => $player) {
                    $chosen = $game->getShip($playerId);
                ?>
                    <article class="panel select ship--<?= $player['mod'] ?>">
                        <h2 class="ship__name"><?= $player['name'] ?></h2>
                        <p class="select__hint">
                            <?= $chosen === null ? 'Kies je schip:' : 'Gekozen: <strong>' . $chosen->getType() . '</strong>' ?>
                        </p>

                        <div class="choices">
                            <?php foreach (Game::getShipTypes() as $typeKey => $example) {
                                $isChosen = $chosen !== null && $chosen->getType() === $example->getType();
                            ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="choose">
                                    <input type="hidden" name="ship" value="<?= $playerId ?>">
                                    <input type="hidden" name="type" value="<?= $typeKey ?>">
                                    <button type="submit" class="choice <?= $isChosen ? 'choice--selected' : '' ?>">
                                        <img class="choice__img" src="<?= shipImage($example, $player['mod']) ?>" alt="<?= $example->getType() ?>">
                                        <span class="choice__type"><?= $example->getType() ?></span>
                                        <span class="choice__desc"><?= $example->getDescription() ?></span>
                                        <span class="choice__stats">
                                            <span>HP</span><span><?= $example->maxHitPoints ?></span>
                                            <span>Ammo</span><span><?= $example->maxAmmo ?></span>
                                            <span>Schade</span><span><?= $example->getShotDamage() ?> per schot</span>
                                            <span>Pantser</span><span><?= $example->getArmor() ?></span>
                                        </span>
                                    </button>
                                </form>
                            <?php } ?>
                        </div>
                    </article>
                <?php } ?>

                <form method="post">
                    <button type="submit" name="action" value="start" class="btn btn--start"
                        <?= $game->canStart() ? '' : 'disabled' ?>>
                        Start gevecht
                    </button>
                </form>

            <?php } else { ?>

                <!-- ===== Fase 2: het gevecht ===== -->
                <?php foreach ($game->getShips() as $shipId => $ship) {
                    $player = $players[$shipId];
                    // Mag dit schip nu iets doen? Alleen als het zijn beurt is en het gevecht nog bezig is.
                    $myTurn = !$gameOver && $game->isTurnOf($shipId);
                ?>
                    <article class="panel ship ship--<?= $player['mod'] ?> <?= $ship->isAlive ? '' : 'ship--dead' ?> <?= $myTurn ? 'ship--active' : 'ship--waiting' ?>">
                        <div class="ship__top">
                            <img class="ship__img" src="<?= shipImage($ship, $player['mod']) ?>" alt="<?= $ship->getType() ?>">
                            <div>
                                <h2 class="ship__name"><?= $player['name'] ?></h2>
                                <p class="ship__type"><?= $ship->getType() ?></p>
                                <span class="badge <?= $ship->isAlive ? 'badge--alive' : 'badge--dead' ?>">
                                    <?= $ship->isAlive ? 'Online' : 'Destroyed' ?>
                                </span>
                                <?php if ($ship->getArmor() > 0) { ?>
                                    <span class="badge badge--info">Pantser <?= $ship->getArmor() ?></span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="stat">
                            <div class="stat__label"><span>Hitpoints</span><span><?= $ship->hitPoints ?> / <?= $ship->maxHitPoints ?></span></div>
                            <div class="bar"><div class="bar__fill bar__fill--hp" style="width: <?= percentage($ship->hitPoints, $ship->maxHitPoints) ?>%"></div></div>
                        </div>
                        <div class="stat">
                            <div class="stat__label"><span>Ammo</span><span><?= $ship->ammo ?> / <?= $ship->maxAmmo ?></span></div>
                            <div class="bar"><div class="bar__fill bar__fill--ammo" style="width: <?= percentage($ship->ammo, $ship->maxAmmo) ?>%"></div></div>
                        </div>

                        <!-- De knop stuurt de actie en het schip mee naar de server -->
                        <form method="post" class="actions">
                            <input type="hidden" name="ship" value="<?= $shipId ?>">
                            <button type="submit" name="action" value="shoot" class="btn btn--shoot"
                                <?= (!$myTurn || $ship->ammo < $ship->getShotCost()) ? 'disabled' : '' ?>>
                                Schieten
                                <small><?= $ship->getShotDamage() ?> schade &middot; <?= $ship->getShotCost() ?> ammo</small>
                            </button>
                        </form>
                    </article>
                <?php } ?>

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

    <script>
        // Na een klik herlaadt de pagina en spring je normaal terug naar boven.
        // Daarom onthouden we vlak voor het versturen hoe ver je gescrold was, en zetten dat na het laden terug.
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                try { sessionStorage.setItem('scrollY', window.scrollY); } catch (e) {}
            });
        });

        try {
            const scrollY = sessionStorage.getItem('scrollY');
            if (scrollY !== null) {
                window.scrollTo(0, parseInt(scrollY));
                sessionStorage.removeItem('scrollY');
            }
        } catch (e) {}
    </script>
</body>

</html>
