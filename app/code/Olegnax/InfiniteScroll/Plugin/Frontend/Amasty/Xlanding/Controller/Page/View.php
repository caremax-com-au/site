<?php


namespace Olegnax\InfiniteScroll\Plugin\Frontend\Amasty\Xlanding\Controller\Page;


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