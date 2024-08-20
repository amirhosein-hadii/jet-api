<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Navbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainPageController extends Controller
{
    public function mainPage()
    {
        $navbars = Navbar::query()
            ->with(['navbar_banners' => function ($q) {
                $q->orderByDesc('priority');
            },
                'navbar_products' => function ($query) {
                    $query->join('products', 'products.id', '=', 'navbars_products.product_id')
                        ->join('vendors_products', 'vendors_products.product_id', '=', 'products.id')
                        ->orderByDesc('priority')
                        ->select('navbars_products.*', 'products.title', 'products.avatar_link_l', DB::raw('MIN(vendors_products.price) as price'))
                        ->groupBy('navbars_products.id', 'products.title', 'products.avatar_link_l'); // Include all selected columns in the group by
                },
                'navbars_brands' => function ($query) {
                    $query->join('brands', 'brands.id', '=', 'navbars_brands.brand_id')
                        ->orderByDesc('priority');
                },
                'navbars_tags' => function ($query) {
                    $query->join('tags', 'tags.id', '=', 'navbars_tags.tag_id')
                        ->orderByDesc('priority');
                }
            ])
            ->where('which_page', 'home')
            ->where('status', 'active')
            ->orderByDesc('priority')
            ->get();

        return ApiResponse::Json(200,'', ['navbars' => $navbars],200);
    }

}
