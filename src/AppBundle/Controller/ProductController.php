<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use AppBundle\Entity\Product;
use AppBundle\Form\ProductType;
use AppBundle\Service\FileUploader;

class ProductController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
 public function indexAction()
    {
die('test2');
        // replace this example code with whatever you need
//        return $this->render('default/index.html.twig', [
//            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
//        ]);
            return $this->redirectToRoute('app_product_new');

    }

    /**
     * @Route("/product/new", name="app_product_new")
     */
    public function newAction(Request $request) 
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $product->getBrochure();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            //ADDED - CLEAN DEPOSITORY DIRECTORY
            $this->clearDirectory();


            // Move the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('brochures_directory'),
                $fileName
            );

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $product->setBrochure($fileName);

            // ... persist the $product variable or any other work

            //return $this->redirect($this->generateUrl('app_product_list', array('filename' => $file->getClientOriginalName)));
            return $this->redirectToRoute('app_product_list', array(
                'fileName' => $file->getClientOriginalName(),
            ));
        }

        return $this->render('product/new.html.twig', array(
            'form' => $form->createView(), 'product' => $product
        ));
    }

    /**
     * @Route("/product/list/{fileName}", name="app_product_list")
     */
    public function listAction($fileName){
        $finder = new Finder();
        
        $directory = $this->getParameter('brochures_directory');
        $iterator = $finder->files()->in($directory);

        $i=0;
        $files = array();
        foreach ($iterator as $file) {
            $files[$i] = $file->getRealpath();
            $i++;
        } 

        $shellCommandBase = "java -jar \"".__DIR__.'\..\..\..\Jars\\'."tika-app-1.14.jar\" --metadata \"";
        $filesMetadata = array();       //initializing array of arrays of key value pairs per file
        for ($j=0;$j<count($files);$j++) {
            $command = $shellCommandBase.$files[$j].'"';
            exec($command, $output);

            $d = array();
            unset($oneFile);
            for ($k=0;$k<count($output);$k++) {
                unset($d);
                $d = explode(': ', $output[$k]);
                $oneFile[$d[0]] = $d[1];
                if ($d[0] = 'resourceName') {
                    $oneFile[$d[0]] = $fileName;
                }
            }

            $filesMetadata[$j] = $oneFile;
        }   

        
        return $this->render('product/display.html.twig',
            array(
                'filename' => $fileName,
                'filenames' => $filesMetadata,
        ));
    }


    private function clearDirectory () {
        $finder = new Finder();
        
        $directory = $this->getParameter('brochures_directory');
        $iterator = $finder->files()->in($directory);

        $files = array();
        foreach ($iterator as $file) {
            unlink($file->getRealpath());
        } 

    }


}


