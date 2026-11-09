<?php
// De Game class is de "regisseur" van het gevecht. Hij bevat de twee Spaceship-objecten en een logboek.
// De Spaceship class weet hoe één schip schiet, geraakt wordt en beweegt. De Game class weet hoe twee
// schepen met elkaar samenwerken (wie schiet op wie). Zo heeft elke class één duidelijke taak.
include_once 'Spaceship.php';

class Game
{
    // Properties
    // private: alleen de Game class zelf mag hier direct aan komen. Van buitenaf gebruik je de methods.
    private array $ships = [];
    private array $log = [];

    // Constructor: maakt een nieuw gevecht met twee schepen (instanties van de blauwdruk Spaceship).
    public function __construct()
    {
        $this->ships['ship1'] = new Spaceship();            // standaard waarden (100, 100, 100)
        $this->ships['ship2'] = new Spaceship();  
        $this->addLog('Het gevecht begint! Ship 1 en Ship 2 staan klaar.', 'end');
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

    // ===== Acties =====

    // Het schip met $attackerId schiet op het andere schip.
    public function shoot(string $attackerId): void
    {
        $attacker = $this->getShip($attackerId);
        $target = $this->getShip($this->getOpponentId($attackerId));

        if ($attacker === null || $target === null) {
            return;
        }

        $attackerName = $this->getName($attackerId);
        $targetName = $this->getName($this->getOpponentId($attackerId));

        if (!$attacker->isAlive) {
            $this->addLog("$attackerName is vernietigd en kan niet meer schieten.", 'bad');
            return;
        }
        if (!$target->isAlive) {
            $this->addLog("$targetName is al vernietigd.", 'bad');
            return;
        }

        // Data transport: shoot() geeft de schade terug, die geven we als parameter door aan hit().
        $damage = $attacker->shoot();

        if ($damage === 0) {
            $this->addLog("$attackerName heeft niet genoeg ammo om te schieten!", 'bad');
            return;
        }

        $target->hit($damage);
        $this->addLog("$attackerName schiet op $targetName en doet $damage schade.", 'attack');
        $this->addLog("$targetName heeft nog {$target->hitPoints} HP.", 'hit');

        if (!$target->isAlive) {
            $this->addLog("$targetName is vernietigd! $attackerName wint!", 'bad');
        }
    }

    // Het schip met $shipId beweegt één keer.
    public function move(string $shipId): void
    {
        $ship = $this->getShip($shipId);

        if ($ship === null) {
            return;
        }

        $name = $this->getName($shipId);

        if (!$ship->isAlive) {
            $this->addLog("$name is vernietigd en kan niet meer bewegen.", 'bad');
            return;
        }
        if ($ship->fuel === 0) {
            $this->addLog("$name heeft geen fuel meer en kan niet bewegen!", 'bad');
            return;
        }

        $ship->move();
        $this->addLog("$name beweegt. Fuel over: {$ship->fuel}.", 'move');
    }

    // ===== Hulpmethods (alleen voor intern gebruik, daarom private) =====

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
