<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::query()
            ->with('images', 'properties')
            ->find($id);

        return response()->json(['product' => $product],200);

    }
}
