<?php
include_once 'Spaceship.php';

// Fighter is een child class van Spaceship (extends).
// Hij erft alles van Spaceship, maar is agressief: hij doet veel schade per schot, maar heeft
// minder HP en geen pantser.
class Fighter extends Spaceship
{
    // Eigen waarden. Die overschrijven de standaardwaarden van de parent class.
    protected string $type = 'Fighter';
    protected int $shotCost = 5;
    protected int $damagePerShot = 5;  // 5 * 5 = 25 schade per schot

    public function __construct()
    {
        // Roep de constructor van de parent aan met de startwaarden van een Fighter.
        // Volgorde: ammo, hitPoints
        parent::__construct(100, 110);
    }

    // Verplicht, omdat getDescription() abstract is in Spaceship.
    public function getDescription(): string
    {
        return 'Snel en agressief. Doet veel schade per schot, maar heeft minder HP en geen pantser.';
    }
}
