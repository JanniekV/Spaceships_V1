<?php
include_once 'Spaceship.php';

// Tank is een child class van Spaceship (extends).
// Hij is zwaar en taai: veel HP, maar zijn sterke aanvallen laden langzaam op.
class Tank extends Spaceship
{
    // Eigen waarden. Die overschrijven de standaardwaarden van de parent class.
    protected string $type = 'Tank';

    public function __construct()
    {
        // Roep de constructor van de parent aan met de startwaarden van een Tank.
        // Volgorde: ammo, hitPoints
        parent::__construct(100, 160);
    }

    // Verplicht, omdat getDescription() abstract is in Spaceship.
    public function getDescription(): string
    {
        return 'Langzaam en zwaar met veel HP.';
    }

    // Verplicht, omdat createAttacks() abstract is in Spaceship.
    // new Attack(naam, beschrijving, schade, ammo, cooldown)
    protected function createAttacks(): array
    {
        return [
            'kanon'   => new Attack('Kanon', 'Betrouwbaar zwaar kanon', 15, 5, 0),
            'granaat' => new Attack('Granaat', 'Explodeert bij inslag', 30, 20, 4),
            'railgun' => new Attack('Railgun', 'Verwoestend, maar traag', 45, 45, 6),
        ];
    }
}
