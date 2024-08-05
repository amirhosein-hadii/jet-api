<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Navbar;
use App\Models\Tag;
use Illuminate\Http\Request;

class MainPageController extends Controller
{
    public function mainPage()
    {
        $tags = Tag::query()->whereNull('parent_id')->select('name','id')->get();

        $navbars = Navbar::query()
            ->with(['navbar_banners',
                    'navbar_products' => function ($query) {
                        $query->join('products', 'products.id', '=', 'navbars_products.product_id')
                            ->select('navbars_products.*', 'products.title', 'products.avatar_link_l');
                }
            ])
            ->where('which_page', 'home')
            ->orderByDesc('priority')
            ->get();

        return response()->json(['tags' => $tags, 'navbars' => $navbars],200);
    }

}
