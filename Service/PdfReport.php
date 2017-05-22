<?php
/** 
 * The MIT License (MIT)
 * Copyright (c) 2017 Discus Tecnologia.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
*/

namespace DiscusTecnologia\PdfReportBundle\Service;

use mikehaertl\wkhtmlto\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

/**
 * Wrapper of wkhtmltopdf that uses twig templating to generate pdf files.
 * @author Rodrigo Ramos <rodrigoramos@discustecnologia.com.br>
 * @name PdfReport
 * @license MIT
 *
 */
class PdfReport
{
    //default pages sizes
    const A4 = [210, 297];
    const A3 = [297, 420];
    const A5 = [148, 210];
    const A2 = [420, 594];
    const A1 = [594, 841];
    const A0 = [841, 1189];

    private $dimensions = [
        'L' => 0,
        'B' => 0,
        'R' => 0,
        'T' => 0,
        'pageW' => 210,
        'pageH' => 297,
        'headerH' => 30,
        'footerH' => 30
    ];
    private $orientation = "Portrait"; //"Landscape"
    private $showBookmarks = true;
    private $grayscale = false;
    private $savePDF = false;
    private $pathToSave = "";  //'C:\wamp\www/wkpdf/pdf/page.pdf'

    private $iniConf = array();
    private $pdf;
    private $pages = "";
    private $cssUser = "";
    private $header = null;
    private $footer = null;
    private $totalPages = 1;

    private $twig;
    private $twigLoader;

    /**
     * Constructor that receive Twig and Twig Loader by dependency injection.
     * @param Symfony\Bundle\TwigBundle\TwigEngine $twig
     * @param Symfony\Bundle\TwigBundle\Loader\FilesystemLoader $twigLoader
     */
    public function __construct(TwigEngine $twig, FilesystemLoader $twigLoader)
    {
        $this->twig = $twig;
        $this->twigLoader = $twigLoader;
        $this->iniConf = array(
            'margin-top'    => 0, 
            'margin-right'  => 0, 
            'margin-bottom' => 0, 
            'margin-left'   => 0, 
            'encoding' => 'UTF-8',
            'orientation'   => $this->orientation,
            'page-width' => $this->dimensions['pageW'],
            'page-height' => $this->dimensions['pageH'],
            // Default page options
            'disable-smart-shrinking'
        );
        if(!$this->showBookmarks) array_push($iniConf, 'no-outline');
        if($this->grayscale)array_push($iniConf, 'grayscale');
        $this->pdf = new Pdf($this->iniConf);
    }

    /**
     * Generate pdf file based on twig template.
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function generate()
    {
        $this->pdf->setOptions($this->iniConf);
        if($this->savePDF) {
            if (!$this->pdf->saveAs($this->pathToSave)) {
                echo $this->pdf->getError();
            }
        }
        $this->pdf->send();
        $response = new Response();
        $response->headers->set("Content-type", "application/pdf");
        return $response;
    }

    /**
     * Add pages to pdf. Pages are twig templates that can have one or more pages of data.
     * @param string $twigTemplate Path to twig template.
     * @param mixed[] $data Array of data to pass to twig template .
     * @return void
     */
    public function addPages($twigTemplate, $data = [])
    {
        $data['pdfReportDimensions'] = $this->dimensions;
        $data['pdfReportCssUser'] = $this->cssUser;
        $data['pdfReportContentDocument'] = $twigTemplate;
        $data['pdfReportPageHeader'] = $this->header;
        $data['pdfReportPageFooter'] = $this->footer;
        $data['pdfReportTotalPages'] = $this->totalPages;
        $data['pdfReportOrientationPrtrait'] = $this->orientation == "Portrait" ? true : false;
        
        $this->twigLoader->addPath(__DIR__ . '/../Resources/views/', $namespace = '__main__');
        $content = $this->twig->render('DiscusTecnologiaPdfReportBundle:pdfReport:base-report.html.twig', $data);

        $this->pages .= $content;
        $this->pdf->addPage($content);
    }

