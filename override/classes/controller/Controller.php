<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

//
// IMPORTANT : don't forget to delete the underscore _ in the file name if you want to use it !
//

$GLOBALS['debugtoolbar'] = array();

require_once(_PS_MODULE_DIR_.'debugtoolbar/debugtoolbar.php');

function developpementErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno))
		return;
	switch($errno)
	{
		case E_ERROR:
			echo '[PHP Error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_WARNING:
			echo '[PHP Warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_PARSE:
			echo '[PHP Parse #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_NOTICE:
			echo '[PHP Notice #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_CORE_ERROR:
			echo '[PHP Core #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_CORE_WARNING:
			echo '[PHP Core warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_COMPILE_ERROR:
			echo '[PHP Compile #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_COMPILE_WARNING:
			echo '[PHP Compile warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_USER_ERROR:
			echo '[PHP Error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_USER_WARNING:
			echo '[PHP User warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_USER_NOTICE:
			echo '[PHP User notice #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_STRICT:
			echo '[PHP Strict #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		case E_RECOVERABLE_ERROR:
			echo '[PHP Recoverable error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
			break;
		default:
			echo '[PHP Unknown error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
	}
	die;
	return true;
}


function debug($debug, $name = null)
{
	$GLOBALS['debugtoolbar'] = (($name !== null) ? '<b>Debug var :</b> '.$name.'<br />' : '').'<pre>'.print_r($debug, true).'</pre>';
}

/** 
* Converts bytes into human readable file size. 
* 
* @param string $bytes 
* @return string human readable file size (2,87 Мб)
* @author Mogilev Arseny 
*/ 
function FileSizeConvert($bytes)
{
    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "tB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "gB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "mB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "kB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "bytes",
                "VALUE" => 1
            ),
        );

    $result = '';
    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

abstract class Controller extends ControllerCore
{
	public $_memory = array();
	public $_time = array();
	private static $_footer = true;

	public static function disableParentCalls()
	{
		self::$_footer = false;
	}

	private function displayMemoryColor($n)
	{
		$n /= 1048576;
		if ($n > 3)
			return '<span style="color:#ff4141">'.round($n, 2).' Mb</span>';
		if ($n > 1)
			return '<span style="color:#ef8400">'.round($n, 2).' Mb</span>';
		return '<span style="color:#90bd00">'.round($n, 2).' Mb</span>';
	}

	private function displayPeakMemoryColor($n)
	{
		$n /= 1048576;
		if ($n > 16)
			return '<span style="color:#ff4141">'.round($n, 1).' Mb</span>';
		if ($n > 12)
			return '<span style="color:#ef8400">'.round($n, 1).' Mb</span>';
		return '<span style="color:#90bd00">'.round($n, 1).' Mb</span>';
	}

	private function displaySQLQueries($n)
	{
		if ($n > 150)
			return '<span style="color:#ff4141">'.$n.' queries</span>';
		if ($n > 100)
			return '<span style="color:#ef8400">'.$n.' queries</span>';
		return '<span style="color:#90bd00">'.$n.' quer'.($n == 1 ? 'y' : 'ies').'</span>';
	}

	private function displayRowsBrowsed($n)
	{
		if ($n > 200)
			return '<span style="color:#ff4141">'.$n.' rows browsed</span>';
		if ($n > 50)
			return '<span style="color:#ef8400">'.$n.'  rows browsed</span>';
		return '<span style="color:#90bd00">'.$n.' row'.($n == 1 ? '' : 's').' browsed</span>';
	}

	private function displayLoadTimeColor($n, $pre = false)
	{
		$balise = ($pre ? 'pre' : 'span');
		if ($n > 1)
			return '<'.$balise.' style="color:#ff4141">'.round($n, 3).'s</'.$balise.'>';
		if ($n > 0.5)
			return '<'.$balise.' style="color:#ef8400">'.round($n * 1000).'ms</'.$balise.'>';
		return '<'.$balise.' style="color:#90bd00">'.round($n * 1000).'ms</'.$balise.'>';
	}

	private function getTimeColor($n)
	{
		if ($n > 4)
			return 'style="color:#ff4141"';
		if ($n > 2)
			return 'style="color:#ef8400"';
		return 'style="color:#90bd00"';
	}

