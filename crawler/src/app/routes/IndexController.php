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

use Exception;
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
    public function indexAction($site = "agencyanalytics.com", $count = 5, $depth = 0)
    {
        $this->crawlAction($site, $count, $depth);
    }

    # GET /crawl/:site/:count/:depth
    public function crawlAction($site = "agencyanalytics.com", $count = 5, $depth = 0)
    {
        try {
            # Set Base URL (assumes HTTP -- Curl will Follow if Redirected to HTTPS)
            $this->baseUrl = 'http://' . $site;

            # Crawl
            $output = $this->crawlSite($this->baseUrl, $count, $depth, true);
            echo $this->tag->getDocType(5);
            echo '<html>';
            # Response
            echo '<div class="alert alert-primary" role="alert">';
            echo '# of Sites Crawled: <strong>' . sizeof($this->visitedLinks) . '</strong><br/>';
            echo 'Average Load Time: <strong>' . $this->load / sizeof($this->visitedLinks) . ' seconds</strong><br/>';
            echo 'Average Word Count: <strong>' . $this->wc / sizeof($this->visitedLinks) . ' words</strong><br/>';
            echo 'Average Title Length: <strong>' . $this->titleLen / sizeof($this->visitedLinks) . ' words</strong>';
            echo '</div>';
            echo $output;
            echo '</html>';
        } catch (\Exception $e) {
            echo "Something went wrong";
        }
    }

    # Crawl Website - Url, Crawl Links Count - Depth to Follow Links, Initial Load
    protected function crawlSite($site, $count, $depth, $first)
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

            # Only Fetch Unique Links
            if ($this->checkLink($site)) {
                $page = $this->helper->fetch($site);
                $this->storeLink($site);
            }

            if ($page) {
                # Load HTML
                $html5 = new HTML5();
                $dom = $html5->loadHTML($page['data']);
                $this->combDOM($output, $dom);

                # Render Header Section (only first level)
                if ($first)
                    $result[] = $this->view->render('header', ['crawlSite' => $site]);

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
                    $this->helper->consoleLog($url);
                    if ($this->checkLink($url)) {
                        if ($depth >= 0) {
                            $newDepth = $depth - 1;
                            $result[] = $this->crawlSite($url, $count, $newDepth, false);
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

    # Parse Tags of Interest
    protected function parseTag(&$output, $tag)
    {
        switch ($tag->tagName) {
                # Anchor Tags
            case 'a':
                $url = $tag->getAttribute('href');
                $src = $this->helper->formatURL($url, $output['site']);
                $chksum = crc32($src);
                if (!in_array($chksum, array_keys($output['internal'])) && !in_array($chksum, array_keys($output['external'])) && $this->helper->isInternal($url, $this->baseUrl)) {
                    $output['internal'][$chksum] = [
                        'src' => $src,
                        'label' => $tag->textContent,
                        'checksum' => $chksum
                    ];
                } elseif (!in_array($chksum, array_keys($output['internal'])) && !in_array($chksum, array_keys($output['external']))) {
                    $output['external'][$chksum] = [
                        'src' => $src,
                        'label' => $tag->textContent,
                        'checksum' => $chksum
                    ];
                }
                break;
                # Image Tags
            case 'img':
                $url = $tag->getAttribute('src');
                $alt = $tag->getAttribute('alt');
                $src = $this->helper->formatURL($url, $output['site']);
                $chksum = crc32($src);
                if (!in_array($chksum, array_keys($output['img'])))
                    $output['img'][$chksum] = [
                        'src' => $src,
                        'label' => $alt,
                        'checksum' => $chksum
                    ];
                break;
        }
        $output['wordcount'] += sizeof(explode(' ', $tag->textContent));
    }

    // Crawl Page DOM Tree
    protected function combDOM(&$output, $dom)
    {
        if (is_null($dom->childNodes)) {
            return;
        } else
            foreach ($dom->childNodes as $item) {
                $this->parseTag($output, $item);
                $this->combDOM($output, $item);
            }
    }

    # Store in Cache
    protected function storeLink($url)
    {
        $this->visitedLinks[] = $this->helper->formatURL($url, $url);
    }

    # Check Against Cache
    protected function checkLink($url)
    {
        return !in_array($this->helper->formatURL($url, $url), $this->visitedLinks);
    }
}
