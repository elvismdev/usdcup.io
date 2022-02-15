<?php

namespace App\Entity;

use App\Repository\PriceHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups("priceHistory:read")
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

    /**
     * @ORM\Column(type="integer")
     */
    private $maxPriceAd;

    /**
     * @ORM\Column(type="integer")
     */
    private $minPriceAd;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $maxPriceAdUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $minPriceAdUrl;

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

    public function getMaxPriceAd(): ?int
    {
        return $this->maxPriceAd;
    }

    public function setMaxPriceAd(int $maxPriceAd): self
    {
        $this->maxPriceAd = $maxPriceAd;

        return $this;
    }

    public function getMinPriceAd(): ?int
    {
        return $this->minPriceAd;
    }

    public function setMinPriceAd(int $minPriceAd): self
    {
        $this->minPriceAd = $minPriceAd;

        return $this;
    }

    public function getMaxPriceAdUrl(): ?string
    {
        return $this->maxPriceAdUrl;
    }

    public function setMaxPriceAdUrl(string $maxPriceAdUrl): self
    {
        $this->maxPriceAdUrl = $maxPriceAdUrl;

        return $this;
    }

    public function getMinPriceAdUrl(): ?string
    {
        return $this->minPriceAdUrl;
    }

    public function setMinPriceAdUrl(string $minPriceAdUrl): self
    {
        $this->minPriceAdUrl = $minPriceAdUrl;

        return $this;
    }

}
