<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Tools\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Api extends Controller_Module
{
	public function scss($action = 'reload', $only = [])
	{
		$list_scss_files = function(){
			$files = [];

			dir_scan('.', function($file) use (&$files){
				if (preg_match('#\.scss$#', $file, $match))
				{
					$files[] = $file;
				}
			});

			return $files;
		};

		$compile = function($files){
			$results = [];
			$preprocess = function($file){
				ob_start();
				include $file;
				return ob_get_clean();
			};

			foreach ($files as $file)
			{
				if (preg_match('#/sass/((?!_)[a-z0-9_.-]+)\.scss$#', $file, $match))
				{
					$path = preg_replace('#/sass/[^/]*?\.scss#', '', $file);
					$css  = $path.'/'.$match[1].'.css';

					$scss = new \ScssPhp\ScssPhp\Compiler();
					$scss->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);
					$scss->setSourceMap(\ScssPhp\ScssPhp\Compiler::SOURCE_MAP_FILE);
					$scss->setSourceMapOptions([
						'sourceMapFilename' => basename($css),
						'sourceMapURL'     => $match[1].'.css.map',
						'sourceRoot'       => '/'
					]);

					$filesystem = new \ScssPhp\ScssPhp\Importer\FilesystemImporter(realpath($path.'/sass'));
					$importer = new class($filesystem, $preprocess) extends \ScssPhp\ScssPhp\Importer\Importer {
						private $filesystem;
						private $preprocess;

						public function __construct($filesystem, $preprocess)
						{
							$this->filesystem = $filesystem;
							$this->preprocess = $preprocess;
						}

						public function canonicalize(\League\Uri\Contracts\UriInterface $url): ?\League\Uri\Contracts\UriInterface
						{
							return $this->filesystem->canonicalize($url);
						}

						public function load(\League\Uri\Contracts\UriInterface $url): ?\ScssPhp\ScssPhp\Importer\ImporterResult
						{
							$file = \ScssPhp\ScssPhp\Util\Path::fromUri($url);
							return new \ScssPhp\ScssPhp\Importer\ImporterResult(
								call_user_func($this->preprocess, $file),
								\ScssPhp\ScssPhp\Syntax::forPath($file),
								$url
							);
						}

						public function couldCanonicalize(\League\Uri\Contracts\UriInterface $url, \League\Uri\Contracts\UriInterface $canonicalUrl): bool
						{
							return $this->filesystem->couldCanonicalize($url, $canonicalUrl);
						}

						public function __toString(): string
						{
							return 'HiddenCMS SCSS importer';
						}
					};

					try
					{
						$md5 = is_file($css) ? md5_file($css) : NULL;
						$source = $preprocess($file);
						$compiled = $scss->compileString($source, realpath($file), $importer);
						file_put_contents($css, $compiled->getCss());

						if ($compiled->getSourceMap() !== NULL)
						{
							file_put_contents($css.'.map', $compiled->getSourceMap());
						}

						if ($md5 != md5_file($css))
						{
							$results[] = $css;
						}
					}
					catch (\Throwable $e)
					{
						echo "Error $file\n\t--> ".$e->getMessage()."\n";
					}
				}
			}

			return $results;
		};

		$files = $list_scss_files();

		if ($only)
		{
			$files = array_values(array_intersect($files, (array)$only));
		}

		if ($action == 'reload')
		{
			foreach ($compile($files) as $file)
			{
				echo $file."\n";
			}

			$this->config('version_css', time());

			return 'OK';
		}
		else if ($action == 'watch')
		{
			echo "Watching...\n";

			$files = [];
			$first = TRUE;

			while (TRUE)
			{
				$need_update = [];

				$scan = $list_scss_files();

				if ($only)
				{
					$scan = array_values(array_intersect($scan, (array)$only));
				}

				foreach ($scan as $file)
				{
					if (!array_key_exists($file, $files))
					{
						$files[$file] = filemtime($file);
						$need_update['Added'][] = $file;
					}
					else if ($files[$file] != ($time = filemtime($file)))
					{
						$files[$file] = $time;
						$need_update['Updated'][] = $file;
					}
				}

				foreach (array_diff(array_keys($files), $scan) as $file)
				{
					$need_update['Removed'][] = $file;
					unset($files[$file]);
				}

				if ($need_update)
				{
					foreach ($updated = $compile($scan) as $file)
					{
						$need_update['-->'][] = $file;
					}

					if (!$first && isset($need_update['-->']))
					{
						foreach ($need_update as $type => $f)
						{
							echo "$type\n".implode("\n", array_map(function($a){
								return "\t".$a;
							}, $f))."\n";
						}
					}

					$first = FALSE;
				}

				usleep(200000);
			}
		}
	}
}


