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

class HelperService implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'helper',
            function (): Helper {
                $helper = new Helper();
                return $helper;
            }
        );
    }
}

class FetchResponse
{
    public $data;
    public $status;
    public $load;

    public function __construct(string $data, int $status, float $load)
    {
        $this->data = $data;
        $this->status = $status;
        $this->load = $load;
    }
}

class Helper
{

    public function fetch(string $url): FetchResponse
    {
        $cHandler = curl_init($url);
        # Options
        curl_setopt($cHandler, CURLOPT_USERAGENT, 'ChaosCrawler - v1.0.0 - Web Crawler');
        curl_setopt($cHandler, CURLOPT_REFERER, $url);
        curl_setopt($cHandler, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($cHandler, CURLOPT_RETURNTRANSFER, true);
        # Fetch Page
        $webdata = curl_exec($cHandler);
        $status =  curl_getinfo($cHandler, CURLINFO_HTTP_CODE);
        $load = curl_getinfo($cHandler, CURLINFO_TOTAL_TIME);
        # Close & Store Link in Cache
        curl_close($cHandler);
        # Return

        return new FetchResponse(mb_convert_encoding($webdata, 'HTML-ENTITIES', "UTF-8"), $status, $load);
    }

    # Format URL provided with Url (possibly /path) & Site
    public function formatURL(string $url, string $site): string
    {
        if (strpos($url, '//'))
            return $url;
        else {
            if (strpos($site, '//'))
                $nUrl = parse_url($site);
            else
                $nUrl = parse_url('https://' . $site);
            if (strpos($url, '/') == 0) {
                if ($url == '/') {
                    return $nUrl['scheme'] . '://' . $nUrl['host'];
                } else {
                    return $nUrl['scheme'] . '://' . $nUrl['host'] . $url;
                }
            } else
                return $nUrl['scheme'] . '://' . $nUrl['host'] . $nUrl['path'] . $url;
        }
    }

    # Internal Link vs External Link Check
    public function isInternal(string $url = '', string $baseUrl): bool
    {
        // Abort if parameter URL is empty
        if (empty($url)) {
            return false;
        }
        $link_url = parse_url($url);
        $home_url = parse_url($baseUrl);

        if (empty($link_url['host'])) {
            $class = true;
        } elseif ($link_url['host'] == $home_url['host']) {
            $class = true;
        } else {
            $class = false;
        }
        return $class;
    }

    # Console Log via Script Tag to Browser (on Load)
    public function consoleLog(string $input): void
    {
        echo '<script>console.log("' . $input . '");</script>';
    }
}
