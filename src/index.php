<?php
// Om te zorgen dat de code in dit bestand bij de code in de class kan voegen we onderstaande code toe. Gelieve hier
// de include_once te gebruiken. Dit zorgt voor een extra stukje bescherming tegen fouten, namelijk dat het bestand
// maar 1x wordt toegevoegd. Dus als de code al een keer is toegevoegd, dan wordt dat niet nog een keer gedaan. Een
// zogenaamde include loop wordt hiermee voorkomen.
include_once 'xxxx'
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <p style="font-family: Cambria; font-size: 40px;">
        <?php
        // Eerst een standaard instantie maken van het object
        // Hoe moet het? Wat gebeurt er? Er kan hier verteld worden dat er een nieuwe kopie wordt gemaakt van de
        // blauwdruk Spaceship. Door geen parameters (data) mee te geven, wordt er een standaard kopie gemaakt. Een
        // analogie die gemaakt kan worden is met een printer, je maakt een blauwdruk van een document, dat print je
        // 10x. Als je op de eerste kopie een kras zet met een pen, verschijnt die kras dan ook op de overige 9 kopieën?
        $ship1 = new ; // spaceship? 


        // Haal simpel een eigenschap op (public) en toon dit.
        echo $ship1-> "<br>";   // ammmo
        // Maak een tweede instantie nu custom.
        $ship2 = new xxxxx(50, 50, 50);
        // Laten zien dat er nu ook een andere waarde komt.
        echo $ship2->ammo . "<br>";

        // Data transport. Ship1 schiet, return moet niet worden vergeten.
        // Variabele expres dmg genoemd ipv damage om te laten zien dat het om de data gaat, niet om de naam.
        $dmg = $ship1->shoot();
        // Hoe krijgen we de data bij Ship2? Daarom de parameter.
        $ship2->hit($dmg);

        // Aantonen dat het is gelukt.
        echo "Ship1 ammo: " . $ship1->ammo . "<br>";
        echo "Ship2 HP: " . $ship2->hitPoints . "<br>";


        echo "The end of the code has been reached.<br>";
        ?>
    </p>
</body>

</html>