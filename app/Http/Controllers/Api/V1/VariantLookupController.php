<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductVariantResource;
use App\Services\VariantLookupService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Endpoints used by the POS screen: barcode scan and quick product search. */
final class VariantLookupController extends Controller
{
    public function __construct(private readonly VariantLookupService $service) {}

    public function search(Request $request): AnonymousResourceCollection
    {
        $term = trim((string) $request->query('q', ''));

        return ProductVariantResource::collection(
            $term === '' ? collect() : $this->service->search($term, min($request->integer('limit', 20), 50))
        );
    }

    public function barcode(string $barcode): ProductVariantResource
    {
        $variant = $this->service->byBarcode($barcode);

        abort_if($variant === null, 404, 'No sellable product found for this barcode.');

        return new ProductVariantResource($variant);
    }
}
