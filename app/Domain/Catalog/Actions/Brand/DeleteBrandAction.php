<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Brand;

use App\Domain\Catalog\Exceptions\BrandNotFoundException;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Repositories\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * Action for deleting a brand.
 */
readonly class DeleteBrandAction
{
    /**
     * Create a new DeleteBrandAction instance.
     *
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
    ) {
    }

    /**
     * Execute the action to delete a brand.
     *
     * @param int $brandId
     * @return bool
     * @throws BrandNotFoundException
     * @throws RuntimeException
     */
    public function __invoke(int $brandId): bool
    {
        // Verificar que la marca existe
        $brand = $this->brandRepository->find($brandId);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('No se encontró la marca con ID "%d".', $brandId)
            );
        }

        // Verificar si tiene productos asociados
        /** @var Collection<int, \App\Domain\Catalog\Models\Product> $products */
        $products = $brand->products;

        if ($products->count() > 0) {
            throw new RuntimeException(
                sprintf(
                    'No se puede eliminar la marca "%s" porque tiene %d producto(s) asociado(s). ' .
                    'Elimine o reasigne los productos primero.',
                    $brand->name,
                    $products->count()
                )
            );
        }

        // Eliminar la marca usando el repositorio
        $deleted = $this->brandRepository->delete($brandId);

        if (!$deleted) {
            throw new RuntimeException(
                sprintf('No se pudo eliminar la marca con ID "%d".', $brandId)
            );
        }

        return true;
    }
}
