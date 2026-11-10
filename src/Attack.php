<?php
// Attack is de blauwdruk voor één aanval van een schip.
// Elk schip heeft meerdere Attack-objecten. Een aanval weet zelf hoeveel schade hij doet, hoeveel
// munitie hij kost en hoeveel beurten hij moet opladen (cooldown) nadat hij gebruikt is.
class Attack
{
    // ===== Properties =====
    // private: alleen de Attack class zelf mag deze waarden aanpassen. Van buitenaf lees je ze met getters.
    private string $name;
    private string $description;
    private int $damage;
    private int $ammoCost;
    private int $cooldown;              // hoeveel eigen beurten de aanval na gebruik niet beschikbaar is
    private int $remainingCooldown = 0; // hoeveel beurten hij nu nog moet opladen (0 = klaar)

    public function __construct(string $name, string $description, int $damage, int $ammoCost, int $cooldown = 0)
    {
        $this->name = $name;
        $this->description = $description;
        $this->damage = $damage;
        $this->ammoCost = $ammoCost;
        $this->cooldown = $cooldown;
    }

    // ===== Getters =====
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function getAmmoCost(): int
    {
        return $this->ammoCost;
    }

    public function getCooldown(): int
    {
        return $this->cooldown;
    }

    public function getRemainingCooldown(): int
    {
        return $this->remainingCooldown;
    }

    // Is de aanval klaar voor gebruik?
    public function isReady(): bool
    {
        return $this->remainingCooldown === 0;
    }

    // ===== Methods =====

    // De aanval is net gebruikt: hij moet nu opladen.
    public function startCooldown(): void
    {
        $this->remainingCooldown = $this->cooldown;
    }

    // Er is een beurt voorbij: de aanval laadt één beurt verder op.
    public function tickCooldown(): void
    {
        if ($this->remainingCooldown > 0) {
            $this->remainingCooldown--;
        }
    }
}
