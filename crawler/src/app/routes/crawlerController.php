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


class CrawlerController extends Controller
{

    # Link Cache
    private $visitedLinks = [];

    # GET /crawler/index/:site/:count/:depth
    public function indexAction($site = "https://www.google.ca", $count = 0, $depth = 0, $first = true)
    {
        $output = [
            'site' => $site,
            'checksum' => crc32($site),
            'wordcount' => 0,
            'a' => [],
            'img' => [],
            'link' => [],
        ];

        $html5 = new HTML5();
        $page = $this->fetch($site, $site);

        if ($page['status'] == 200) {

            # Load HTML
            $dom = $html5->loadHTML($page['data']);
            $this->crawlPage($output, $dom);

            # Render Header Section (only first level)
            if ($first)
                echo $this->view->render('header');

            # Render Sites
            echo $this->view->render('results', ['output' => $output]);

            // Crawl # more of Links from Base
            $lCount = $count;

            // Output all if set to 0 links
            if ($lCount == 0) $lCount = sizeof($output['a']);
            while (--$lCount > 0 && sizeof($output['a']) > 0) {
                $url = array_pop($output['a'])['src'];
                if ($depth >= 0) {
                    $newDepth = $depth - 1;
                    $this->indexAction($url, $count, $newDepth, false);
                }
            }
        }
    }

    # Store in Cache
    protected function storeLink($url,$site)
    {
        $this->visitedLinks[] = $this->formatURL($url,$site);
    }

    # Check Against Cache
    protected function checkLink($url,$site)
    {
        return !in_array($this->formatURL($url,$site), $this->visitedLinks);
    }

    # Fetch Link
    protected function fetch($url, $site)
    {
        if ($this->checkLink($url,$site)) {
            $cHandler = curl_init($url);

            curl_setopt($cHandler, CURLOPT_USERAGENT, 'ChaosCrawler - v1.0.0 - Web Crawler');
            curl_setopt($cHandler, CURLOPT_REFERER, $site);
            curl_setopt($cHandler, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($cHandler, CURLOPT_RETURNTRANSFER, true);

            $webdata = curl_exec($cHandler);
            $status =  curl_getinfo($cHandler, CURLINFO_HTTP_CODE);

            curl_close($cHandler);
            $this->storeLink($url,$site);

            return ['data' => mb_convert_encoding($webdata, 'HTML-ENTITIES', "UTF-8"), 'status' => $status];
        } else {
            return ['data' => null, 'status' => 500];
        }
    }

    # Format (For Relative Links)
    protected function formatURL($url, $site)
    {
        if (strpos($url, '//'))
            return $url;
        else {
            if (strpos($site, '//'))
                $nUrl = parse_url($site);
            else
                $nUrl = parse_url('https://' . $site);
            if (strpos($url, '/') == 0)
                return $nUrl['scheme'] . '://' . $nUrl['host'] . $url;
            else
                return $nUrl['scheme'] . '://' . $nUrl['host'] . $nUrl['path'] . $url;
        }
    }

    # Parse Tags of Interest
    protected function parseTag(&$output, $tag)
    {
        // Parse various Types of Tags
        switch ($tag->tagName) {
                # Anchor Tags
            case 'a':
                $chksum = crc32($tag->getAttribute('href'));
                if (!in_array($chksum, array_keys($output['a']))) {
                    $output['a'][$chksum] = [
                        'src' => $this->formatURL($tag->getAttribute('href'), $output['site']),
                        'label' => $tag->textContent,
                        'checksum' => $chksum
                    ];
                }
                break;
                # Image Tags
            case 'img':
                $chksum = crc32($tag->getAttribute('src'));
                if (!in_array($chksum, array_keys($output['img'])))
                    $output['img'][$chksum] = [
                        'src' => $this->formatURL($tag->getAttribute('src'), $output['site']),
                        'label' => $tag->getAttribute('alt'),
                        'checksum' => $chksum
                    ];
                break;
        }
        // Count Words Regardless (TODO -- CHECK THIS ISNT RECOUNTING)
        $output['wordcount'] += sizeof(explode(' ', $tag->textContent));
    }

    // Recursively Print DOM Tags
    protected function crawlPage(&$output, $dom)
    {
        if (is_null($dom->childNodes)) {
            return;
        } else
            foreach ($dom->childNodes as $item) {
                # Parse Content
                $this->parseTag($output, $item);
                # Crawl Child Nodes
                $this->crawlPage($output, $item);
            }
    }
}
