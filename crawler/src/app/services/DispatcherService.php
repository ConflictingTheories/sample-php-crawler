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
use Phalcon\Mvc\Dispatcher;

class DispatcherService implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->setShared(
            'dispatcher',
            function () {
                # Dispatch to Routes (via Namespace)
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(
                    'ChaosCrawler\Routes'
                );
                return $dispatcher;
            }
        );
    }
}
