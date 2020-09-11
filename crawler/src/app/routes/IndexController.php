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
**              IndexController               **
** ------------------------------------------ **
\*                                            */

namespace ChaosCrawler\Routes;

use Phalcon\Mvc\Controller;
use Masterminds\HTML5;


class IndexController extends Controller
{

    # Link Cache
    private $visitedLinks = [];
    # Initial URL
    private $baseUrl = '';
    # Total Load Time
    private $load = 0;
    # Total Word Count
    private $wc = 0;
    # Total Title Lenght
    private $titleLen = 0;

    # GET / (Default)
    public function indexAction($site = "agencyanalytics.com", $count = 5, $depth = 0, $first = true)
    {
        $this->crawlAction($site, $count, $depth, $first);
    }

    # GET /crawl/:site/:count/:depth
    public function crawlAction($site = "agencyanalytics.com", $count = 5, $depth = 0, $first = true)
    {
        try {
            # Set Base URL (assumes HTTPS)
            $this->baseUrl = 'https://' . $site;

            # Crawl
            $output = $this->crawl($this->baseUrl, $count, $depth, $first);

            # Response
            echo '<div class="alert alert-primary" role="alert">';
            echo '# of Sites Crawled: <strong>' . sizeof($this->visitedLinks) . '</strong><br/>';
            echo 'Average Load Time: <strong>' . $this->load / sizeof($this->visitedLinks) . ' seconds</strong><br/>';
            echo 'Average Word Count: <strong>' . $this->wc / sizeof($this->visitedLinks) . ' words</strong><br/>';
            echo 'Average Title Length: <strong>' . $this->titleLen / sizeof($this->visitedLinks) . ' words</strong>';
            echo '</div>';

            echo $output;
        } catch (\Exception $e) {
            echo "Something went wrong";
        }
    }

    protected function displayAverages()
    {
    }

    # Crawl Website - Url, Crawl Links Count - Depth to Follow Links, Initial Load
    protected function crawl($site, $count, $depth, $first)
    {
        $result = [];
        try {
            $output = [
                'site' => $site,
                'checksum' => crc32($site),
                'wordcount' => 0,
                'internal' => [],
                'external' => [],
                'img' => [],
            ];

            $html5 = new HTML5();
            $page = $this->fetch($site);

            if ($page['status']) {

                # Load HTML
                $dom = $html5->loadHTML($page['data']);
                $this->crawlPage($output, $dom);

                # Render Header Section (only first level)
                if ($first)
                    $result[] = $this->view->render('header');

                # Render Output Table
                $result[] = $this->view->render('results', ['output' => $output, 'status' => $page['status'], 'load' => $page['load']]);
                # Load Averages
                $this->load += $page['load'];
                # Word Count Averages
                $this->wc += $output['wordcount'];
                # Title Length
                $this->titleLen += strlen($dom->getElementsByTagName('title')[0]->textContent);

                # Crawl # of Links (Internal)
                $lCount = $count;
                if ($lCount == 0) $lCount = sizeof($output['internal']); # OR (all if set to 0)
                while (--$lCount > 0 && sizeof($output['internal']) > 0) {
                    $url = array_pop($output['internal'])['src'];
                    $this->consoleLog($url);
                    if ($this->checkLink($url)) {
                        if ($depth >= 0) {
                            $newDepth = $depth - 1;
                            $result[] = $this->crawl($url, $count, $newDepth, false);
                        }
                    } else
                        $lCount++;
                }
            }
        } catch (\Exception $e) {
            echo "Something went wrong";
        }
        return join("", $result);
    }

    # Log to Browser (JS Console)
    protected function consoleLog($url)
    {
        echo '<script>console.log("' . $url . '");</script>';
    }

    # Store in Cache
    protected function storeLink($url)
    {
        $this->visitedLinks[] = $this->formatURL($url, $url);
    }

    # Check Against Cache
    protected function checkLink($url)
    {
        return !in_array($this->formatURL($url, $url), $this->visitedLinks);
    }

    # Fetch Link
    protected function fetch($url)
    {
        if ($this->checkLink($url)) {
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
            $this->storeLink($url);
            # Return
            return ['data' => mb_convert_encoding($webdata, 'HTML-ENTITIES', "UTF-8"), 'status' => $status, 'load' => $load];
        } else {
            return ['data' => null, 'status' => 500];
        }
    }

    # Format URL to Fully Qualified
    protected function formatURL($url, $site)
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

    # Internal vs External Links
    protected function isInternal($url = '')
    {
        // Abort if parameter URL is empty
        if (empty($url)) {
            return false;
        }
        $link_url = parse_url($url);
        $home_url = parse_url($this->baseUrl);

        if (empty($link_url['host'])) {
            $class = true;
        } elseif ($link_url['host'] == $home_url['host']) {
            $class = true;
        } else {
            $class = false;
        }
        return $class;
    }


    # Parse Tags of Interest
    protected function parseTag(&$output, $tag)
    {
        switch ($tag->tagName) {
                # Anchor Tags
            case 'a':

                $url = $tag->getAttribute('href');
                $chksum = crc32($this->formatURL($url, $output['site']));
                if (!in_array($chksum, array_keys($output['internal'])) && !in_array($chksum, array_keys($output['external'])) && $this->isInternal($url)) {
                    $output['internal'][$chksum] = [
                        'src' => $this->formatURL($url, $output['site']),
                        'label' => $tag->textContent,
                        'checksum' => $chksum
                    ];
                } elseif (!in_array($chksum, array_keys($output['internal'])) && !in_array($chksum, array_keys($output['external']))) {
                    $output['external'][$chksum] = [
                        'src' => $this->formatURL($url, $output['site']),
                        'label' => $tag->textContent,
                        'checksum' => $chksum
                    ];
                }
                break;
                # Image Tags
            case 'img':
                $url = $tag->getAttribute('src');
                $chksum = crc32($this->formatURL($url, $output['site']));
                if (!in_array($chksum, array_keys($output['img'])))
                    $output['img'][$chksum] = [
                        'src' => $this->formatURL($url, $output['site']),
                        'label' => $tag->getAttribute('alt'),
                        'checksum' => $chksum
                    ];
                break;
        }
        $output['wordcount'] += sizeof(explode(' ', $tag->textContent));
    }

    // Crawl Page DOM Tree
    protected function crawlPage(&$output, $dom)
    {
        if (is_null($dom->childNodes)) {
            return;
        } else
            foreach ($dom->childNodes as $item) {
                $this->parseTag($output, $item);
                $this->crawlPage($output, $item);
            }
    }
}
