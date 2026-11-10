<?php
include_once 'Spaceship.php';

// Fighter is een child class van Spaceship (extends).
// Hij erft alles van Spaceship, maar is snel en wendbaar: weinig HP, goedkoop bewegen, en na het
// bewegen ontwijkt hij de eerstvolgende aanval.
class Fighter extends Spaceship
{
    // Eigen waarden. Die overschrijven de standaardwaarden van de parent class.
    protected string $type = 'Fighter';
    protected int $shotCost = 5;
    protected int $damagePerShot = 4;  // 5 * 4 = 20 schade per schot
    protected int $fuelUsage = 10;

    // Alleen de Fighter heeft deze property, daarom staat hij hier en niet in Spaceship.
    private bool $evading = false;

    public function __construct()
    {
        // Roep de constructor van de parent aan met de startwaarden van een Fighter.
        // Volgorde: ammo, fuel, hitPoints
        parent::__construct(100, 100, 80);
    }

    // Verplicht, omdat getDescription() abstract is in Spaceship.
    public function getDescription(): string
    {
        return 'Snel en wendbaar. Na het bewegen ontwijkt hij de eerstvolgende aanval volledig.';
    }

    // Overschrijft de method uit Spaceship (die altijd false teruggeeft).
    public function isEvading(): bool
    {
        return $this->evading;
    }

    // Move overschrijven: eerst het normale bewegen uit de parent, daarna klaar om te ontwijken.
    public function move(): void
    {
        parent::move();
        $this->evading = true;
    }

    // Hit overschrijven: is de Fighter aan het ontwijken, dan komt de aanval niet aan.
    public function hit(int $damage): int
    {
        if ($this->evading) {
            $this->evading = false; // het ontwijken werkt maar voor één aanval
            return 0;
        }

        // Niet aan het ontwijken: gewoon de normale hit() van de parent gebruiken.
        return parent::hit($damage);
    }
}
