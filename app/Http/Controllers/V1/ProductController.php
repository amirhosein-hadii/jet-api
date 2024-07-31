<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PropertiesSuperLabel;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::query()
            ->with(['images', 'productVendors', 'importanceProperties'])
            ->find($id);

        $properties = PropertiesSuperLabel::query()
            ->has('titles.ProductPropertiesValues')
            ->with(['titles' => function($query) use ($id) {
                $query->has('ProductPropertiesValues')
                    ->with(['ProductPropertiesValues' => function($q) use ($id) {
                        $q->where('product_id', $id);
                    }]);
            }])
            ->get();


        return response()->json(['product' => $product, 'properties' => $properties],200);
    }

    public function list()
    {
        $products = Product::query()
            ->with(['productVendors', 'importanceProperties'])
            ->paginate(10);

        return response()->json(['products' => $products],200);
    }
}
