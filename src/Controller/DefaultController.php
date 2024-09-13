<?php

namespace App\Controller;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

class DefaultController extends AbstractController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'data' => 'Upload product file',
            'products' => $this->productService->getProducts()
        ]);
    }

    #[Route('/upload', name: 'upload_json')]
    public function upload(Request $request): Response
    {
        $response = null;

        // Get request
        $file = $request->files->get('json_file');

        if ($file && $file->isValid()) {
            // Read file content
            $jsonContent = file_get_contents($file->getPathname());

            // Convert JSON to arary
            $data = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Send to Twig template
                //$response = $data;

                $tableName = $file->getClientOriginalName();;
                $primaryKey = 'EAN Nummer';

                $this->productService->createTableIfNotExists($data, $tableName, $primaryKey);
                $response = $this->productService->saveProducts($data, $tableName, $primaryKey);
            } else {
                // Json decode JSON error
                $response = 'Invalid JSON file';
            }
        } else {
            $response = 'No file uploaded';
        }
        
        #return $this->render('output.html.twig', [
        #    'jsonData' => $response
        #]);

        return $this->render('index.html.twig', [
            'data' => 'Upload product file',
            'products' => $this->productService->getProducts(),
            'jsonData' => $response
        ]);
    }

    #[Route('/productDetail', name: 'product_detail')]
    public function productDetail(Request $request): Response
    {
        return $this->render('detail.html.twig', [
            'product' => $this->productService->getProductByEan($request->getContent())
        ]);
    }

    #[Route('/downloadExcell/{ean}', name: 'download_excel')]
    public function downloadExcel($ean): Response
    {
        $product = $this->productService->getProductByEan($ean);

        $writer = WriterEntityFactory::createXLSXWriter();

        $writer->openToBrowser('products.xlsx');

        $headerRow = WriterEntityFactory::createRowFromArray(array_keys($product));
        $writer->addRow($headerRow);
        
        $row = WriterEntityFactory::createRowFromArray(array_values($product));
        $writer->addRow($row);

        $writer->close();

        return new Response('', Response::HTTP_OK);
    }

    #[Route('/compare', name: 'compare')]
    public function compare(Request $request): Response
    {
        $eans = json_decode($request->getContent());

        return $this->render('compare.html.twig', [
            'products' => $this->productService->getComparedProducts($eans->ean,$eans->compared)
        ]);
    }

    #[Route('/downloadPdf/{ean}/{compared}', name: 'download_pdf')]
    public function downloadPdf($ean, $compared)
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $products = $this->productService->getComparedProducts($ean,$compared);
        $html = $this->render('compare.html.twig', [
            'products' => $products,
        ]);
        echo '<pre>';
        var_dump($html);die;

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}