	private function getQueryColor($n)
	{
		if ($n > 5)
			return 'style="color:#ff4141"';
		if ($n > 2)
			return 'style="color:#ef8400"';
		return 'style="color:#90bd00"';
	}

	private function getTableColor($n)
	{
		if ($n > 30)
			return 'style="color:#ff4141"';
		if ($n > 20)
			return 'style="color:#ef8400"';
		return 'style="color:#90bd00"';
	}

	private function getObjectModelColor($n)
	{
		if ($n > 50)
			return 'style="color:#ff4141"';
		if ($n > 10)
			return 'style="color:#ef8400"';
		return 'style="color:#90bd00"';
	}

	public function __construct()
	{
		parent::__construct();

		// error management
		set_error_handler('developpementErrorHandler');
		ini_set('html_errors', 'on');
		ini_set('display_errors', 'on');
		error_reporting(E_ALL | E_STRICT);

		if (!self::$_footer)
			return;

		$this->_memory['config'] = memory_get_usage();
		$this->_mempeak['config'] = memory_get_peak_usage();
		$this->_time['config'] = microtime(true);

		parent::__construct();
		$this->_memory['constructor'] = memory_get_usage();
		$this->_mempeak['constructor'] = memory_get_peak_usage();
		$this->_time['constructor'] = microtime(true);
	}

	public function run()
	{
		if (!DebugToolbar::isEnable())
			return parent::run();

		$this->init();
		$this->_memory['init'] = memory_get_usage();
		$this->_mempeak['init'] = memory_get_peak_usage();
		$this->_time['init'] = microtime(true);

		if ($this->checkAccess())
		{
			$this->_memory['checkAccess'] = memory_get_usage();
			$this->_mempeak['checkAccess'] = memory_get_peak_usage();
			$this->_time['checkAccess'] = microtime(true);

			if (!$this->content_only && ($this->display_header || (isset($this->className) && $this->className)))
				$this->setMedia();
			$this->_memory['setMedia'] = memory_get_usage();
			$this->_mempeak['setMedia'] = memory_get_peak_usage();
			$this->_time['setMedia'] = microtime(true);

			// postProcess handles ajaxProcess
			$this->postProcess();
			$this->_memory['postProcess'] = memory_get_usage();
			$this->_mempeak['postProcess'] = memory_get_peak_usage();
			$this->_time['postProcess'] = microtime(true);

			if (!empty($this->redirect_after))
				$this->redirect();

			if (!$this->content_only && ($this->display_header || (isset($this->className) && $this->className)))
				$this->initHeader();
			$this->_memory['initHeader'] = memory_get_usage();
			$this->_mempeak['initHeader'] = memory_get_peak_usage();
			$this->_time['initHeader'] = microtime(true);

			$this->initContent();
			$this->_memory['initContent'] = memory_get_usage();
			$this->_mempeak['initContent'] = memory_get_peak_usage();
			$this->_time['initContent'] = microtime(true);

			if (!$this->content_only && ($this->display_footer || (isset($this->className) && $this->className)))
				$this->initFooter();
			$this->_memory['initFooter'] = memory_get_usage();
			$this->_mempeak['initFooter'] = memory_get_peak_usage();
			$this->_time['initFooter'] = microtime(true);

			// default behavior for ajax process is to use $_POST[action] or $_GET[action]
			// then using displayAjax[action]
			if ($this->ajax)
			{
				$action = Tools::getValue('action');
				if (!empty($action) && method_exists($this, 'displayAjax'.Tools::toCamelCase($action))) 
					$this->{'displayAjax'.$action}();
				elseif (method_exists($this, 'displayAjax'))
					$this->displayAjax();
			}
			else
				$this->displayDebug();
		}
		else
		{
			$this->initCursedPage();
			$this->displayDebug();
		}
	}

	private function ini_get_display_errors()
	{
		$a = 'display_errors';
		$b = ini_get($a);
		switch (strtolower($b))
		{
			case 'on':
			case 'yes':
			case 'true':
				return 'assert.active' !== $a;
			case 'stdout':
			case 'stderr':
				return 'display_errors' === $a;
			default:
				return (bool)(int)$b;
		}
	}
	
