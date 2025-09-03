<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Remove the specified image from storage.
     */
    
public function destroy(ProductImage $image)
{
    try {
        if (Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $image->delete();

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to delete image.'], 500);
    }
}
}
