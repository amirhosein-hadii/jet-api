<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PropertiesSuperLabel;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::query()
            ->with([
                'images', 'productVendors',
                'importanceProperties' => function ($q) {
                    $q->join('properties_title', 'properties_title.id', 'products_properties_value.property_title_id')
                        ->select('products_properties_value.name as value_name', 'products_properties_value.product_id',
                            'properties_title.name as title_name', 'properties_title.priority', 'properties_title.id as title_id'
                        )->orderBy('priority', 'desc');
                }
            ])
            ->find($id);

        $properties = PropertiesSuperLabel::query()
            ->has('titles.ProductPropertiesValues')
            ->with(['titles' => function($query) use ($id) {
                $query->join('products_properties_value', 'products_properties_value.property_title_id', 'properties_title.id')
                    ->where('products_properties_value.product_id', $id)
                    ->select('products_properties_value.name as value_name', 'products_properties_value.product_id',
                        'properties_title.name as title_name', 'properties_title.priority', 'properties_title.property_super_label_id', 'properties_title.id as title_id'
                    )->orderBy('priority', 'desc');
            }])
            ->get();

        return ApiResponse::Json(200,'', ['product' => $product, 'properties' => $properties],200);
    }

    public function list()
    {
        $products = Product::query()
            ->with(['productVendors', 'importanceProperties'])
            ->paginate(10);

        return ApiResponse::Json(200,'', ['products' => $products],200);
    }
}
