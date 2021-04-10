<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Plugin\CatalogSearch\Controller\Result;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\Page;
use Olegnax\LayeredNavigation\Plugin\Ajax;

class Index extends Ajax
{

    public function afterExecute(Action $controller, $page)
    {
        if ($this->isAjax() && $page instanceof Page) {
            $page = $this->json($this->getAjaxContent($page));
        }

        return $page;
    }

}