    /**
     * Used to debug your template. Instead of returning a pdf, return html.
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function renderHTML()
    {
        $response = new Response();
        $response->setContent($this->pages);
        return $response;
    }

    //setar as configurações

    /**
     * Set margins of papper. Use sizes in milimeters.
     * @param int $top
     * @param int $right
     * @param int $bottom
     * @param int $left
     * @return void
     */
    public function setMargins($top, $right, $bottom, $left)
    {
        $this->dimensions['T'] = $top;
        $this->dimensions['R'] = $right;
        $this->dimensions['B'] = $bottom;
        $this->dimensions['L'] = $left;
        $this->dimensions['footerW'] = $this->dimensions['pageW'] - $this->dimensions['L'] - $this->dimensions['R'];
    }

    /**
     * Set orientation pages to landscape.
     * @return void
     */
    public function setOrientationLandscape()
    {
        $this->orientation = "Landscape";
        $this->iniConf['orientation'] = $this->orientation;
        $this->pdf->setOptions($this->iniConf);
    }

    /**
     * Set orientation pages to portrait.
     * @return void
     */
    public function setOrientationPortrait()
    {
        $this->orientation = "Portrait";
        $this->iniConf['orientation'] = $this->orientation;
        $this->pdf->setOptions($this->iniConf);
    }

    /**
     * Set to hide or show pdf bookmarks when you use H1 html tag.
     * @param boolean $show Show pdf bookmarks if true.
     * @return void
     */
    public function setShowBookmarks($show = true)
    {
        $this->showBookmarks = $show;
        if(!$show && isset($this->iniConf['no-outline'])) unset($this->iniConf['no-outline']);
        if($show && !isset($this->iniConf['no-outline'])) array_push($this->iniConf, 'no-outline');
        $this->pdf->setOptions($this->iniConf);
    }

    /**
     * Set pdf to be shown in black and white.
     * @param boolean $grayscale Set pdf to grayscale if true.
     * @return void
     */
    public function setGrayscale($grayscale = false)
    {
        $this->grayscale = $grayscale;
        if(!$grayscale && isset($this->iniConf['grayscale'])) unset($this->iniConf['grayscale']);
        if($grayscale && !isset($this->iniConf['grayscale'])) array_push($this->iniConf, 'grayscale');
        $this->pdf->setOptions($this->iniConf);
    }

    /**
     * Set to when you generate pdf using method generate(), the pdf to be saved on disk.
     * @param string @path Path on disk to save the pdf file.
     * @return void
     */
    public function setSavePDF($path = '')
    {
        $this->savePDF = true;
        $this->pathToSave = $path;
    }

    /**
     * Set pdf pages size. Default value is A4 page size.
     * @param array[int, int] [ Width of pages in milimeters, Height of pages in milimeters]
     * @return void
     */
    public function setPageSize($size = [210, 297])
    {
        $this->dimensions['pageW'] = $size[0];
        $this->dimensions['pageH'] = $size[1];
        $this->dimensions['footerW'] = $this->dimensions['pageW'] - $this->dimensions['L'] - $this->dimensions['R'];
        $this->iniConf['page-width'] = $size[0];
        $this->iniConf['page-height'] = $size[1];
        $this->pdf->setOptions($this->iniConf);
    }

    /**
     * Set height of header. Default is 30 milimeters.
     * @param int $heightmm Use values in milimeters.
     * @return void
     */
    public function setHeaderHeight($heightmm = 30)
    {
        $this->dimensions['headerH'] = $heightmm;
    }

    /**
     * Set height of footer. Default is 30 milimeters.
     * @param int $heightmm Use values in milimeters.
     * @return void
     */
    public function setFooterHeight($heightmm = 30)
    {
        $this->dimensions['footerH'] = $heightmm;
    }

    /**
     * Set custom css for your PDFs.
     * @param string $css Put your css code.
     * @return void
     */
    public function setCss($css)
    {
        $this->cssUser = $css;
    }

    /**
     * Set template of page header.
     * @param string $header Path to twig template
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Set template of page footer.
     * @param string $footer Path to twig template
     * @return void
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    /**
     * Set total pages of pdf to use when you generate reports based on records which you define how many records will shown by page.
     * @param int $numberOfPages
     * @return void
     */
    public function setTotalPages($numberOfPages)
    {
        $this->totalPages = $numberOfPages;
    }
}