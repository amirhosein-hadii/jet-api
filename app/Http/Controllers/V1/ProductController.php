<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ColorsSubCategories;
use App\Models\Product;
use App\Models\PropertiesSuperLabel;


class ProductController extends Controller
{
    public function show($id)
    {
        try {
            $product = Product::query()
                ->with([
                    'images', 'productVendors.vendor:id,name', 'productVendors.color',
                    'importanceProperties' => function ($q) {
                        $q->join('properties_title', 'properties_title.id', 'products_properties_value.property_title_id')
                            ->select('products_properties_value.name as value_name', 'products_properties_value.product_id',
                                'properties_title.name as title_name', 'properties_title.priority', 'properties_title.id as title_id'
                            )->orderBy('priority', 'desc');
                    }
                ])
                ->findOrFail($id);

            $properties = PropertiesSuperLabel::query()
                ->whereHas('titles.ProductPropertiesValues', function ($q) use ($id) {
                    $q->where('products_properties_value.product_id', $id);
                })
                ->with(['titles' => function($query) use ($id) {
                    $query->join('products_properties_value', 'products_properties_value.property_title_id', '=', 'properties_title.id')
                        ->where('products_properties_value.product_id', $id)
                        ->select(
                            'products_properties_value.name as value_name',
                            'products_properties_value.product_id',
                            'properties_title.name as title_name',
                            'properties_title.priority',
                            'properties_title.property_super_label_id',
                            'properties_title.id as title_id'
                        )
                        ->orderBy('priority', 'desc');
                }])
                ->get();

            $colors = ColorsSubCategories::query()->whereIn('id', $product->productVendors->pluck('sub_color_id'))->select('id', 'code', 'name')->get();

            return ApiResponse::Json(200,'', ['product' => $product, 'properties' => $properties, 'colors' => $colors],200);

        } catch (\Exception $e) {
            dd($e->getMessage());
            return ApiResponse::Json(400,'خطایی رخ داده است.', [],400);
        }
    }


    public function list()
    {
        $products = Product::query()
            ->with(['importanceProperties'])
            ->where('products.status', 'active')
            ->select('id', 'brand_id', 'title', 'tag_id', 'avatar_link_l')
            ->orderByDesc('id')
            ->paginate(10);

        return ApiResponse::Json(200,'', ['products' => $products],200);
    }
}
