<?php
namespace Lorenzschaef\HelpPagesBundle;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
class HelpPages{
		
	
	
	/** Returns the FileInfo of the file that corresponds to the given route
	*
	public function getFileInfo($path)
	{
		$resourceDir = $this->kernel->getRootDir().'/Resources/HelpPages';
		
		// parse and check path
		$path = explode('/', $path);
		$filename = array_pop($path);
		$currentDir = $resourceDir;
		
		
		foreach($path as $string){
			$finder = new Finder();
			$finder->directories()->in($currentDir)->name('/^(\d+_)?'.preg_quote($string, '/').'$/');
			if(count($finder) < 1){
				//throw new NotFoundHttpException("Page not found");
				return false;
			}
			$currentDir .= '/'.$finder[0]->getRelativePathname();
		}
				
		
		// check if file exists
		$finder = new Finder();
		$finder->files()->in($currentDir)->name('/^(\d+_)?'.preg_quote($filename, '/').'\.md(\.twig)?$/');
		if(iterator_count($finder) == 1){
			$array = iterator_to_array($finder);
			return reset($array); // returns the first item
		}
		// check if a directory with index file exists
		$finder = new Finder();
		$finder->files()->in($currentDir)->path('/^(\d+_)?'.preg_quote($filename, '/').'$/')->name('index.md');
		if(count($finder) == 1){
			$finder->rewind();
			return $finder->current();
			//$currentDir .= '/'.$finder[0]->getRelativePathname();
		}
		return false;
	}
	
	*/
	
	public function getFileInfo($route, $basedir){
		
		
		$routeArray = explode('/', $route);
		if($route == '') $routeArray = []; // otherwise the array will have 1 empty element (weird behaviour of explode())
		
		if($route == '') {
			$finder = Finder::create()->in($basedir)->depth(0)->name('index.md');
			if(count($finder) >= 1){
				$finderArray = iterator_to_array($finder);
				return reset($finderArray);
			}
		}
		
		
		//
		
		
		
		// name/index.md
		if($fileInfo = $this->searchFile($routeArray, 'index', $basedir)){
			return $fileInfo;
		}
		
		// name.md
		$filename = array_pop($routeArray);
		if($fileInfo = $this->searchFile($routeArray, $filename, $basedir)){
			return $fileInfo;
		}
		
		return false;	
	}
	
	/** Helper method for getFileInfo. Returns the fileInfo or false
	*/
	private function searchFile($path, $file, $basedir){
	
		$pathRegex = $this->routeArrayToPathRegex($path);
		$fileRegex = '/^(\d+_)?'.preg_quote($file, '/').'\.md$/';
		
		$finder = new Finder();
		//echo $pathRegex.' | '.$fileRegex.'<br />';
		$finder->files()->path($pathRegex)->name($fileRegex)->in($basedir);
		if(count($finder) >= 1){
			$finderArray = iterator_to_array($finder);
			return reset($finderArray);
		}else{
			return false;
		}
	}
	
	/** Generates the regex for the path() methode of the Finder
	*/
	private function routeArrayToPathRegex($routeArray){
		
		if(count($routeArray) == 0) return '';
		
		foreach($routeArray as &$dir){
			$dir = '(\d+_)?'.preg_quote($dir, '/');
		}
		
		return '/^'.implode('\/', $routeArray).'/';
	}
	
	
	
	public function getRoute($fileInfo){
		$pathString = $fileInfo->getRelativePathname();
		$path = explode('/', $pathString);
		foreach($path as &$dir){
			$dir = preg_replace('/^(\d+_)?(.+?)(\.md)?$/', '$2', $dir);
		}
		array_filter($path);
		return implode('/', $path);
	}
	
	
	public function getParent($fileInfo){
		
	}
	
	
	public function getTocData($basedir, $startpath = ''){
		
		$finder = Finder::create()->in($basedir)->notName('index.md')->sortByName();
		if($startpath == '') $finder->depth(0);
		else $finder->path($startpath.'/');
		
		
		$data = [];
		foreach($finder as $item){
		
			if ($item->isDir()){
				// get index.md
				$dirpath = $startpath.($startpath == '' ? '' : '/').$item->getRelativePathname();
				//echo $dirpath.'<br />';
				$indexFileFinder = $finder = Finder::create()->in($basedir)->path($dirpath)->name('index.md');
				if(count($indexFileFinder) == 1){
					$array = iterator_to_array($indexFileFinder);
					$indexFile = reset($array);
					$title = $this->getTitle($indexFile);
				}
				else{
					$title = $item->getRelativePathname();
				}
				// call recursion
				$data[] = 
					[
					'path' => $this->getRoute($item),
					'title' => $title, 
					'subpages' => $this->getTocData($basedir, $dirpath)
					];
			}
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
	
	private function getTitle($item){
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