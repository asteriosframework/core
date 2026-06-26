<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Node;

use Asterios\Core\Seo\Schema\Contracts\Node;
use Asterios\Core\Seo\Schema\Data\AmenityData;
use Asterios\Core\Seo\Schema\Data\ImageData;
use Asterios\Core\Seo\Schema\Data\VacationRentalData;
use Asterios\Core\Seo\Schema\Enums\SchemaIdEnum;

final readonly class VacationRental implements Node
{
    public function __construct(
        private VacationRentalData $data,
    ) {
    }

    public function build(): array
    {
        return [
            '@type' => 'VacationRental',
            '@id' => $this->data->url . SchemaIdEnum::VACATION_RENTAL,

            'inLanguage' => $this->data->language,

            'name' => $this->data->name,
            'description' => $this->data->description,
            'url' => $this->data->url,

            'image' => $this->images(),

            'numberOfRooms' => $this->data->rooms,

            'floorSize' => [
                '@type' => 'QuantitativeValue',
                'value' => $this->data->livingSpace,
                'unitCode' => 'MTK',
            ],

            'occupancy' => [
                '@type' => 'QuantitativeValue',
                'maxValue' => $this->data->maxGuests,
            ],

            'numberOfBedrooms' => [
                '@type' => 'QuantitativeValue',
                'value' => $this->data->bedrooms,
            ],

            'petsAllowed' => $this->data->petsAllowed,
            'smokingAllowed' => $this->data->smokingAllowed,

            'checkinTime' => $this->data->checkIn,
            'checkoutTime' => $this->data->checkOut,

            'priceRange' => $this->data->priceRange,

            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->data->address->street,
                'postalCode' => $this->data->address->postalCode,
                'addressLocality' => $this->data->address->city,
                'addressRegion' => $this->data->address->region,
                'addressCountry' => $this->data->address->country,
            ],

            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $this->data->geo->latitude,
                'longitude' => $this->data->geo->longitude,
            ],

            'amenityFeature' => $this->amenityFeatures(),

            'hasMap' => sprintf(
                'https://www.openstreetmap.org/?mlat=%s&mlon=%s',
                $this->data->geo->latitude,
                $this->data->geo->longitude,
            ),

            'currenciesAccepted' => 'EUR',

            'paymentAccepted' => [
                'Bank Transfer',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function images(): array
    {
        return array_map(
            static fn(ImageData $image): string => $image->url,
            $this->data->images,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function amenityFeatures(): array
    {
        return array_map(
            static fn(AmenityData $amenity): array => [
                '@type' => 'LocationFeatureSpecification',
                'name' => $amenity->name,
                'value' => true,
            ],
            $this->data->amenityFeatures,
        );
    }
}