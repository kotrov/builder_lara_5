<?php

namespace App\Http\ViewComposers;

use App\Models\Tree;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class HeaderComposer
{
    public function compose(View $view)
    {
        $menu = Cache::tags(['tree'])->rememberForever('menuProduct', function () {
            $menu = Tree::isMenu()->get();

            return $menu;
        });

        $view->with(compact('menu'));
    }
}
