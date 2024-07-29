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
            ->with(['images', 'properties', 'importanceProperties'])
            ->find($id);

        return response()->json(['product' => $product],200);

    }

    public function list()
    {
        $products = Product::query()
            ->with(['productVendors', 'importanceProperties'])
            ->paginate(10);

        return response()->json(['products' => $products],200);
    }
}
