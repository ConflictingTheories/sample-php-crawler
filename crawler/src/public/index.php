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

namespace ChaosCrawler;

use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

# Paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

# Composer
require BASE_PATH . "/vendor/autoload.php";

try {

    # Autoloads Classes from Folders
    $loader = new Loader();
    $loader->registerNamespaces(
        [
            'ChaosCrawler\Services' => APP_PATH . '/services/',
            'ChaosCrawler\Models' => APP_PATH . '/models/',
            'ChaosCrawler\Routes' => APP_PATH . '/routes/',
        ]
    );
    $loader->register();

    # App Container
    $container = new FactoryDefault();

    # Inject Services
    $services = include_once APP_PATH . '/config/services.php';
    foreach ($services as $service) {
        $container->register(new $service());
    }

    # Start App
    $application = new Application($container);
    $application->useImplicitView(false); // For Simple Views

    # Handle Response
    $response = $application->handle($_SERVER["REQUEST_URI"]);
    $response->send();
} catch (\Exception $e) {

    echo 'Exception: ', $e->getMessage();
}
