<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Plugin\Frontend\Magento\Catalog\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\View\Result\Page;
use Olegnax\InfiniteScroll\Plugin\Ajax;

class View extends Ajax
{

    /**
     * @param Action $controller
     * @param Page $page
     * @return Raw
     */
    public function afterExecute(Action $controller, $page)
    {
        if ($this->isAjax()) {
            $page = $this->json($this->getAjaxContent());
        }

        return $page;
    }

}
