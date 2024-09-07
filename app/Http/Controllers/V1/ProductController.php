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
                    'images', 'productVendors.color', 'importanceProperties',
                    'productVendors' => function ($q) {
                        $q->with('vendor:id,name,status')
                            ->join('vendors', 'vendors.id', '=', 'vendors_products.vendor_id')
                            ->where('vendors.status', 'active')
                            ->orderByDesc('inventory_num')
                            ->select('vendors_products.*');
                    }
                ])
                ->where('products.status', 'active')
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
        $products = Product::query()
            ->with(['importanceProperties'])
            ->join('vendors_products', 'vendors_products.product_id', 'products.id')
            ->whereHas('vendors', function ($query) {
                $query->where('vendors.status', 'active');
            })
            ->when($request->filled('brand_id'), function ($q) use ($request) {
                return $q->where('products.brand_id', $request->brand_id);
            })
            ->when($request->filled('title'), function ($q) use ($request) {
                return $q->where('products.title', 'like', $request->title . "%");
            })
            ->when($request->filled('price_from'), function ($q) use ($request) {
                return $q->where('vendors_products.price', '>=', $request->price_from);
            })
            ->when($request->filled('price_to'), function ($q) use ($request) {
                return $q->where('vendors_products.price', '<=', $request->price_to);
            })
            ->when($request->filled('tag_id'), function ($q) use ($request) {
                $tagsId = DB::select("
                        WITH RECURSIVE cte (id, name, parent_id, orig_id) AS (
                            SELECT id, name, parent_id, id AS orig_id
                            FROM tags
                            WHERE id = ?
                            UNION ALL
                            SELECT t1.id, t1.name, t1.parent_id, t2.orig_id
                            FROM tags t1
                            INNER JOIN cte t2 ON t2.id = t1.parent_id
                        )
                        SELECT id FROM cte
                    ", [$request->tag_id]);

                $tagsId = collect($tagsId)->pluck('id')->toArray();

                return $q->whereIn('products.tag_id', $tagsId);
            })
            ->where('products.status', 'active')
            ->groupBy('products.id')
            ->select('products.id', 'products.brand_id', 'products.title', 'products.tag_id', 'products.avatar_link_l', DB::raw('MIN(price) as price'))
            ->orderByDesc('id')
            ->paginate(10);

        return ApiResponse::Json(200,'', ['products' => $products],200);
    }
}
