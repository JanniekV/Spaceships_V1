<?php
// Peilen voorkennis:
// Vooraf kan er aan de klas gevraagd worden:
// - Wie heeft er al eens met klassen gewerkt?
// - Wie weet waar de afkorting OOP voor staat?
// - Wie heeft er al eens (per ongeluk) mee gewerkt?
// - Heeft iemand al eens gehoord van bijvoorbeeld MVC of MVVM? Op stage bijvoorbeeld?
//
// Belangrijk is het om te zorgen dat studenten de meerwaarde gaan zien van het gebruiken van OOP. Het zorgt vooral
// in het leerproces een extra belasting op zowel het (voorbereidend) werk dat gedaan moet worden, als op het abstracte
// denkvermogen.
// Voordelen:
// - Minder code schrijven.
// - Hergebruik van werkende code.
// - Code is beter gestructureerd.
// - Code is altijd gemakkelijk te debuggen.
// - Code is gemakkelijker uit te breiden.

// Opdracht om aan te tonen dat we ook gewend zijn om in objecten te denken kan de studenten gevraagd worden om eens
// wat objecten te beschrijven in onderdelen en (indien mogelijk) gedrag. Voorbeelden zijn objecten in een klaslokaal,
// maar ook het klaslokaal zelf.
// Het vergelijk met een blauwdruk is gemakkelijk gemaakt en bijvoorbeeld woonwijken met huizen om ook een link te
// leggen met het initializeren van een object. Eenmaal een blauwdruk, kan de 'constructor' (het bouwbedrijf)
// vrij gemakkelijk nieuwe huizen neerzetten.

class Spaceship
{
    // Properties
    public bool $isAlive;
    public int $fuel;
    public int $hitPoints;
    public int $ammo;

    // Constructor
    // We maken gebruik van default values om het gebruikers (andere programmeurs) gemakkelijk te maken de code
    // te gebruiken. Daarnaast ontbreekt de mogelijkheid binnen PHP om de contructor een overload te geven met een
    // custom constructor.
    public function __construct(
        $ammo = 100,
        $fuel = 100,
        $hitPoints = 100
    ) {
        $this->ammo = $ammo;
        $this->fuel = $fuel;
        $this->hitPoints = $hitPoints;
        // We kunnen kiezen om isAlive niet als parameter mee te geven met het argument dat
        // er geen logische use-case is om deze mogelijkheid te bieden. En schip waar een instantie
        // van gemaakt wordt, moet levend zijn.
        $this->isAlive = true;
    }

    // Methods/Functions
    // Het gaat hier om hele simpele vormen van implementatie van het gedrag. Het doel is hier om vooral uit te leggen
    // dat er gedrag is van een object en hoe dit moet, niet om een heel ingewikkeld algoritme te bedenken of dit
    // in een uitgebreide applicatie te gebruiken.
    // Het bedenken van een functie bestaat uit een paar stappen. Allereerst, wat is een goede en niet te lange naam
    // voor de functie die beschrijft wat de functie doet. Daarna is de functie voor intern of extern
    // gebruik ten opzichte van het object? Ten slotte nadenken over of de functie alleen iets gaat uitvoeren waarbij
    // het resultaat niet gebruikt hoeft te worden ergens anders of juist wel. In het laatste geval maak je gebruik van
    // een returntype (en bedenk je welk type) en het keyword return. Zorg er in dit geval voor dat een functie altijd
    // de mogelijkheid heeft bij de return te komen (anders krijg je een melding).
    // Shoot
    public function shoot(): int
    {
        // Aparte variabelen gemaakt om self explaining code uit te leggen, geen magic numbers
        $shot = 5;
        $damage = 2;
        // Studenten laten bedenken hoe dit er een beetje uit kan komen te zien.
        // Standaard schade berekenen op 10 bijvoorbeeld.
        if ($this->ammo - $shot >= 0) {
            // short-hand code uitleggen
            $this->ammo -= $shot;
            // Hierboven is hetzelfde als:
            // $this->ammo =  $this->ammo - $shot;
            // Bij het bereiken van een return eindigt de uitvoer in de functie en 'springt' de code terug naar waar
            // de functie is aangeroepen.
            return ($shot * $damage);
        } else {
            // Hier zorgen we ervoor dat er altijd een waarde terug wordt gegeven.
            return 0;
        }
    }

    // Hit
    // Wat is een parameter? Waarom hier wel? Data moet van buiten de scope van de functie komen, dus hebben we
    // de parameter(s) nodig om dit voor elkaar te krijgen. Ze het als een soort doorgeefluik.
    // Bepaal van tevoren of je een parameter nodig hebt en waarom.
    public function hit($damage)
    {
        // Mogelijkheid 1:
        //        $this->hitPoints -= $damage;
        //        if($this->hitPoints <= 0)
        //        {
        //            $this->isAlive = false;
        //        }

        // Mogelijkheid 2 (gekozen om duidelijkheid):
        if ($this->hitPoints - $damage > 0) {
            $this->hitPoints -= $damage;
        } else {
            $this->isAlive = false;
        }
    }
    // Move
    // Studenten kunnen hier zelf op proberen te komen.
    public function move()
    {
        $fuelUsage = 2;
        if ($this->fuel - $fuelUsage > 0) {
            $this->fuel -= $fuelUsage;
        } else {
            $this->fuel = 0;
        }
    }
    // Get/Set
    // Deze zijn voor de volgende les en vanwege de public properties nog niet nodig.

}
