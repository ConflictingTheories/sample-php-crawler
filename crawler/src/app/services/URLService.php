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
use Phalcon\Mvc\View;

class URLService implements ServiceProviderInterface
{
    public function register(DiInterface $di) : void
    {
        $di->setShared(
            'url',
            function (): Url {
                $url = new Url();
                $url->setBaseUri('/');
                return $url;
            }
        );
    }
}
