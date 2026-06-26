<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class VacationRentalData extends Data
{
    public function __construct(
        public string $name,
        public string $description,
        public string $url,
        public string $language = 'de-DE',
        public int $rooms,
        public int $bedrooms,
        public int $livingSpace,
        public int $maxGuests,
        public bool $petsAllowed = false,
        public bool $smokingAllowed = false,
        public string $checkIn,
        public string $checkOut,
        public ?string $priceRange = null,
        public AddressData $address,
        public GeoData $geo,
        public array $images = [],
        public array $amenityFeatures = [],
    ) {
    }
}