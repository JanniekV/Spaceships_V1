<?php
// De Spaceship class is de blauwdruk voor één ruimteschip.
// Elk schip heeft eigen waarden (ammo, fuel, hitpoints) en kan schieten, geraakt worden en bewegen.
// De Game class maakt hier twee objecten van en laat ze tegen elkaar vechten.
class Spaceship
{
    // ===== Properties =====
    // De eigenschappen van een schip. Ze zijn public, zodat de Game class en index.php ze kunnen
    // uitlezen (bijvoorbeeld voor de statusbalken).
    public bool $isAlive;   // true zolang het schip niet vernietigd is
    public int $fuel;       // brandstof, nodig om te bewegen
    public int $hitPoints;  // levenspunten, bij 0 is het schip vernietigd
    public int $ammo;       // munitie, nodig om te schieten

    // ===== Constructor =====
    // Wordt automatisch uitgevoerd bij "new Spaceship()".
    // De parameters hebben standaardwaarden: new Spaceship() geeft een schip met 100 ammo, 100 fuel
    // en 100 HP, en met new Spaceship(50, 50, 50) maak je een schip met eigen waarden.
    public function __construct(
        $ammo = 100,
        $fuel = 100,
        $hitPoints = 100
    ) {
        $this->ammo = $ammo;
        $this->fuel = $fuel;
        $this->hitPoints = $hitPoints;
        // isAlive is geen parameter: een nieuw schip begint altijd levend.
        $this->isAlive = true;
    }

    // ===== Methods =====

    // Shoot
    // Verbruikt munitie en geeft de schade terug die het schot doet.
    // De schade wordt niet hier aan de tegenstander gegeven: de Game class vangt de return-waarde op
    // en geeft die door aan hit() van het andere schip.
    // Returnt 0 als er niet genoeg munitie is. De Game class gebruikt dat om te zien dat het schot mislukt is.
    public function shoot(): int
    {
        $shot = 5;    // hoeveel munitie één schot kost
        $damage = 2;  // schade per eenheid munitie, één schot doet dus 5 * 2 = 10 schade

        // Alleen schieten als er na het schot niet minder dan 0 munitie over is.
        if ($this->ammo - $shot >= 0) {
            $this->ammo -= $shot; // korte schrijfwijze van $this->ammo = $this->ammo - $shot;
            return ($shot * $damage);
        } else {
            return 0;
        }
    }

    // Hit
    // Verwerkt de schade die dit schip oploopt. $damage komt van shoot() van het andere schip.
    // HP kan niet onder 0 komen: bij 0 HP wordt het schip vernietigd (isAlive = false).
    public function hit($damage)
    {
        if ($this->hitPoints - $damage > 0) {
            $this->hitPoints -= $damage;
        } else {
            $this->hitPoints = 0;
            $this->isAlive = false;
        }
    }

    // Move
    // Het schip beweegt en verbruikt daarvoor brandstof.
    // Is er minder brandstof dan nodig, dan wordt fuel 0 en kan het schip daarna niet meer bewegen.
    public function move()
    {
        $fuelUsage = 15; // hoeveel brandstof één beweging kost

        if ($this->fuel - $fuelUsage > 0) {
            $this->fuel -= $fuelUsage;
        } else {
            $this->fuel = 0;
        }
    }
}