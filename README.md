# pdf-report-bundle
[![logo Discus Tecnologia](https://www.discustecnologia.com.br/images/logo_pq.png)](http://www.discustecnologia.com.br)

Symfony Bundle. Wrapper of wkhtmltopdf that uses twig templating to generate pdf files.

---

## Dependencies
You need of wkhtmltopdf installed in your system.

## Instalation

You need of the [wkhtmltopdf](https://wkhtmltopdf.org/) installed in your OS.


Require the bundle with composer:  
```bash
composer require discustecnologia/pdf-report-bundle
```

Enable the bundle in the kernel:  

```php
public function registerBundles()
{
    $bundles = [
        // ...
        new DiscusTecnologia\PdfReportBundle\DiscusTecnologiaPdfReportBundle(),
        // ...
    ];
    ...
}
```

## How to use
In controllers instead of use:
```php
return $this->render(...);
```
You should use:
```php
$pdfReport = $this->get('discus-tecnologia.pdf-report');

$pdfReport->setHeader('default/header.twig');
$pdfReport->setFooter('default/footer.twig');
$pdfReport->setMargins(20, 20, 20, 20);
$pdfReport->addPages('default/testReport.twig', []);

return $pdfReport->generate();
```
Example of twig report based on records which shows 4 records per page:
```php
{% for i in ((pdfReportPageNumber -1) * 4)..(((pdfReportPageNumber * 4)-1) < (obj|length - 1) ? ((pdfReportPageNumber * 4)-1) : obj|length-1) %}
<div>{{ obj[i] }}</div>
{% endfor %}
```

## Example Symfony Controller
```php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DiscusTecnologia\PdfReportBundle\Service\PdfReport;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $pdfReport = $this->get('discus-tecnologia.pdf-report');
        
        $pdfReport->setSavePDF("/var/tmp/pdfreport.pdf");
        $pdfReport->setHeader('default/header.twig');
        $pdfReport->setFooter('default/footer.twig');
        $pdfReport->setMargins(20, 20, 20, 20);
        //Default size is A4. You can use A0, A1, A2, A3, A4, A5 or pass custom size array as [width, height]
        $pdfReport->setPageSize(PdfReport::A3);
        $pdfReport->setGrayscale(true);
        $pdfReport->setOrientationLandscape();
        $pdfReport->setTotalPages(2);
        $pdfReport->setHeaderHeight(40);
        $pdfReport->setFooterHeight(40);
        $pdfReport->setCss("#footer p {
            font-size: 10px;
            text-align: center;
            background: #ccc;
        }
        
        #content {
            width:      168mm;
            /*border: 1px #000000 solid;*/
        }
        
        p {
            /*border: 1px #FF0000 solid;*/
            max-width:      168mm;
            text-indent: 30px;
            margin-bottom: 15px;
            line-height: 20px;
            font-size: 14px;
            text-align: justify;
        }");
        $pdfReport->addPages('default/testeRel.twig', ['obj' => ['a', 'b', 'c', 'd', 'e', 'f', 'g'] ]);
        return $pdfReport->generate();
        //return $pdfReport->renderHTML(); //use for debug
    }
}
```

## Licence
This bundle is under the MIT license. See the complete license in the bundle.

## Credits

Author: **Rodrigo Ramos**

*Discus Tecnologia*
