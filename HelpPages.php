<?php
namespace Lorenzschaef\HelpPagesBundle;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
class HelpPages{
	/**
	 * Looks for a markdown file that matches the given route and returns its splFileInfo object.
	 *
	 * @param string $route The relative url to the page (after the prefix in the route config)
	 * @param string $basedir The base directory where the documents are kept
	 *
	 * @return SplFileInfo|bool The SplFileInfo of the matched file or false
	 */
	public function getFileInfo($route, $basedir){
		
		
		$routeArray = explode('/', $route);
		if($route == '') $routeArray = []; // otherwise the array will have one empty element (weird behaviour of explode())
		
		// if the route is empty, look for an index.md in the root dir.
		if($route == '') {
			$finder = Finder::create()->in($basedir)->depth(0)->name('index.md');
			if(count($finder) >= 1){
				$finderArray = iterator_to_array($finder);
				return reset($finderArray);
			}
		}
		
		// name/index.md
		if($fileInfo = $this->getFileInfoHelper($routeArray, 'index', $basedir)){
			return $fileInfo;
		}
		
		// name.md
		$filename = array_pop($routeArray);
		if($fileInfo = $this->getFileInfoHelper($routeArray, $filename, $basedir)){
			return $fileInfo;
		}
		
		return false;
	}
	
	/** 
	 * Helper method for getFileInfo. Looks for a file in a specific path and returns the splFileInfo or false.
	 *
	 * @param string $path The path to the file relative to the basedir and without the filename
	 * @param string $file The filename
	 * @param string $basedir The base directory where the documents are kept
	 *
	 * @return SplFileInfo|bool The SplFileInfo of the file or false
	 */
	private function getFileInfoHelper($path, $file, $basedir){
		
		$pathRegex = $this->routeArrayToPathRegex($path);
		$fileRegex = '/^(\d+_)?'.preg_quote($file, '/').'\.md$/';
		
		$finder = new Finder();
		$finder->files()->path($pathRegex)->name($fileRegex)->in($basedir);
		if(count($finder) >= 1){
			$finderArray = iterator_to_array($finder);
			return reset($finderArray);
		}else{
			return false;
		}
	}
	
	/** 
	 * Generates the regex for the path() method of the Finder used in getFileInfoHelper()
	 *
	 * @param mixed $routeArray Relative url as an array
	 * 
	 * @return string A regex that matches any valid directory for the given route
	 */
	private function routeArrayToPathRegex($routeArray){
		
		if(count($routeArray) == 0) return '';
		
		foreach($routeArray as &$dir){
			$dir = '(\d+_)?'.preg_quote($dir, '/');
		}
		
		return '/^'.implode('\/', $routeArray).'/';
	}
	
	/**
	 * Returns an array containing the data necesary for rendering the table of contents.
	 *
	 * @param string $basedir The base directory where the documents are kept
	 * @param string $startpath The path at which the generation of the TOC should start (used for the recursion)
	 *
	 * @return mixed An array containing the menu data
	 */
	public function getTocData($basedir, $startpath = ''){
		
		// select all files in the current directory (except index.md)
		$finder = Finder::create()->in($basedir)->notName('index.md')->sortByName();
		
		if($startpath == '') $finder->depth(0);
		else $finder->path($startpath.'/');
		
		
		$data = [];
		foreach($finder as $item){
			
			// if the item is a directory, look for an index.md to extract a title.
			if ($item->isDir()){
				// get index.md
				$dirpath = $startpath.($startpath == '' ? '' : '/').$item->getRelativePathname();
				$indexFileFinder = $finder = Finder::create()->in($basedir)->path($dirpath)->name('index.md');
				if(count($indexFileFinder) == 1){
					$array = iterator_to_array($indexFileFinder);
					$indexFile = reset($array);
					$title = $this->getTitle($indexFile);
				}
				// otherwise just use the filename as a title (fallback)
				else{ 
					$title = $item->getRelativePathname();
				}
				// set the data, including the data for its subelements (recursive)
				$data[] = 
					[
					'path' => $this->getRoute($item),
					'title' => $title, 
					'subpages' => $this->getTocData($basedir, $dirpath)
					];
			}
			// if it's a file, just set the data
			else{
				
				$data[] = 
					[
					'path' => $this->getRoute($item),
					'title' => $this->getTitle($item)
					];
			}
			
		}
		return $data;
	}
	
	
	/**
	 * Returns the relative url for the given file. The resulting relative url depends on how the given SplFileInfo has been created!
	 *
	 * @param SplFileInfo $fileInfo
	 *
	 * @return string The relative url of this file
	 */
	public function getRoute($fileInfo){
		$pathString = $fileInfo->getRelativePathname();
		$path = explode('/', $pathString);
		foreach($path as &$dir){
			$dir = preg_replace('/^(\d+_)?(.+?)(\.md)?$/', '$2', $dir);
		}
		array_filter($path);
		return implode('/', $path);
	}
	
	/**
	 * Extracts and returns the first level 1 title from the given markdown file.
	 *
	 * @param SplFileInfo $fileInfo
	 *
	 * @return string The title of this file
	 */
	private function getTitle($fileInfo){
		$content = $item->getContents();
		if(preg_match('/^#([^#].*)$/m', $content, $matches)){
			return $matches[1];
		}
		else{
			return "Untitled";
		}
	
	}
	
	
}

?>