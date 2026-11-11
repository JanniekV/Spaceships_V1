<?php
// De Game class is de "regisseur" van het gevecht. Hij bevat de schepen van beide spelers, een logboek
// en houdt bij in welke fase het spel is (schepen kiezen of vechten) en wie er aan de beurt is.
include_once 'Attack.php';
include_once 'Spaceship.php';
include_once 'Fighter.php';
include_once 'Tank.php';

class Game
{
    // ===== Properties =====
    // Aan het begin heeft nog geen speler een schip gekozen, daarom staat er null.
    private array $ships = ['ship1' => null, 'ship2' => null];
    private array $log = [];
    private bool $started = false;
    private string $currentTurn = 'ship1'; // Speler 1 begint
    private int $round = 1;

    public function __construct()
    {
        $this->addLog('Kies allebei een schip om het gevecht te beginnen.', 'end');
    }

    // ===== Schepen kiezen =====

    // Alle types waaruit gekozen kan worden, met een voorbeeld-object om de eigenschappen te tonen.
    // static: je roept deze method aan op de class zelf (Game::getShipTypes()), zonder object.
    public static function getShipTypes(): array
    {
        return [
            'fighter' => new Fighter(),
            'tank' => new Tank(),
        ];
    }

    // Een speler kiest een type schip. Zolang het spel nog niet begonnen is, mag je van keuze wisselen.
    public function chooseShip(string $playerId, string $type): void
    {
        if ($this->started || !array_key_exists($playerId, $this->ships)) {
            return;
        }

        $ship = $this->createShip($type);
        if ($ship === null) {
            return;
        }

        $this->ships[$playerId] = $ship;
        $this->addLog($this->getPlayerName($playerId) . ' kiest de ' . $ship->getType() . '.', 'choose');
    }

    // Het spel kan pas beginnen als beide spelers een schip hebben gekozen.
    public function canStart(): bool
    {
        return !$this->started && !in_array(null, $this->ships, true);
    }

    public function start(): void
    {
        if (!$this->canStart()) {
            return;
        }

        $this->started = true;
        $this->addLog('Het gevecht begint!', 'end');
        $this->addLog('Ronde 1: ' . $this->getName('ship1') . ' is aan de beurt.', 'good');
    }

    // ===== Getters =====
    public function isStarted(): bool
    {
        return $this->started;
    }

    public function getShips(): array
    {
        return $this->ships;
    }

    public function getShip(string $shipId): ?Spaceship
    {
        // ?? geeft null terug als de speler niet bestaat (bijv. bij een verkeerde waarde uit het formulier).
        return $this->ships[$shipId] ?? null;
    }

    public function getLog(): array
    {
        // Nieuwste regel bovenaan.
        return array_reverse($this->log);
    }

    public function getCurrentTurn(): string
    {
        return $this->currentTurn;
    }

    public function getRound(): int
    {
        return $this->round;
    }

    public function isTurnOf(string $shipId): bool
    {
        return $this->currentTurn === $shipId;
    }

    // Het gevecht is voorbij als een schip vernietigd is, of als geen enkel schip nog iets kan doen.
    public function isGameOver(): bool
    {
        if (!$this->started) {
            return false;
        }
        foreach ($this->ships as $ship) {
            if (!$ship->isAlive) {
                return true;
            }
        }
        return !$this->canAct('ship1') && !$this->canAct('ship2');
    }

    // ===== Acties =====

    // Het schip van $attackerId valt het schip van de tegenstander aan met de gekozen aanval.
    public function shoot(string $attackerId, string $attackKey): void
    {
        if (!$this->checkTurn($attackerId)) {
            return;
        }

        $attacker = $this->getShip($attackerId);
        $attack = $attacker->getAttack($attackKey);
        $targetId = $this->getOpponentId($attackerId);
        $target = $this->getShip($targetId);

        $attackerName = $this->getName($attackerId);
        $targetName = $this->getName($targetId);

        if ($attack === null) {
            return;
        }

        // Mislukte actie: de beurt blijft bij dit schip, zodat het iets anders kan kiezen.
        if (!$attack->isReady()) {
            $this->addLog($attack->getName() . " laadt nog {$attack->getRemainingCooldown()} beurt(en) op!", 'bad');
            return;
        }
        if ($attacker->ammo < $attack->getAmmoCost()) {
            $this->addLog("$attackerName heeft niet genoeg ammo voor " . $attack->getName() . '!', 'bad');
            return;
        }

        // Data transport: shoot() geeft de schade terug, die geven we als parameter door aan hit().
        // Het maakt de Game class niet uit of het een Fighter of een Tank is: elk object heeft
        // zijn eigen aanvallen, maar ze worden op dezelfde manier gebruikt (polymorfisme).
        $damage = $attacker->shoot($attackKey);

        $target->hit($damage);
        $this->addLog("$attackerName gebruikt " . $attack->getName() . " op $targetName ($damage schade).", 'attack');

        $this->addLog("$targetName heeft nog {$target->hitPoints} HP.", 'hit');

        if (!$target->isAlive) {
            $this->addLog("$targetName is vernietigd! $attackerName wint!", 'bad');
            return; // gevecht voorbij, geen nieuwe beurt meer
        }

        $this->nextTurn();
    }

