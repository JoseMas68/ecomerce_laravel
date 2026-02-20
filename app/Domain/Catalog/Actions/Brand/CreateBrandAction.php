<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Brand;

use App\Domain\Catalog\DTOs\CreateBrandData;
use App\Domain\Catalog\Exceptions\BrandAlreadyExistsException;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Repositories\BrandRepositoryInterface;

/**
 * Action for creating a new brand.
 */
readonly class CreateBrandAction
{
    /**
     * Create a new CreateBrandAction instance.
     *
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
    ) {
    }

    /**
     * Execute the action to create a brand.
     *
     * @param CreateBrandData $data
     * @return Brand
     * @throws BrandAlreadyExistsException
     */
    public function __invoke(CreateBrandData $data): Brand
    {
        // Verificar si ya existe una marca con el mismo slug
        $existingBrand = $this->brandRepository->findBySlug($data->slug);

        if ($existingBrand !== null) {
            throw new BrandAlreadyExistsException(
                sprintf('Ya existe una marca con el slug "%s".', $data->slug)
            );
        }

        // Crear la marca usando el repositorio
        return $this->brandRepository->create($data->toArray());
    }
}
