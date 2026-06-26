<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema;

use Asterios\Core\Seo\Schema\Contracts\Node;
use Asterios\Core\Seo\Schema\Data\BreadcrumbData;
use Asterios\Core\Seo\Schema\Data\ImageGalleryData;
use Asterios\Core\Seo\Schema\Data\OfferCatalogData;
use Asterios\Core\Seo\Schema\Data\OfferData;
use Asterios\Core\Seo\Schema\Data\OrganizationData;
use Asterios\Core\Seo\Schema\Data\ResourceData;
use Asterios\Core\Seo\Schema\Data\VacationRentalData;
use Asterios\Core\Seo\Schema\Exceptions\RendererException;
use Asterios\Core\Seo\Schema\Node\Accommodation;
use Asterios\Core\Seo\Schema\Node\Breadcrumb;
use Asterios\Core\Seo\Schema\Node\ImageGallery;
use Asterios\Core\Seo\Schema\Node\Legalpage;
use Asterios\Core\Seo\Schema\Node\Offer;
use Asterios\Core\Seo\Schema\Node\OfferCatalog;
use Asterios\Core\Seo\Schema\Node\Organization;
use Asterios\Core\Seo\Schema\Node\TouristDestination;
use Asterios\Core\Seo\Schema\Node\VacationRental;
use Asterios\Core\Seo\Schema\Node\Webpage;
use Asterios\Core\Seo\Schema\Node\Website;

final class Schema
{
    private Graph $graph;

    private JsonRenderer $renderer;

    private function __construct()
    {
        $this->graph = new Graph();
        $this->renderer = new JsonRenderer();
    }

    public static function make(): self
    {
        return new self();
    }

    public function organization(OrganizationData $data): self
    {
        return $this->add(
            new Organization($data)
        );
    }

    public function add(Node $node): self
    {
        $this->graph->add($node);

        return $this;
    }

    public function website(ResourceData $data): self
    {
        return $this->add(
            new Website($data)
        );
    }

    public function webPage(ResourceData $data): self
    {
        return $this->add(
            new WebPage($data)
        );
    }

    public function breadcrumb(BreadcrumbData $data): self
    {
        return $this->add(
            new Breadcrumb($data)
        );
    }

    public function legalPage(ResourceData $data): self
    {
        return $this->add(
            new LegalPage($data)
        );
    }

    public function touristDestination(ResourceData $data): self
    {
        return $this->add(
            new TouristDestination($data)
        );
    }

    public function accommodation(ResourceData $data): self
    {
        return $this->add(
            new Accommodation($data)
        );
    }

    public function vacationRental(VacationRentalData $data): self
    {
        return $this->add(
            new VacationRental($data)
        );
    }

    public function offer(OfferData $data): self
    {
        return $this->add(
            new Offer($data)
        );
    }

    public function offerCatalog(OfferCatalogData $data): self
    {
        return $this->add(
            new OfferCatalog($data)
        );
    }

    public function imageGallery(ImageGalleryData $data): self
    {
        return $this->add(
            new ImageGallery($data)
        );
    }

    /**
     * @throws RendererException
     */
    public function render(): string
    {
        return $this->renderer->render(
            $this->build()
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function build(): array
    {
        return $this->graph->build();
    }
}