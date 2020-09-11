<?php

/*                                            *\
** ------------------------------------------ **
**      	   SAMPLE PHP CRAWLER     	      **
** ------------------------------------------ **
**  Copyright (c) 2020 - Kyle Derby MacInnis  **
**                                            **
** Any unauthorized distribution or transfer  **
**    of this work is strictly prohibited.    **
**                                            **
**           All Rights Reserved.             **
** ------------------------------------------ **
\*                                            */

namespace ChaosCrawler\Services;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\View\Simple;

class ViewService implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->setShared(
            'view',
            function () {
                $view = new Simple(); // Simple Views
                $view->setViewsDir(APP_PATH . '/views/');
                return $view;
            }
        );
    }
}