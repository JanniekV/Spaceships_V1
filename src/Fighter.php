<?php
include_once 'Spaceship.php';

// Fighter is een child class van Spaceship (extends).
// Hij erft alles van Spaceship, maar is agressief: sterke aanvallen die snel weer opladen,
// maar minder HP.
class Fighter extends Spaceship
{
    // Eigen waarden. Die overschrijven de standaardwaarden van de parent class.
    protected string $type = 'Fighter';

    public function __construct()
    {
        // Roep de constructor van de parent aan met de startwaarden van een Fighter.
        // Volgorde: ammo, hitPoints
        parent::__construct(100, 120);
    }

    // Verplicht, omdat getDescription() abstract is in Spaceship.
    public function getDescription(): string
    {
        return 'Snel en agressief maar kwetsbaar.';
    }

    // Verplicht, omdat createAttacks() abstract is in Spaceship.
    // new Attack(naam, beschrijving, schade, ammo, cooldown)
    protected function createAttacks(): array
    {
        return [
            'laser'  => new Attack('Laser', 'Snelle laserstraal', 20, 5, 0),
            'raket'  => new Attack('Raket', 'Zoekt zijn doel', 35, 25, 2),
            'salvo'  => new Attack('Salvo', 'Alles tegelijk afvuren', 55, 50, 4),
        ];
    }
}
