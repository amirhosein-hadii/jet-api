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
        $tags = Tag::query()->whereNull('parent_id')->pluck('name','id');

        $navbars = Navbar::query()
            ->with(['navbar_banners', 'navbar_products'])
            ->where('which_page', 'home')
            ->orderByDesc('priority')
            ->get();

        return response()->json(['tags' => $tags, 'navbars' => $navbars],200);
    }

}
