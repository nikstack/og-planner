<?php

namespace ogPlanner\utils;

require_once 'IScraper.php';
require_once BASEDIR . 'src/ogPlanner/model/Table.php';

use DOMDocument;
use DOMNode;
use DOMXPath;
use ogPlanner\model\ITable;
use ogPlanner\model\Table;


class TableScraper implements IScraper
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function scrape(): ITable
    {

        // Todo: Error handling
        $html = file_get_contents($this->url); // allow_url_fopen must be set to true in php.ini; use curl instead
        $html = str_replace('aside', 'div', $html);
        $html = str_replace('footer', 'div', $html);
        $html = str_replace('nav', 'div', $html);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $headerNodes = $xpath->query('//table/thead/tr/th');
        $rowNodes = $xpath->query('//table/tbody/tr');

        $headerColumns = [];
        /** @var DOMNode $headerNode */
        foreach ($headerNodes as $headerNode) {
            $headerColumns[] = self::prepareInput($headerNode->nodeValue);
        }

        $rows = [];
        /** @var DOMNode $rowNode */
        foreach ($rowNodes as $rowNode) {
            $row = [];
            $rowEntries = $xpath->query('./td', $rowNode);

            /** @var DOMNode $rowEntry */
            foreach ($rowEntries as $rowEntry) {
                $row[] = self::prepareInput($rowEntry->nodeValue);
            }

            $rows[] = $row;
        }

        return new Table($headerColumns, $rows);
    }

    private static function prepareInput(string $input): string
    {
        return htmlspecialchars(stripslashes(trim($input)));
    }
}