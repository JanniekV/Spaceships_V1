<?php
// Spaceship is de parent class (basis-class) voor alle schepen.
// Hij is abstract: je kunt geen "los" Spaceship maken met new Spaceship(), alleen een specifiek type
// zoals een Fighter of een Tank. Die child classes erven (extends) alle properties en methods van
// deze class, en passen alleen aan wat bij hun type anders is.
abstract class Spaceship
{
    // ===== Properties =====
    // public: de Game class en index.php mogen deze waarden uitlezen (bijvoorbeeld voor de statusbalken).
    public bool $isAlive;   // true zolang het schip niet vernietigd is
    public int $fuel;       // brandstof, nodig om te bewegen
    public int $hitPoints;  // levenspunten, bij 0 is het schip vernietigd
    public int $ammo;       // munitie, nodig om te schieten

    // De startwaarden. Die hebben we nodig om de statusbalken als percentage te tonen,
    // want een Tank heeft meer HP dan een Fighter.
    public int $maxFuel;
    public int $maxHitPoints;
    public int $maxAmmo;

    // protected: alleen deze class en de child classes mogen hier bij.
    // Een child class overschrijft deze waarden om zich anders te gedragen.
    protected string $type = 'Spaceship';
    protected int $shotCost = 5;       // hoeveel munitie één schot kost
    protected int $damagePerShot = 2;  // schade per eenheid munitie
    protected int $fuelUsage = 15;     // hoeveel brandstof één beweging kost
    protected int $armor = 0;          // schade die het schip per treffer tegenhoudt

    // ===== Constructor =====
    // Wordt aangeroepen door de constructor van de child class met parent::__construct(...).
    public function __construct(int $ammo = 100, int $fuel = 100, int $hitPoints = 100)
    {
        $this->ammo = $ammo;
        $this->fuel = $fuel;
        $this->hitPoints = $hitPoints;

        $this->maxAmmo = $ammo;
        $this->maxFuel = $fuel;
        $this->maxHitPoints = $hitPoints;

        // isAlive is geen parameter: een nieuw schip begint altijd levend.
        $this->isAlive = true;
    }

    // ===== Abstracte method =====
    // Elke child class MOET deze method zelf invullen, anders geeft PHP een foutmelding.
    // Zo weten we zeker dat elk type schip een beschrijving heeft.
    abstract public function getDescription(): string;

    // ===== Getters =====
    public function getType(): string
    {
        return $this->type;
    }

    public function getShotCost(): int
    {
        return $this->shotCost;
    }

    public function getShotDamage(): int
    {
        return $this->shotCost * $this->damagePerShot;
    }

    public function getFuelUsage(): int
    {
        return $this->fuelUsage;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    // Standaard ontwijkt een schip niets. De Fighter overschrijft deze method.
    public function isEvading(): bool
    {
        return false;
    }

    // ===== Methods =====

    // Shoot
    // Verbruikt munitie en geeft de schade terug die het schot doet.
    // De Game class geeft die schade door aan hit() van het andere schip.
    // Returnt 0 als er niet genoeg munitie is.
    public function shoot(): int
    {
        if ($this->ammo - $this->shotCost >= 0) {
            $this->ammo -= $this->shotCost;
            return $this->getShotDamage();
        } else {
            return 0;
        }
    }

    // Hit
    // Verwerkt de schade die dit schip oploopt en geeft terug hoeveel schade er echt aankwam.
    // Child classes kunnen deze method overschrijven (de Tank heeft bijvoorbeeld pantser).
    // HP kan niet onder 0 komen: bij 0 HP wordt het schip vernietigd (isAlive = false).
    public function hit(int $damage): int
    {
        if ($this->hitPoints - $damage > 0) {
            $this->hitPoints -= $damage;
        } else {
            $this->hitPoints = 0;
            $this->isAlive = false;
        }
        return $damage;
    }

    // Move
    // Het schip beweegt en verbruikt daarvoor brandstof.
    // Is er minder brandstof dan nodig, dan wordt fuel 0 en kan het schip daarna niet meer bewegen.
    public function move(): void
    {
        if ($this->fuel - $this->fuelUsage > 0) {
            $this->fuel -= $this->fuelUsage;
        } else {
            $this->fuel = 0;
        }
    }
}
