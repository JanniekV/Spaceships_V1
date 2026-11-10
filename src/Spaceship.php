<?php
include_once 'Attack.php';

// Spaceship is de parent class (basis-class) voor alle schepen.
// Hij is abstract: je kunt geen "los" Spaceship maken met new Spaceship(), alleen een specifiek type
// zoals een Fighter of een Tank. Die child classes erven (extends) alle properties en methods van
// deze class, en passen alleen aan wat bij hun type anders is.
abstract class Spaceship
{
    // ===== Constants =====
    // Een constant is een vaste waarde die nooit verandert. private: alleen deze class kan erbij,
    // dus een child class (Fighter of Tank) kan de reparatie niet sterker of zwakker maken.
    private const REPAIR_AMOUNT = 20;  // hoeveel HP één reparatie teruggeeft
    private const MAX_REPAIRS = 3;     // hoe vaak een schip mag repareren per gevecht

    // ===== Properties =====
    // public: de Game class en index.php mogen deze waarden uitlezen (bijvoorbeeld voor de statusbalken).
    public bool $isAlive;    // true zolang het schip niet vernietigd is
    public int $hitPoints;   // levenspunten, bij 0 is het schip vernietigd
    public int $ammo;        // munitie, nodig om aan te vallen
    public int $repairsLeft; // hoeveel reparaties er nog over zijn

    // De startwaarden. Die hebben we nodig om de statusbalken als percentage te tonen,
    // want een Tank heeft meer HP dan een Fighter.
    public int $maxHitPoints;
    public int $maxAmmo;

    // protected: alleen deze class en de child classes mogen hier bij.
    protected string $type = 'Spaceship';
    protected int $armor = 0;     // schade die het schip per treffer tegenhoudt
    protected array $attacks = []; // de Attack-objecten van dit schip, bijv. ['laser' => Attack]

    // ===== Constructor =====
    // Wordt aangeroepen door de constructor van de child class met parent::__construct(...).
    public function __construct(int $ammo = 100, int $hitPoints = 100)
    {
        $this->ammo = $ammo;
        $this->hitPoints = $hitPoints;

        $this->maxAmmo = $ammo;
        $this->maxHitPoints = $hitPoints;

        // self:: gebruik je om een constant van deze class op te halen.
        $this->repairsLeft = self::MAX_REPAIRS;

        // isAlive is geen parameter: een nieuw schip begint altijd levend.
        $this->isAlive = true;

        // Elke child class bepaalt zelf welke aanvallen hij heeft.
        $this->attacks = $this->createAttacks();

        // Regel: elk schip moet minstens één aanval zonder cooldown hebben, zodat een speler nooit
        // vast komt te zitten. Klopt dat niet, dan stopt PHP met een duidelijke foutmelding.
        if (!$this->hasAttackWithoutCooldown()) {
            throw new Exception($this->type . ' moet minstens één aanval zonder cooldown hebben.');
        }
    }

    // ===== Abstracte methods =====
    // Elke child class MOET deze methods zelf invullen, anders geeft PHP een foutmelding.
    abstract public function getDescription(): string;

    // Geeft een array met Attack-objecten terug. De keys gebruiken we in het formulier.
    abstract protected function createAttacks(): array;

    // ===== Getters =====
    public function getType(): string
    {
        return $this->type;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    public function getAttacks(): array
    {
        return $this->attacks;
    }

    public function getAttack(string $attackKey): ?Attack
    {
        return $this->attacks[$attackKey] ?? null;
    }

    public function getRepairAmount(): int
    {
        return self::REPAIR_AMOUNT;
    }

    public function getMaxRepairs(): int
    {
        return self::MAX_REPAIRS;
    }

    // Kan deze aanval nu gebruikt worden? Hij moet bestaan, opgeladen zijn en er moet genoeg ammo zijn.
    public function canUseAttack(string $attackKey): bool
    {
        $attack = $this->getAttack($attackKey);

        return $this->isAlive
            && $attack !== null
            && $attack->isReady()
            && $this->ammo >= $attack->getAmmoCost();
    }

    // Is er minstens één aanval die nu gebruikt kan worden?
    public function canShoot(): bool
    {
        foreach (array_keys($this->attacks) as $attackKey) {
            if ($this->canUseAttack($attackKey)) {
                return true;
            }
        }
        return false;
    }

    // Kan het schip repareren? Het moet leven, reparaties over hebben en schade hebben.
    public function canRepair(): bool
    {
        return $this->isAlive && $this->repairsLeft > 0 && $this->hitPoints < $this->maxHitPoints;
    }

    // ===== Methods =====

    // Shoot
    // Voert de gekozen aanval uit en geeft de schade terug. De Game class geeft die schade door aan
    // hit() van het andere schip. Returnt 0 als de aanval nu niet gebruikt kan worden.
    public function shoot(string $attackKey): int
    {
        if (!$this->canUseAttack($attackKey)) {
            return 0;
        }

        $attack = $this->getAttack($attackKey);
        $this->ammo -= $attack->getAmmoCost();

        // Eerst laden alle aanvallen een beurt verder op, daarna gaat de gebruikte aanval in cooldown.
        // In deze volgorde, anders zou de net gebruikte aanval meteen al een beurt korter wachten.
        $this->tickCooldowns();
        $attack->startCooldown();

        return $attack->getDamage();
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

    // Repair
    // Geeft het schip HP terug en geeft terug hoeveel HP er echt bij kwam (0 als repareren niet kan).
    // final: een child class mag deze method NIET overschrijven. Zo werkt repareren voor elk type
    // schip precies hetzelfde.
    // HP kan niet boven het maximum komen: min() pakt de kleinste van de twee waarden.
    final public function repair(): int
    {
        if (!$this->canRepair()) {
            return 0;
        }

        $before = $this->hitPoints;
        $this->hitPoints = min($this->hitPoints + self::REPAIR_AMOUNT, $this->maxHitPoints);
        $this->repairsLeft--;

        // Repareren kost ook een beurt, dus de aanvallen laden verder op.
        $this->tickCooldowns();

        return $this->hitPoints - $before;
    }

    // ===== Hulpmethods (alleen voor intern gebruik) =====

    // Na elke eigen beurt laadt elke aanval één beurt verder op.
    private function tickCooldowns(): void
    {
        foreach ($this->attacks as $attack) {
            $attack->tickCooldown();
        }
    }

    private function hasAttackWithoutCooldown(): bool
    {
        foreach ($this->attacks as $attack) {
            if ($attack->getCooldown() === 0) {
                return true;
            }
        }
        return false;
    }
}
