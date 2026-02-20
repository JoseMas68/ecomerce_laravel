<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Brand;

use App\Domain\Catalog\DTOs\UpdateBrandData;
use App\Domain\Catalog\Exceptions\BrandAlreadyExistsException;
use App\Domain\Catalog\Exceptions\BrandNotFoundException;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Repositories\BrandRepositoryInterface;

/**
 * Action for updating an existing brand.
 */
readonly class UpdateBrandAction
{
    /**
     * Create a new UpdateBrandAction instance.
     *
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
    ) {
    }

    /**
     * Execute the action to update a brand.
     *
     * @param int $brandId
     * @param UpdateBrandData $data
     * @return Brand
     * @throws BrandNotFoundException
     * @throws BrandAlreadyExistsException
     */
    public function __invoke(int $brandId, UpdateBrandData $data): Brand
    {
        // Verificar que la marca existe
        $brand = $this->brandRepository->find($brandId);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('No se encontró la marca con ID "%d".', $brandId)
            );
        }

        // Si se está actualizando el slug, verificar que no esté duplicado
        if ($data->slug !== null && $data->slug !== $brand->slug) {
            $existingBrand = $this->brandRepository->findBySlug($data->slug);

            if ($existingBrand !== null && $existingBrand->id !== $brandId) {
                throw new BrandAlreadyExistsException(
                    sprintf('Ya existe otra marca con el slug "%s".', $data->slug)
                );
            }
        }

        // Actualizar la marca usando el repositorio
        $updatedBrand = $this->brandRepository->update($brandId, $data->toArray());

        if ($updatedBrand === null) {
            throw new BrandNotFoundException(
                sprintf('No se pudo actualizar la marca con ID "%d".', $brandId)
            );
        }

        return $updatedBrand;
    }
}
