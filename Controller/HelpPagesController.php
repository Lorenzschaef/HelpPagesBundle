<?php

namespace Lorenzschaef\HelpPagesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HelpPagesController extends Controller
{
	/**
	 * Displays a Help Page
	 *
	 * @Route("/{path}", defaults={"path": ""}, requirements={"path": ".*"}, name="help_page_bundle_show")
	 */
	public function indexAction($path)
	{
		// configuration
		$basedir = $this->container->getParameter('basedir'); // get the directory form config
		$basedir = $this->get('kernel')->getRootDir().'/'.$basedir; // prepend the path to the kernel (/app)
		if(!is_dir($basedir)) throw new HttpException(500, 'Basedir not found');
		
		
		// 
		$fileInfo = $this->get("lorenzschaef_help_pages.help_pages")->getFileInfo($path, $basedir);
		if($fileInfo === false){
			$msg = 'The Help Page "'.$path.'" could not be found';
			throw new NotFoundHttpException($msg);
		}
		$content = $fileInfo->getContents();
				
		
		// apply markdown
		$content = $this->container->get('markdown.parser')->transformMarkdown($content);
		
		// toc
		$tocData = $this->get("lorenzschaef_help_pages.help_pages")->getTocData($basedir);
		
		// render
		return $this->render('LorenzschaefHelpPagesBundle::layout.html.twig', ['content' => $content, 'tocData' => $tocData]);
		
	}
}
