<?php
// De Game class is de "regisseur" van het gevecht. Hij bevat de schepen van beide spelers, een logboek
// en houdt bij in welke fase het spel is (schepen kiezen of vechten) en wie er aan de beurt is.
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
        $this->addLog($this->getPlayerName($playerId) . ' kiest de ' . $ship->getType() . '.', 'move');
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

    // Het schip van $attackerId schiet op het schip van de tegenstander.
    public function shoot(string $attackerId): void
    {
        if (!$this->checkTurn($attackerId)) {
            return;
        }

        $attacker = $this->getShip($attackerId);
        $targetId = $this->getOpponentId($attackerId);
        $target = $this->getShip($targetId);

        $attackerName = $this->getName($attackerId);
        $targetName = $this->getName($targetId);

        // Data transport: shoot() geeft de schade terug, die geven we als parameter door aan hit().
        // Het maakt de Game class niet uit of het een Fighter of een Tank is: elk object voert
        // zijn eigen versie van shoot() en hit() uit (polymorfisme).
        $damage = $attacker->shoot();

        if ($damage === 0) {
            // Mislukte actie: de beurt blijft bij dit schip, zodat het iets anders kan proberen.
            $this->addLog("$attackerName heeft niet genoeg ammo om te schieten!", 'bad');
            return;
        }

        $damageTaken = $target->hit($damage);
        $this->addLog("$attackerName schiet op $targetName ($damage schade).", 'attack');

        if ($damageTaken < $damage) {
            $blocked = $damage - $damageTaken;
            $this->addLog("$targetName weert $blocked schade af!", 'good');
        }

        $this->addLog("$targetName heeft nog {$target->hitPoints} HP.", 'hit');

        if (!$target->isAlive) {
            $this->addLog("$targetName is vernietigd! $attackerName wint!", 'bad');
            return; // gevecht voorbij, geen nieuwe beurt meer
        }

        $this->nextTurn();
    }

    // Het schip van $shipId beweegt één keer.
    public function move(string $shipId): void
    {
        if (!$this->checkTurn($shipId)) {
            return;
        }

        $ship = $this->getShip($shipId);
        $name = $this->getName($shipId);

        if ($ship->fuel === 0) {
            $this->addLog("$name heeft geen fuel meer en kan niet bewegen!", 'bad');
            return;
        }

        $ship->move();
        $this->addLog("$name beweegt. Fuel over: {$ship->fuel}.", 'move');

        if ($ship->isEvading()) {
            $this->addLog("$name is klaar om de volgende aanval te ontwijken.", 'good');
        }

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

        // Kan het andere schip niets meer doen (geen ammo én geen fuel)? Dan slaat het zijn beurt over.
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

    // Kan dit schip nog iets doen? Het moet leven en genoeg ammo of fuel hebben.
    private function canAct(string $shipId): bool
    {
        $ship = $this->getShip($shipId);
        if ($ship === null || !$ship->isAlive) {
            return false;
        }
        return $ship->ammo >= $ship->getShotCost() || $ship->fuel > 0;
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