    // Het schip van $shipId repareert zichzelf. Dit kost een beurt.
    public function repair(string $shipId): void
    {
        if (!$this->checkTurn($shipId)) {
            return;
        }

        $ship = $this->getShip($shipId);
        $name = $this->getName($shipId);

        // Mislukte actie: de beurt blijft bij dit schip, zodat het iets anders kan doen.
        if ($ship->repairsLeft === 0) {
            $this->addLog("$name heeft geen reparaties meer over!", 'bad');
            return;
        }
        if ($ship->hitPoints === $ship->maxHitPoints) {
            $this->addLog("$name is niet beschadigd en hoeft niet te repareren.", 'bad');
            return;
        }

        $repaired = $ship->repair();
        $this->addLog("$name repareert zichzelf: +$repaired HP (nu {$ship->hitPoints} HP, nog {$ship->repairsLeft} reparaties over).", 'repair');

        $this->nextTurn();
    }

    // Het schip van $shipId vult zijn ammo helemaal bij. Dit kost een beurt en kan 1x per match.
    public function reload(string $shipId): void
    {
        if (!$this->checkTurn($shipId)) {
            return;
        }

        $ship = $this->getShip($shipId);
        $name = $this->getName($shipId);

        // Mislukte actie: de beurt blijft bij dit schip, zodat het iets anders kan doen.
        if ($ship->reloadsLeft === 0) {
            $this->addLog("$name heeft al herladen deze match!", 'bad');
            return;
        }
        if ($ship->ammo === $ship->maxAmmo) {
            $this->addLog("$name heeft al volle ammo.", 'bad');
            return;
        }

        $added = $ship->reload();
        $this->addLog("$name herlaadt: +$added ammo (nu {$ship->ammo} ammo).", 'reload');

        $this->nextTurn();
    }

    // ===== Hulpmethods (alleen voor intern gebruik, daarom private) =====

    // Maakt een nieuw object van het gekozen type.
    private function createShip(string $type): ?Spaceship
    {
        switch ($type) {
            case 'fighter':
                return new Fighter();
            case 'tank':
                return new Tank();
            default:
                return null;
        }
    }

    // Controleert of dit schip nu een actie mag doen. Zo niet, dan komt er een melding in het logboek.
    private function checkTurn(string $shipId): bool
    {
        if (!$this->started || $this->getShip($shipId) === null || $this->isGameOver()) {
            return false;
        }

        if (!$this->isTurnOf($shipId)) {
            $this->addLog($this->getName($shipId) . ' is niet aan de beurt!', 'bad');
            return false;
        }

        return true;
    }

    // Geeft de beurt aan het andere schip.
    private function nextTurn(): void
    {
        $next = $this->getOpponentId($this->currentTurn);

        // Kan het andere schip niet schieten, repareren of herladen? Dan slaat het zijn beurt over.
        if (!$this->canAct($next)) {
            if ($this->isGameOver()) {
                $this->addLog('Geen enkel schip kan nog iets doen. Gelijkspel!', 'end');
                return;
            }
            $this->addLog($this->getName($next) . ' kan niets meer doen en slaat zijn beurt over.', 'end');
            $next = $this->currentTurn;
        }

        // Is speler 2 net klaar? Dan begint er een nieuwe ronde.
        if ($this->currentTurn === 'ship2') {
            $this->round++;
        }

        $this->currentTurn = $next;
        $this->addLog('Ronde ' . $this->round . ': ' . $this->getName($next) . ' is aan de beurt.', 'good');
    }

    // Kan dit schip nog iets doen? Schieten, repareren of herladen.
    private function canAct(string $shipId): bool
    {
        $ship = $this->getShip($shipId);
        if ($ship === null) {
            return false;
        }
        return $ship->canShoot() || $ship->canRepair() || $ship->canReload();
    }

    private function getOpponentId(string $shipId): string
    {
        return $shipId === 'ship1' ? 'ship2' : 'ship1';
    }

    // 'ship1' wordt 'Speler 1'
    private function getPlayerName(string $shipId): string
    {
        return 'Speler ' . str_replace('ship', '', $shipId);
    }

    // 'ship1' met een Tank wordt 'Speler 1 (Tank)'
    private function getName(string $shipId): string
    {
        $ship = $this->getShip($shipId);
        $name = $this->getPlayerName($shipId);
        return $ship === null ? $name : $name . ' (' . $ship->getType() . ')';
    }

    private function addLog(string $message, string $type = ''): void
    {
        $this->log[] = ['message' => $message, 'type' => $type];
    }
}
