<?php

namespace App\Entity;

use App\Repository\PriceHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=PriceHistoryRepository::class)
 */
class PriceHistory
{

    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $currency;

    /**
     * @ORM\Column(type="float")
     */
    private $closingPrice;

    /**
     * @ORM\Column(type="integer")
     */
    private $adsPricesEval;

    /**
     * @ORM\Column(type="bigint")
     */
    private $unixCreatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getClosingPrice(): ?float
    {
        return $this->closingPrice;
    }

    public function setClosingPrice(float $closingPrice): self
    {
        $this->closingPrice = $closingPrice;

        return $this;
    }

    public function getAdsPricesEval(): ?int
    {
        return $this->adsPricesEval;
    }

    public function setAdsPricesEval(int $adsPricesEval): self
    {
        $this->adsPricesEval = $adsPricesEval;

        return $this;
    }

    public function getUnixCreatedAt(): ?string
    {
        return $this->unixCreatedAt;
    }

    public function setUnixCreatedAt(string $unixCreatedAt): self
    {
        $this->unixCreatedAt = $unixCreatedAt;

        return $this;
    }

}
