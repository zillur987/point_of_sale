<?php

namespace App\Exceptions\Inventory;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Business-rule violation. Rendered as 422 {message} - the same shape the frontend's
 * useServerErrors() already understands, so it shows up as a toast/alert.
 */
abstract class InventoryException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
