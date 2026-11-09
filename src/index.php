<?php
// include_once zorgt ervoor dat Spaceship.php maar 1x wordt toegevoegd (voorkomt een include loop).
include_once 'Spaceship.php';

// Hulpfunctie: zet een waarde om naar een percentage (max 100) voor de statusbalken.
function percentage(int $value, int $max = 100): int
{
    return max(0, min(100, (int) round($value / $max * 100)));
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

        <!-- Logboek: hier staat alle uitvoer van de code, in volgorde -->
        <section class="panel log">
            <h2 class="panel__title">Mission log</h2>
            <div class="log__screen">
                <?php
                // Eerst een standaard instantie maken van het object (zonder parameters = standaard waarden).
                $ship1 = new Spaceship();
                echo "<p class='log__line'>Ship1 ammo: " . $ship1->ammo . "</p>";

                // Een tweede instantie, nu met eigen waarden.
                $ship2 = new Spaceship(50, 50, 50);
                echo "<p class='log__line'>Ship2 ammo: " . $ship2->ammo . "</p>";

                // Data transport: Ship1 schiet, de return-waarde vangen we op in $dmg.
                $dmg = $ship1->shoot();
                echo "<p class='log__line log__line--attack'>Ship1 shot and did damage: " . $dmg . "</p>";

                // De schade via de parameter doorgeven aan Ship2.
                $ship2->hit($dmg);

                // Aantonen dat het is gelukt.
                echo "<p class='log__line'>Ship1 ammo: " . $ship1->ammo . "</p>";
                echo "<p class='log__line log__line--hit'>Ship2 HP: " . $ship2->hitPoints . "</p>";
                if ($ship2->isAlive) {
                    echo "<p class='log__line log__line--good'>Ship2 is still alive!</p>";
                } else {
                    echo "<p class='log__line log__line--bad'>Ship2 is dead!</p>";
                }

                // Toon de fuel van ship1, laat ship1 1x bewegen en toon de fuel opnieuw.
                echo "<p class='log__line'>Ship1 fuel: " . $ship1->fuel . "</p>";
                $ship1->move();
                echo "<p class='log__line log__line--move'>Ship1 moves once</p>";
                echo "<p class='log__line'>Ship1 fuel: " . $ship1->fuel . "</p>";
                if ($ship1->fuel > 0) {
                    echo "<p class='log__line log__line--good'>Ship1 has fuel!</p>";
                } else {
                    echo "<p class='log__line log__line--bad'>Ship1 has no fuel!</p>";
                }

                echo "<p class='log__line log__line--end'>The end of the code has been reached.</p>";
                ?>
                <span class="cursor" aria-hidden="true"></span>
            </div>
        </section>

        <!-- Scheepskaarten: de eindstand van beide objecten -->
        <section class="ships">
            <?php
            $ships = [
                ['name' => 'Ship 1', 'object' => $ship1, 'img' => 'img/ship1.svg', 'mod' => 'blue'],
                ['name' => 'Ship 2', 'object' => $ship2, 'img' => 'img/ship2.svg', 'mod' => 'red'],
            ];

            foreach ($ships as $ship) {
                $s = $ship['object'];
            ?>
                <article class="panel ship ship--<?= $ship['mod'] ?> <?= $s->isAlive ? '' : 'ship--dead' ?>">
                    <div class="ship__top">
                        <img class="ship__img" src="<?= $ship['img'] ?>" alt="<?= $ship['name'] ?>">
                        <div>
                            <h2 class="ship__name"><?= $ship['name'] ?></h2>
                            <span class="badge <?= $s->isAlive ? 'badge--alive' : 'badge--dead' ?>">
                                <?= $s->isAlive ? 'Online' : 'Destroyed' ?>
                            </span>
                        </div>
                    </div>

                    <div class="stat">
                        <div class="stat__label"><span>Hitpoints</span><span><?= $s->hitPoints ?></span></div>
                        <div class="bar"><div class="bar__fill bar__fill--hp" style="width: <?= percentage($s->hitPoints) ?>%"></div></div>
                    </div>
                    <div class="stat">
                        <div class="stat__label"><span>Ammo</span><span><?= $s->ammo ?></span></div>
                        <div class="bar"><div class="bar__fill bar__fill--ammo" style="width: <?= percentage($s->ammo) ?>%"></div></div>
                    </div>
                    <div class="stat">
                        <div class="stat__label"><span>Fuel</span><span><?= $s->fuel ?></span></div>
                        <div class="bar"><div class="bar__fill bar__fill--fuel" style="width: <?= percentage($s->fuel) ?>%"></div></div>
                    </div>
                </article>
            <?php } ?>
        </section>

    </main>
</body>

</html>
