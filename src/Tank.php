<?php
include_once 'Spaceship.php';

// Tank is een child class van Spaceship (extends).
// Hij is zwaar en taai: veel HP en pantser, maar zijn sterke aanvallen laden langzaam op.
class Tank extends Spaceship
{
    // Eigen waarden. Die overschrijven de standaardwaarden van de parent class.
    protected string $type = 'Tank';
    protected int $armor = 5; // elke treffer doet 5 schade minder

    public function __construct()
    {
        // Roep de constructor van de parent aan met de startwaarden van een Tank.
        // Volgorde: ammo, hitPoints
        parent::__construct(100, 160);
    }

    // Verplicht, omdat getDescription() abstract is in Spaceship.
    public function getDescription(): string
    {
        return 'Langzaam en zwaar gepantserd met veel HP.';
    }

    // Verplicht, omdat createAttacks() abstract is in Spaceship.
    // new Attack(naam, beschrijving, schade, ammo, cooldown)
    protected function createAttacks(): array
    {
        return [
            'kanon'   => new Attack('Kanon', 'Betrouwbaar zwaar kanon', 15, 5, 0),
            'granaat' => new Attack('Granaat', 'Explodeert bij inslag', 30, 10, 3),
            'railgun' => new Attack('Railgun', 'Verwoestend, maar traag', 45, 20, 5),
        ];
    }

    // Hit overschrijven: eerst het pantser van de schade aftrekken, daarna de normale hit() van de parent.
    // max(0, ...) zorgt ervoor dat de schade nooit negatief wordt.
    public function hit(int $damage): int
    {
        return parent::hit(max(0, $damage - $this->armor));
    }
}
