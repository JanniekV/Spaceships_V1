<?php
// De Game class is de "regisseur" van het gevecht. Hij bevat de twee Spaceship-objecten, een logboek
// en houdt bij wie er aan de beurt is.
// De Spaceship class weet hoe één schip schiet, geraakt wordt en beweegt. De Game class weet hoe twee
// schepen met elkaar samenwerken (wie schiet op wie, wie is aan de beurt). Zo heeft elke class één taak.
include_once 'Spaceship.php';

class Game
{
    // Properties
    // private: alleen de Game class zelf mag hier direct aan komen. Van buitenaf gebruik je de methods.
    private array $ships = [];
    private array $log = [];
    private string $currentTurn = 'ship1'; // Ship 1 begint
    private int $round = 1;

    // Constructor: maakt een nieuw gevecht met twee schepen (instanties van de blauwdruk Spaceship).
    public function __construct()
    {
        $this->ships['ship1'] = new Spaceship();            // standaard waarden (100, 100, 100)
        $this->ships['ship2'] = new Spaceship();  // eigen waarden
        $this->addLog('Het gevecht begint! Ship 1 is als eerste aan de beurt.', 'end');
    }

    // ===== Getters =====
    public function getShips(): array
    {
        return $this->ships;
    }

    public function getShip(string $shipId): ?Spaceship
    {
        // ?? geeft null terug als het schip niet bestaat (bijv. bij een verkeerde waarde uit het formulier).
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
        foreach ($this->ships as $ship) {
            if (!$ship->isAlive) {
                return true;
            }
        }
        return !$this->canAct('ship1') && !$this->canAct('ship2');
    }

    // ===== Acties =====

    // Het schip met $attackerId schiet op het andere schip.
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
        $damage = $attacker->shoot();

        if ($damage === 0) {
            // Mislukte actie: de beurt blijft bij dit schip, zodat het iets anders kan proberen.
            $this->addLog("$attackerName heeft niet genoeg ammo om te schieten!", 'bad');
            return;
        }

        $target->hit($damage);
        $this->addLog("$attackerName schiet op $targetName en doet $damage schade.", 'attack');
        $this->addLog("$targetName heeft nog {$target->hitPoints} HP.", 'hit');

        if (!$target->isAlive) {
            $this->addLog("$targetName is vernietigd! $attackerName wint!", 'bad');
            return; // gevecht voorbij, geen nieuwe beurt meer
        }

        $this->nextTurn();
    }

    // Het schip met $shipId beweegt één keer.
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

        $this->nextTurn();
    }

    // ===== Hulpmethods (alleen voor intern gebruik, daarom private) =====

    // Controleert of dit schip nu een actie mag doen. Zo niet, dan komt er een melding in het logboek.
    private function checkTurn(string $shipId): bool
    {
        if ($this->getShip($shipId) === null || $this->isGameOver()) {
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

        // Is ship2 net klaar? Dan begint er een nieuwe ronde.
        if ($this->currentTurn === 'ship2') {
            $this->round++;
        }

        $this->currentTurn = $next;
        $this->addLog('Ronde ' . $this->round . ': ' . $this->getName($next) . ' is aan de beurt.', 'good');
    }

    // Kan dit schip nog iets doen? Het moet leven en ammo of fuel hebben.
    private function canAct(string $shipId): bool
    {
        $ship = $this->getShip($shipId);
        return $ship->isAlive && ($ship->ammo > 0 || $ship->fuel > 0);
    }

    private function getOpponentId(string $shipId): string
    {
        return $shipId === 'ship1' ? 'ship2' : 'ship1';
    }

    private function getName(string $shipId): string
    {
        // 'ship1' wordt 'Ship 1'
        return ucfirst(str_replace('ship', 'ship ', $shipId));
    }

    private function addLog(string $message, string $type = ''): void
    {
        $this->log[] = ['message' => $message, 'type' => $type];
    }
}
