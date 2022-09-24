<?php

namespace App\Tests\Service\Import;

use App\Entity\Content;
use App\Service\Import\HtmlCleaner;
use PHPUnit\Framework\TestCase;

class HtmlCleanerTest extends TestCase
{
    public function testStripsTags()
    {
        $taintedHtml = '<h4 xmlns="http://www.w3.org/1999/xhtml" epub:type="title" xmlns:epub="http://www.idpf.org/2007/ops">That the Faculties<a href="#note-57" id="noteref-57" epub:type="noteref">57</a> Are Not Safe to the Uninstructed</h4>';
        $cleaner = new HtmlCleaner();
        $cleaner->setAllowedTagsAndAttributes(Content::ALLOWED_HTML_TAGS);
        $cleanedHtml = $cleaner->clean($taintedHtml);
        $expectedHtml = 'That the Faculties57 Are Not Safe to the Uninstructed';
        $this->assertEquals($expectedHtml, $cleanedHtml);
    }

    public function testLeavesAllowedTags()
    {
        $sourceHtml = '<p>In as many ways as we can change things which are equivalent to one another, in just so many ways we can change the forms of arguments (<i>ἐπιχειρήματα</i>) and enthymemes...</p>';
        $cleaner = new HtmlCleaner();
        $cleaner->setAllowedTagsAndAttributes(Content::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
        $cleanedHtml = $cleaner->clean($sourceHtml);
        $this->assertEquals($sourceHtml, $cleanedHtml);
    }

    public function testStripsAttributes()
    {
        $sourceHtml = '<li xmlns="http://www.w3.org/1999/xhtml" id="note-64" epub:type="endnote" xmlns:epub="http://www.idpf.org/2007/ops"><p>Cicero, <i epub:type="se:name.publication.book">Tusculan Disputations</i> <span epub:type="z3998:roman">v</span> 37, has the same: “<i xml:lang="la" lang="la">Socrates cum rogaretur, cujatem se esse diceret, Mundanum, inquit. Totius enim mundi se incolam et civem arbitrabatur.</i>” <cite>—⁠John Upton</cite> <a href="#noteref-64" epub:type="backlink">↩</a></p></li>';
        $expectedHtml = '<p>Cicero, <i>Tusculan Disputations</i> v 37, has the same: “<i>Socrates cum rogaretur, cujatem se esse diceret, Mundanum, inquit. Totius enim mundi se incolam et civem arbitrabatur.</i>” —⁠John Upton ↩</p>';

        $cleaner = new HtmlCleaner();
        $cleaner->setAllowedTagsAndAttributes(Content::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
        $cleanedHtml = $cleaner->clean($sourceHtml);
        $this->assertEquals($expectedHtml, $cleanedHtml);
    }

    public function testTrims()
    {
        $sourceHtml = "\n\t\t\t\t\tJohann Schweigh\u00e4user writes\n\t\t\t\t";
        $expectedHtml = 'Johann Schweigh\u00e4user writes';

        $cleaner = new HtmlCleaner();
        $cleaner->setAllowedTagsAndAttributes(Content::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
        $cleanedHtml = $cleaner->clean($sourceHtml);
        $this->assertEquals($expectedHtml, $cleanedHtml);
    }
}
