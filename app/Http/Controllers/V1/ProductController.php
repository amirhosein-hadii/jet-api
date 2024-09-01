<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ColorsSubCategories;
use App\Models\Product;
use App\Models\PropertiesSuperLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show($id)
    {
        try {
            $product = Product::query()
                ->with([
                    'images', 'productVendors.color',
                    'productVendors' => function ($q) {
                        $q->with('vendor:id,name,status')
                            ->join('vendors', 'vendors.id', '=', 'vendors_products.vendor_id')
                            ->where('vendors.status', 'active')
                            ->orderByDesc('inventory_num')
                            ->select('vendors_products.*');
                    },
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
                ->orderBy('priority', 'desc')
                ->get();

            $subColors = $product->productVendors
                ->sortByDesc('inventory_num')
                ->pluck('sub_color_id', 'inventory_num')->toArray();

            $colors = ColorsSubCategories::query()
                ->whereIn('id', $subColors)
                ->select('id', 'code', 'name')
                ->orderByRaw("FIELD(id, " . implode(',', $subColors) . ")")
                ->get();

            return ApiResponse::Json(200,'', ['product' => $product, 'properties' => $properties, 'colors' => $colors],200);

        } catch (\Exception $e) {
//            dd($e->getMessage());
            return ApiResponse::Json(400,'خطایی رخ داده است.', [],400);
        }
    }


    public function list(Request $request)
    {
        $tag_id = $request->tag_id;
        $products = DB::select("call List_of_businesses_based_on_tag_id($tag_id)");

        $filteredProducts = array_map(function($product) {
            return [
                'id'            => $product->id,
                'brand_id'      => $product->brand_id,
                'tag_id'        => $product->tag_id,
                'title'         => $product->title,
                'avatar_link_l' => $product->avatar_link_l,
            ];
        }, $products);


//        $products = Product::query()
//            ->with(['importanceProperties'])
//            ->whereHas('vendors', function ($query) {
//                $query->where('vendors.status', 'active');
//            })
//            ->when($request->filled('brand_id'), function ($q) use ($request) {
//                return $q->where('products.brand_id', $request->brand_id);
//            })
//            ->when($request->filled('price_from'), function ($q) use ($request) {
//                return $q->join('')
//                    ->where('brand_id', $request->brand_id);
//            })
//            ->where('products.status', 'active')
//            ->select('products.id', 'products.brand_id', 'products.title', 'products.tag_id', 'products.avatar_link_l')
//            ->orderByDesc('id')
//            ->paginate(10);

        return ApiResponse::Json(200,'', ['products' => $filteredProducts],200);
    }
}
