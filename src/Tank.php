<?php
include_once 'Spaceship.php';

// Tank is een child class van Spaceship (extends).
// Hij is zwaar en traag: veel HP, een groot kanon en pantser, maar weinig munitie en dure bewegingen.
class Tank extends Spaceship
{
    // Eigen waarden. Die overschrijven de standaardwaarden van de parent class.
    protected string $type = 'Tank';
    protected int $shotCost = 10;
    protected int $damagePerShot = 3;  // 10 * 3 = 30 schade per schot
    protected int $fuelUsage = 20;
    protected int $armor = 5;          // elke treffer doet 5 schade minder

    public function __construct()
    {
        // Roep de constructor van de parent aan met de startwaarden van een Tank.
        // Volgorde: ammo, fuel, hitPoints
        parent::__construct(80, 60, 160);
    }

    // Verplicht, omdat getDescription() abstract is in Spaceship.
    public function getDescription(): string
    {
        return 'Zwaar gepantserd met een groot kanon. Het pantser houdt bij elke treffer schade tegen, maar hij heeft weinig munitie.';
    }

    // Hit overschrijven: eerst het pantser van de schade aftrekken, daarna de normale hit() van de parent.
    // max(0, ...) zorgt ervoor dat de schade nooit negatief wordt.
    public function hit(int $damage): int
    {
        return parent::hit(max(0, $damage - $this->armor));
    }
}
