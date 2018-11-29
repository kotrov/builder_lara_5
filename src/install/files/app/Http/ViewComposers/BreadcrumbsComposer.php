<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class BreadcrumbsComposer
{
    public function compose(View $view)
    {
        if (! isset($view->getData()['page'])) {
            return 'Не передан параметр';
        }

        $page = $view->getData()['page'];

        //if node
        if (get_class($page) == 'Tree' || get_class($page) == 'NewsTree') {
            $breadcrumbs = new \Breadcrumbs($page);
        } else {
            $node = $page->getNode();
            $breadcrumbs = new \Breadcrumbs($node);
            $breadcrumbs->add($page->getUrl(), $page->title);
        }

        $view->with(compact('breadcrumbs'));
    }
}
