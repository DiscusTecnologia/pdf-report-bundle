# pdf-report-bundle
[![logo Discus Tecnologia](https://www.discustecnologia.com.br/images/logo_pq.png)](http://www.discustecnologia.com.br)

Symfony Bundle. Wrapper of wkhtmltopdf that uses twig templating to generate pdf files.

---

## Instalation
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

## Licence
This bundle is under the MIT license. See the complete license in the bundle.

## Credits

Author: **Rodrigo Ramos**

*Discus Tecnologia*
