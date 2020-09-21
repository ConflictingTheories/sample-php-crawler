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
use Phalcon\Tag;

class TagService implements ServiceProviderInterface
{
    public function register(DiInterface $di) : void
    {
        $di->setShared(
            'tag',
            function (): Tag {
                $tag = new Tag();
                return $tag;
            }
        );
    }
}