	private function sizeofvar($var)
	{
		$start_memory = memory_get_usage();
		try {
			$tmp = Tools::unSerialize(serialize($var));
		} catch (Exception $e) {
			$tmp = $this->getVarData($var);
		}
		$size = memory_get_usage() - $start_memory;
		return $size;
	}
	
	private function getVarData($var)
	{
		if (is_object($var))
			return $var;
		return (string)$var;
	}

	public function displayDebug()
	{
		global $start_time;

		$this->display();
		$this->_memory['display'] = memory_get_usage();
		$this->_mempeak['display'] = memory_get_peak_usage();
		$this->_time['display'] = microtime(true);

		if (!$this->ini_get_display_errors())
			return;

		$memory_peak_usage = memory_get_peak_usage();
			
		$hr = '<hr style="color:#F5F5F5;margin:2px" />';

		$totalSize = 0;
		foreach (get_included_files() as $file)
			$totalSize += filesize($file);

		$totalQueryTime = 0;
		foreach (Db::getInstance()->queries as $data)
			$totalQueryTime += $data['time'];

		$hooktime = Hook::getHookTime();
		arsort($hooktime);
		$totalHookTime = 0;
		foreach ($hooktime as $time)
			$totalHookTime += $time;

		$hookMemoryUsage = Hook::getHookMemoryUsage();
		arsort($hookMemoryUsage);
		$totalHookMemoryUsage = 0;
		foreach ($hookMemoryUsage as $usage)
			$totalHookMemoryUsage += $usage;

		$globalSize = array();
		$totalGlobalSize = 0;
		foreach ($GLOBALS as $key => $value)
			if ($key != 'GLOBALS')
			{
				$totalGlobalSize += ($size = $this->sizeofvar($value));
				if ($size > 1024)
					$globalSize[$key] = round($size / 1024, 1);
			}
		arsort($globalSize);

		$cache = Cache::retrieveAll();
		$totalCacheSize = $this->sizeofvar($cache);

		$output = '';
		$output .= '<link href="'.Tools::getShopDomain(true).'/modules/debugtoolbar/views/assets/css/debugtoolbar.css" rel="stylesheet" type="text/css" media="all">';

		$output .= '	<div class="debugtoolbar">

							<div class="debugtoolbar-window">
								<div class="debugtoolbar-content-area">
		';

		/* LOAD TIME */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-load-times">';
		$output .= '					<table>
											<tr>
												<th>Name</th>
												<th>Running Time (ms)</th>
											</tr>
											<tr>
												<td class="debugtoolbar-table-first">Global Application</td>
												<td>'.$this->displayLoadTimeColor($this->_time['display'] - $start_time, true).'</td>
											</tr>
		';
		$last_time = $start_time;
		foreach ($this->_time as $k => $time)
		{
			$output .= '
											<tr>
												<td class="debugtoolbar-table-first">'.ucfirst($k).'</td>
												<td>'.$this->displayLoadTimeColor($time - $last_time, true).'</td>
											</tr>
			';
			$last_time = $time;
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /LOAD TIME */

		/* HOOK PROCESSING */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-hook-processing">';
		$output .= '					<table>
											<tr>
												<th>Name</th>
												<th>Running Time (ms) / Memory Usage</th>
											</tr>
											<tr>
												<td class="debugtoolbar-table-first">Global Hook</td>
												<td><pre>'.$this->displayLoadTimeColor($totalHookTime).' / '.$this->displayMemoryColor($totalHookMemoryUsage).'</pre></td>
											</tr>
		';
		foreach ($hooktime as $hook => $time)
		{
			$output .= '
											<tr>
												<td class="debugtoolbar-table-first">'.ucfirst($hook).'</td>
												<td><pre>'.$this->displayLoadTimeColor($time).' / '.$this->displayMemoryColor($hookMemoryUsage[$hook]).'</pre></td>
											</tr>
			';
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /HOOK PROCESSING */

		/* MEMORY PEAK USAGE */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-memory-peak-usage">';
		$output .= '					<table>
											<tr>
												<th>Name</th>
												<th>Memory Usage (Global)</th>
											</tr>
											<tr>
												<td class="debugtoolbar-table-first">Global Memory</td>
												<td><pre>'.$this->displayPeakMemoryColor($memory_peak_usage).'</pre></td>
											</tr>
		';
		foreach ($this->_memory as $k => $memory)
		{
			$last_memory = 0;
			$output .= '
											<tr>
												<td class="debugtoolbar-table-first">'.ucfirst($k).'</td>
												<td><pre>'.$this->displayMemoryColor($memory - $last_memory).' ('.$this->displayPeakMemoryColor($this->_mempeak[$k]).')</pre></td>
											</tr>
			';
			$last_memory = $memory;
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /MEMORY PEAK USAGE */

		/* INCLUDED FILES */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-included-files">';
		$output .= '					<table>
											<tr>
												<th>#</th>
												<th>File</th>
												<th>Size</th>
											</tr>
											<tr>
												<td class="debugtoolbar-table-first">Size global files</td>
												<td><pre>'.$this->displayMemoryColor($totalSize).'</pre></td>
												<td>-</td>
											</tr>
		';
		$i = 1;
		foreach (get_included_files() as $file)
		{
			$f = ltrim(str_replace('\\', '/', str_replace(_PS_ROOT_DIR_, '', $file)), '/');
			$f = dirname($file).'/<span style="color: #0080b0">'.basename($file).'</span>';
			$output .= '
											<tr>
												<td class="debugtoolbar-table-first">'.$i.'</td>
												<td><pre>'.$f.'</pre></td>
												<td>'.FileSizeConvert(filesize($file)).'</td>
											</tr>
			';
			$i++;
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /INCLUDED FILES */

		/* SQL QUERIES */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-sql-queries">';
		$output .= '					<table>
											<tr>
												<th>Time</th>
												<th>Query</th>
											</tr>
		';
		$array_queries = array();
		$queries = Db::getInstance()->queries;
		uasort($queries, 'prestashop_querytime_sort');
		foreach ($queries as $data)
		{
			$query_row = array(
				'time' => $data['time'],
				'query' => $data['query'],
				'location' => $data['file'].':<span style="color:#0080b0">'.$data['line'].'</span>',
				'filesort' => false,
				'rows' => 1,
				'group_by' => false
			);
			if (preg_match('/^\s*select\s+/i', $data['query']))
			{
				$explain = Db::getInstance()->executeS('explain '.$data['query']);
				if (stristr($explain[0]['Extra'], 'filesort'))
					$query_row['filesort'] = true;
				foreach ($explain as $row)
					$query_row['rows'] *= $row['rows'];
				if (stristr($data['query'], 'group by') && !preg_match('/(avg|count|min|max|group_concat|sum)\s*\(/i', $data['query']))
					$query_row['group_by'] = true;
			}
			$array_queries[] = $query_row;
		}
		foreach ($array_queries as $data)
		{
			$filestortGroup = '';
			if (preg_match('/^\s*select\s+/i', $data['query']))
			{
				if ($data['filesort'])
					$filestortGroup .= '<b '.$this->getTimeColor($data['time'] * 1000).'>USING FILESORT</b> - ';
				$filestortGroup .= $this->displayRowsBrowsed($data['rows']);
				if ($data['group_by'])
					$filestortGroup .= ' - <b>Useless GROUP BY need to be removed</b>';
			}

			$output .= '
											<tr>
												<td class="debugtoolbar-table-title" colspan="2"><strong>Query in : </strong>'.$data['location'].' - '.$filestortGroup.'</td>
											</tr>
											<tr>
												<td class="debugtoolbar-table-first"><span '.$this->getTimeColor($data['time'] * 1000).'>'.round($data['time'] * 1000, 3).' ms</span></td>
												<td><pre>'.htmlspecialchars($data['query'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
											</tr>
			';
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /SQL QUERIES */

		/* TABLES */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-sql-table">';
		$output .= '					<table>
											<tr>
												<th>Nb call</th>
												<th>Table</th>
											</tr>
		';
		$tables = Db::getInstance()->tables;
		arsort($tables);
		foreach ($tables as $table => $nb)
		{
			$output .= '
											<tr>
												<td class="debugtoolbar-table-first"><b '.$this->getTableColor($nb).'>'.$nb.'</b></td>
												<td><pre>'.$table.'</pre></td>
											</tr>
			';
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /TABLES */


		/* OBJECTMODEL */
		if (isset(ObjectModel::$debug_list))
		{
			$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-objectmodel-instance">';
			$output .= '					<table>
												<tr>
													<th>Nb call</th>
													<th>ObjectModel Instance</th>
												</tr>
			';
			$list = ObjectModel::$debug_list;
			uasort($list, create_function('$a,$b', 'return (count($a) < count($b)) ? 1 : -1;'));
			$i = 0;
			foreach ($list as $class => $info)
			{
				echo '';
				echo '';
				$i++;
				$output .= '
												<tr>
													<td class="debugtoolbar-table-first"><b '.$this->getObjectModelColor(count($info)).'>'.count($info).'</b></td>
													<td><a href="#" onclick="$(\'#object_model_'.$i.'\').css(\'display\', $(\'#object_model_'.$i.'\').css(\'display\') == \'none\' ? \'block\' : \'none\'); return false" style="color:#0080b0">'.$class.'</a>
														<div id="object_model_'.$i.'" style="display: none">';
														foreach ($info as $trace)
															$output .= ltrim(str_replace(array(_PS_ROOT_DIR_, '\\'), array('', '/'), $trace['file']), '/').' ['.$trace['line'].']<br />';
														$output .=  '</div></td>
												</tr>
				';
			}
			$output .= '
											</table>
			';
			$output .= '				</div>';
		}
		/* /OBJECTMODEL */

		/* GETALLHEADERS */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-getallheader">';
		$output .= '					<table>
											<tr>
												<th>Name</th>
												<th>Value</th>
											</tr>
		';
		$getallheaders = getallheaders();
		foreach ($getallheaders as $name => $value)
		{
			$output .= '
											<tr>
												<td class="debugtoolbar-table-first">'.$name.'</td>
												<td><pre>'.$value.'</pre></td>
											</tr>
			';
		}
		stream_context_set_default(array('http' => array('method' => 'HEAD')));
		$url = Tools::getShopProtocol().apache_getenv("HTTP_HOST") . apache_getenv("REQUEST_URI");
		$get_headers = get_headers($url, 1);
		foreach ($get_headers as $name => $value)
		{
			$output .= '
											<tr>
												<td class="debugtoolbar-table-first">'.$name.'</td>
			';
			if(is_array($value)) :
				foreach ($value as $key => $vArr)
					$output .= '					<td><pre>'.$vArr.'</pre></td>';
			else :
				$output .= '						<td><pre>'.$value.'</pre></td>';
			endif;		
			$output .= '
											</tr>
			';
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /GETALLHEADERS */

		/* DATA */
		$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-getpost-data">';
		$output .= '					<table>
											<tr>
												<th>Name</th>
												<th>Value</th>
											</tr>
											<tr>
												<td class="debugtoolbar-table-title" colspan="2">Post Data</td>
											</tr>
		';
		$post = isset($_POST) ? $_POST : array();
		if(!count($post))
			$output .= '<tr><td colspan="2">No POST Data Found</td></tr>';
		else
		{
			foreach ($post as $name => $value)
			{
				$output .= '
												<tr>
													<td class="debugtoolbar-table-first">'.$name.'</td>
													<td><pre>'.$value.'</pre></td>
												</tr>
				';
			}
		}

		$output .= '						<tr>
												<td class="debugtoolbar-table-title" colspan="2">Get Data</td>
											</tr>
		';

		$get = isset($_GET) ? $_GET : array();
		if(!count($get))
			$output .= '<tr><td colspan="2">No GET Data Found</td></tr>';
		else
		{
			foreach ($get as $name => $value)
			{
				$output .= '
												<tr>
													<td class="debugtoolbar-table-first">'.$name.'</td>
													<td><pre>'.$value.'</pre></td>
												</tr>
				';
			}
		}
		$output .= '
										</table>
		';
		$output .= '				</div>';
		/* /DATA */

		/* DEBUG */
		if(count($GLOBALS['debugtoolbar']))
		{
			$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-debug">';
			$output .= '					<table>
												<tr>
													<th>Debug</th>
												</tr>
			';
			$output .= '
												<tr>
													<td colspan="2">'.$GLOBALS['debugtoolbar'].'</td>
												</tr>
			';
			$output .= '
											</table>
			';
			$output .= '				</div>';
		}
		/* /DEBUG */

		/* PS INFOS */
		if (isset($this->context->employee->id)) :
			$ps_infos = array(
				'version' => array(
					'php'                => phpversion(),
					'server'             => $_SERVER['SERVER_SOFTWARE'],
					'memory_limit'       => ini_get('memory_limit'),
					'max_execution_time' => ini_get('max_execution_time')
				),
				'database' => array(
					'version' => Db::getInstance()->getVersion(),
					'prefix'  => _DB_PREFIX_,
					'engine'  => _MYSQL_ENGINE_,
				),
				'uname' => function_exists('php_uname') ? php_uname('s').' '.php_uname('v').' '.php_uname('m') : '',
				'apache_instaweb' => Tools::apacheModExists('mod_instaweb'),
				'shop' => array(
					'ps'    => _PS_VERSION_,
					'url'   => Tools::getHttpHost(true).__PS_BASE_URI__,
					'theme' => _THEME_NAME_,
				),
				'mail' => Configuration::get('PS_MAIL_METHOD') == 1,
				'smtp' => array(
					'server'     => Configuration::get('PS_MAIL_SERVER'),
					'user'       => Configuration::get('PS_MAIL_USER'),
					'password'   => Configuration::get('PS_MAIL_PASSWD'),
					'encryption' => Configuration::get('PS_MAIL_SMTP_ENCRYPTION'),
					'port'       => Configuration::get('PS_MAIL_SMTP_PORT'),
				),
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			);

			$tests                   = ConfigurationTest::getDefaultTests();
			$tests_op                = ConfigurationTest::getDefaultTestsOp();
			$params_required_results = ConfigurationTest::check($tests);
			$params_optional_results = ConfigurationTest::check($tests_op);

			$tests_errors = array(
				'phpversion' => 'Update your PHP version',
				'upload' => 'Configure your server to allow file uploads',
				'system' => 'Configure your server to allow the creation of directories and files with write permissions.',
				'gd' => 'Enable the GD library on your server.',
				'mysql_support' => 'Enable the MySQL support on your server.',
				'config_dir' => 'Set write permissions for the "config" folder.',
				'cache_dir' => 'Set write permissions for the "cache" folder.',
				'sitemap' => 'Set write permissions for the "sitemap.xml" file.',
				'img_dir' => 'Set write permissions for the "img" folder and subfolders.',
				'log_dir' => 'Set write permissions for the "log" folder and subfolders.',
				'mails_dir' => 'Set write permissions for the "mails" folder and subfolders.',
				'module_dir' => 'Set write permissions for the "modules" folder and subfolders.',
				'theme_lang_dir' => 'Set the write permissions for the "themes'._THEME_NAME_.'/lang/" folder and subfolders, recursively.',
				'translations_dir' => 'Set write permissions for the "translations" folder and subfolders.',
				'customizable_products_dir' => 'Set write permissions for the "upload" folder and subfolders.',
				'virtual_products_dir' => 'Set write permissions for the "download" folder and subfolders.',
				'fopen' => 'Allow the PHP fopen() function on your server',
				'register_globals' => 'Set PHP "register_global" option to "Off"',
				'gz' => 'Enable GZIP compression on your server.'
			);

			$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-ps-info">';
			$output .= "
											<script type=\"text/javascript\">
												$(document).ready(function()
												{
													$.ajax({
														type: 'GET',
														url: '".$this->context->link->getAdminLink('AdminInformation')."',
														data: {
															'action': 'checkFiles',
															'ajax': 1
														},
														dataType: 'json',
														success: function(json)
														{
															var tab = {
																'missing': 'Missing files',
																'updated': 'Updated files'
															};

															if (json.missing.length || json.updated.length)
																$('#changedFilesDebugtoolbar').html('<div style=\"color:#ef8400;\">Changed/missing files have been detected.</div>');
															else
																$('#changedFilesDebugtoolbar').html('<div style=\"color:#0080b0;\">No change has been detected in your files</div>');

															$.each(tab, function(key, lang)
															{
																if (json[key].length)
																{
																	var html = $('<ul>').attr('id', key+'_files');
																	$(json[key]).each(function(key, file)
																	{
																		html.append($('<li>').html(file))
																	});
																	$('#changedFilesDebugtoolbar')
																		.append($('<h3>').html(lang+' ('+json[key].length+')'))
																		.append(html);
																}
															});
														}
													});
												});
											</script>
			";
			$output .= '					<table>
												<tr>
													<th>Name</th>
													<th>Value</th>
												</tr>
			';
			$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>Server Informations</strong></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Server</td>
													<td><pre>'.htmlspecialchars($ps_infos['uname'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Logiciel Serveur</td>
													<td><pre>'.htmlspecialchars($ps_infos['version']['server'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">PHP Version</td>
													<td><pre>'.htmlspecialchars($ps_infos['version']['php'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Memory Limit</td>
													<td><pre>'.htmlspecialchars($ps_infos['version']['memory_limit'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Max Execution Time</td>
													<td><pre>'.htmlspecialchars($ps_infos['version']['max_execution_time'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
			';
			$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>Database Informations</strong></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">MySQL Version</td>
													<td><pre>'.htmlspecialchars($ps_infos['database']['version'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">MySQL Engine</td>
													<td><pre>'.htmlspecialchars($ps_infos['database']['engine'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">MySQL Prefix</td>
													<td><pre>'.htmlspecialchars($ps_infos['database']['prefix'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
			';
			$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>Store Informations</strong></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">PrestaShop Version</td>
													<td><pre>'.htmlspecialchars($ps_infos['shop']['ps'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Store Url</td>
													<td><pre>'.htmlspecialchars($ps_infos['shop']['url'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Themes Use</td>
													<td><pre>'.htmlspecialchars($ps_infos['shop']['theme'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
			';
			if(!empty($ps_infos['mail'])) :
				$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>Email Setting</strong></td>
												</tr>
												<tr>
													<td><pre>You are using the PHP mail function.</pre></td>
												</tr>
				';
			else :
				$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>Email Setting</strong></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">SMTP Server</td>
													<td><pre>'.htmlspecialchars($ps_infos['smtp']['server'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Cryptage</td>
													<td><pre>'.htmlspecialchars($ps_infos['smtp']['encryption'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Port</td>
													<td><pre>'.htmlspecialchars($ps_infos['smtp']['port'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Login</td>
													<td><pre>'.(!empty($ps_infos['smtp']['user']) ? '<span style="color:#90bd00;font-weight:bold;">OK</span>' : '<span style="color:#ff4141;font-weight:bold;">Not defined</span>').'</pre></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Password</td>
													<td><pre>'.(!empty($ps_infos['smtp']['password']) ? '<span style="color:#90bd00;font-weight:bold;">OK</span>' : '<span style="color:#ff4141;font-weight:bold;">Not defined</span>').'</pre></td>
												</tr>
				';
			endif;
			$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>Your information</strong></td>
												</tr>
												<tr>
													<td class="debugtoolbar-table-first">Your web browser</td>
													<td><pre>'.htmlspecialchars($ps_infos['user_agent'], ENT_NOQUOTES, 'utf-8', false).'</pre></td>
												</tr>
			';
			$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>Check your configuration</strong></td>
												</tr>
			';
			$output .= '
												<tr>
													<td class="debugtoolbar-table-first">Required parameters</td>
			';
			if (!in_array('fail', $params_required_results)) :
				$output .= '
													<td><pre><span style="color:#90bd00;font-weight:bold;">OK</span></pre></td>
				';
			else : 
				$output .= '
													<td>
														<pre><span style="color:#ff4141;font-weight:bold;">Please fix the following error(s)</span></pre>
														<ul>
				';
				foreach ($params_required_results as $key => $value)
					if ($value == 'fail')
						$output .= '						<li>'.$tests_errors[$key].'</li>';
				$output .= '
														</ul>
													</td>
				';
			endif;
			$output .= '
												</tr>
			';
			$output .= '
												<tr>
													<td class="debugtoolbar-table-first">Optional parameters</td>
			';
			if (!in_array('fail', $params_optional_results)) :
				$output .= '
													<td><pre><span style="color:#90bd00;font-weight:bold;">OK</span></pre></td>
				';
			else : 
				$output .= '
													<td>
														<pre><span style="color:#ff4141;font-weight:bold;">Please fix the following error(s)</span></pre>
														<ul>
				';
				foreach ($params_optional_results as $key => $value)
					if ($value == 'fail')
						$output .= '						<li>'.$key.'</li>';
				$output .= '
														</ul>
													</td>
				';
			endif;
			$output .= '
												</tr>
			';
			$output .= '
												<tr>
													<td class="debugtoolbar-table-title" colspan="2"><strong>List of changed files</strong></td>
												</tr>
			';
			$output .= '
												<tr>
													<td colspan="2"><div id="changedFilesDebugtoolbar"><img src="../img/admin/ajax-loader.gif" /> Checking files...</div></td>
												</tr>
			';
			$output .= '
											</table>
			';
			$output .= '				</div>';		
		else :
			$output .= '				<div class="debugtoolbar-tab-pane debugtoolbar-table debugtoolbar-ps-info">';
			$output .= '					<table>
												<tr>
													<td><pre><span style="color:#ff4141;font-weight:bold;">Not display in Front office</span></pre></td>
												</tr>
											</table>
										</div>
			';
		endif;
		/* /PS INFOS */


		$output .= '			</div>
							</div>
						
							<ul id="debugtoolbar-open-tabs" class="debugtoolbar-tabs">

								<!-- LOAD TIME -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-load-times">Time <span class="debugtoolbar-count">'.$this->displayLoadTimeColor($this->_time['display'] - $start_time).'</span></a></li>
								<!-- /LOAD TIME -->

								<!-- HOOK PROCESSING -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-hook-processing">Hook <span class="debugtoolbar-count">'.$this->displayLoadTimeColor($totalHookTime).' / '.$this->displayMemoryColor($totalHookMemoryUsage).'</span></a></li>
								<!-- /HOOK PROCESSING -->

								<!-- MEMORY PEAK USAGE -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-memory-peak-usage">Memory <span class="debugtoolbar-count">'.$this->displayPeakMemoryColor($memory_peak_usage).'</span></a></li>
								<!-- /MEMORY PEAK USAGE -->

								<!-- INCLUDED FILES -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-included-files">Files <span class="debugtoolbar-count">'.sizeof(get_included_files()).'</span></a></li>
								<!-- /INCLUDED FILES -->

								<!-- SQL QUERIES -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-sql-queries">Sql <span class="debugtoolbar-count">'.$this->displaySQLQueries(count(Db::getInstance()->queries)).'</span><span class="debugtoolbar-count">'.$this->displayLoadTimeColor($totalQueryTime).'</span></a></li>
								<!-- /SQL QUERIES -->

								<!-- TABLE -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-sql-table">Table</a></li>
								<!-- /TABLE -->

								<!-- OBJECTMODEL -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-objectmodel-instance">ObjectModel</a></li>
								<!-- /OBJECTMODEL -->

								<!-- GETALLHEADERS -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-getallheader">Header</a></li>
								<!-- /GETALLHEADERS -->

								<!-- DATA -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-getpost-data">Data</a></li>
								<!-- /DATA -->

								<!-- PS INFOS -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-ps-info">Infos</a></li>
								<!-- /PS INFOS -->
		';
		if(count($GLOBALS['debugtoolbar']))
		{
			$output .= '
								<!-- DEBUG -->
								<li><a class="debugtoolbar-tab" data-debugtoolbar-tab="debugtoolbar-debug">Debug</a></li>
								<!-- /DEBUG -->
			';
		}

		$output .= '			<li class="debugtoolbar-tab-right"><a id="debugtoolbar-hide" href="#">&#8614;</a></li>
								<li class="debugtoolbar-tab-right"><a id="debugtoolbar-close" href="#">&times;</a></li>
								<li class="debugtoolbar-tab-right"><a id="debugtoolbar-zoom" href="#">&#8645;</a></li>
							</ul>

							<ul id="debugtoolbar-closed-tabs" class="debugtoolbar-tabs">
								<li><a id="debugtoolbar-show" href="#">&#8612;</a></li>
							</ul>

						</div>
		';
		$output .= '<script type="text/javascript" src="'.Tools::getShopDomain(true).'/modules/debugtoolbar/views/assets/js/debugtoolbar.js"></script>';

		echo $output;
	}
}

function prestashop_querytime_sort($a, $b)
{
	if ($a['time'] == $b['time'])
		return 0;
	return ($a['time'] > $b['time']) ? -1 : 1;
}
