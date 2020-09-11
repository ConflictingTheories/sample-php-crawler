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

# List of Services to be Injected
return [
    \ChaosCrawler\Services\DispatcherService::class,
    \ChaosCrawler\Services\ViewService::class,
    \ChaosCrawler\Services\URLService::class,
];