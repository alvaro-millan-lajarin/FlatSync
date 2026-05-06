<?php

namespace App\Controllers;

class SitemapController extends BaseController
{
    public function index()
    {
        $base = rtrim(base_url(), '/');
        $lastmod = date('Y-m-d');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        $xml .= "  <url>\n";
        $xml .= "    <loc>{$base}/</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>monthly</changefreq>\n";
        $xml .= "    <priority>1.0</priority>\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"es\" href=\"{$base}/\"/>\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"{$base}/\"/>\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"ca\" href=\"{$base}/\"/>\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"{$base}/\"/>\n";
        $xml .= "  </url>\n";

        $xml .= "  <url>\n";
        $xml .= "    <loc>{$base}/login</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>yearly</changefreq>\n";
        $xml .= "    <priority>0.3</priority>\n";
        $xml .= "  </url>\n";

        $xml .= "  <url>\n";
        $xml .= "    <loc>{$base}/register</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>yearly</changefreq>\n";
        $xml .= "    <priority>0.3</priority>\n";
        $xml .= "  </url>\n";

        $xml .= "</urlset>\n";

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setBody($xml);
    }
}
