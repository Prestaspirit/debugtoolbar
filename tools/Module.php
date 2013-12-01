<?php

if (!defined('T_ML_COMMENT')) {
	define('T_ML_COMMENT', T_COMMENT);
} else {
	define('T_DOC_COMMENT', T_ML_COMMENT);
}


/**
 * Debugtoolbar  Module For Prestashop
 * Ce module à été développé par Prestaspirit.fr
 * Utilisation sous licence (incluse dans l'archive).
 * Prestaspirit.fr tout droits réservé.
 * http://www.prestaspirit.fr
 * @package Debugtoolbar
 * @author Pichard Franck - PrestaSpirit <contact@prestaspirit.fr>
 * @copyright  20010-2012 PrestaSpirit
 * @version 1.0
 */
abstract class Module extends ModuleCore
{

	/****************************************************************************************/
	/** RemoveOverride Methods **************************************************************/
	/****************************************************************************************/

	/**
	 * Uninstall overrides files for the module
	 *
	 * @return bool
	 */
	public function uninstallOverrides()
	{
		if ($this->name === 'debugtoolbar')
			return true;
		
		if (!is_dir($this->getLocalPath().'override'))
			return true;

		$result = true;
		foreach (Tools::scandir($this->getLocalPath().'override', 'php', '', true) as $file)
		{
			$class = basename($file, '.php');
			if (Autoload::getInstance()->getClassPath($class.'Core'))
				$result &= $this->removeOverride($class);
		}
		return $result;
	}

	/****************************************************************************************/
	/** RemoveOverride Methods **************************************************************/
	/****************************************************************************************/

	/**
	 * removeOverride
	 *
	 * Remove all methods in a module override from the override class
	 *
	 * @access public
	 * @param string $classname
	 * @return bool
	 *
	 */
		public function removeOverride($classname)
		{
			$path = Autoload::getInstance()->getClassPath($classname.'Core');

			if (!Autoload::getInstance()->getClassPath($classname))
				return true;

			// Check if override file is writable
			$override_path = _PS_ROOT_DIR_.'/'.Autoload::getInstance()->getClassPath($classname);
			if (!is_writable($override_path))
				return false;

			// Remove comments from override file
			$code = file_get_contents($override_path);
			$code = $this->stripComments($code);
			file_put_contents($override_path, $code);

			// Make a reflection of the override class and the module override class
			$override_file = file($override_path);
			eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal_remove'), implode('', $override_file)));
			$override_class = new ReflectionClass($classname.'OverrideOriginal_remove');

			$module_file = file($this->getLocalPath().'override/'.$path);
			eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array('', 'class '.$classname.'Override_remove'), implode('', $module_file)));
			$module_class = new ReflectionClass($classname.'Override_remove');

			// Remove methods from override file
			$override_file = file($override_path);
			foreach ($module_class->getMethods() as $method)
			{
				if (!$override_class->hasMethod($method->getName()))
					continue;

				$method = $override_class->getMethod($method->getName());
				$length = $method->getEndLine() - $method->getStartLine() + 1;
				array_splice($override_file, $method->getStartLine() - 1, $length, array_pad(array(), $length, '#--remove--#'));
			}

			// Remove properties from override file
			foreach ($module_class->getProperties() as $property)
			{
				if (!$override_class->hasProperty($property->getName()))
					continue;

				// Remplacer la ligne de dÃ©claration par "remove"
				foreach ($override_file as $line_number => &$line_content)
					if (preg_match('/(public|private|protected)\s+(static\s+)?\$'.$property->getName().'/i', $line_content))
					{
						$line_content = '#--remove--#';
						break;
					}
			}

			// Remove constants from override file
			foreach ($module_class->getConstants() as $constant => $value)
			{
				if (!$override_class->hasConstant($constant))
					continue;

				// Remplacer la ligne de dÃ©claration par "remove"
				foreach ($override_file as $line_number => &$line_content)
					if (preg_match('/const\s+?'.$constant.'/i', $line_content))
					{
						$line_content = '#--remove--#';
						break;
					}
			}

			// Rewrite nice code
			$code = '';
			foreach ($override_file as $line)
			{
				if ($line == '#--remove--#')
					continue;

				$code .= $line;
			}
			file_put_contents($override_path, $code);

			return true;
		}


	/****************************************************************************************/
	/** StripComments Methods ***************************************************************/
	/****************************************************************************************/

	/**
	 * stripComments
	 *
	 * Supprime les commentaires
	 * dans les fichier surchargÃ©
	 *
	 * @access public
	 * @param string source Fichier source
	 * @return string
	 */

		public function stripComments($source)
		{
			$tokens = token_get_all($source);
			$output = "";
			foreach ($tokens as $token) {
				if (is_string($token)) {
					$output .= $token;
				} else {
					list($id, $str) = $token;
					switch ($id) {
						// case T_WHITESPACE:
						case T_COMMENT:
						case T_ML_COMMENT:
						case T_DOC_COMMENT:
							break;

						default:
							$output .= $str;
							break;
					}
				}
			}
			$output = trim($output);
			return $output;
		}



	/****************************************************************************************/
	/** AddOverrideTpl Methods **************************************************************/
	/****************************************************************************************/

	/**
	 * addOverrideTpl
	 *
	 * Ajoute les fichiers template
	 * dans le dossier override
	 *
	 * @access public
	 * @return array
	 */
		public function addOverrideTpl()
		{
			// Stockage des erreurs
			$output = array();

			// Template module directory
			$moduleFiles = self::recursiveGlob(
				_PS_MODULE_DIR_.$this->name.DS."override".DS,
				'*{tpl}',
				GLOB_BRACE, _PS_MODULE_DIR_.$this->name
			);

			// Template override directory
			$overrideFiles = $this->recursiveGlob(
				_PS_OVERRIDE_DIR_."controllers".DS."admin".DS."templates".DS,
				'*{tpl}',
				GLOB_BRACE, _PS_ROOT_DIR_
			);

			foreach ($moduleFiles as $key => $file)
				if(in_array($file, $overrideFiles))
					$output[]["file_exist"] = $file;
				else
					if(self::makePath(_PS_ROOT_DIR_.$file)){
						if(!copy(_PS_MODULE_DIR_.$this->name.$file, _PS_ROOT_DIR_.$file))
							$output[]["copy_fail"] = $file;
					}

			if(sizeof($output)) :
				foreach ($output as $k => $v)
					if(isset($output[$k]['copy_fail']))
						$this->_errors[] = sprintf($this->l('An error occurred while copying the file : "%s".<br><br>'), $output[$k]['copy_fail']);
					elseif(isset($output[$k]['file_exist']))
						$this->_errors[] = sprintf($this->l('This file already exists in the "override" folder, please contact your webmaster to install the module. <br>"%s"<br><br>'), $output[$k]['file_exist']);

				return false;
			endif;

			return true;
		}



	/****************************************************************************************/
	/** DelOverrideTpl Methods **************************************************************/
	/****************************************************************************************/

	/**
	 * delOverrideTpl
	 *
	 * Supprime les fichiers template
	 * dans le dossier override
	 *
	 * @access public
	 * @return array
	 */
		public function delOverrideTpl()
		{
			// Stockage des erreurs
			$output = array();

			// Template module directory
			$moduleFiles = self::recursiveGlob(
				_PS_MODULE_DIR_.$this->name.DS."override".DS,
				'*{tpl}',
				GLOB_BRACE, _PS_MODULE_DIR_.$this->name
			);

			// Template override directory
			$overrideFiles = $this->recursiveGlob(
				_PS_OVERRIDE_DIR_."controllers".DS."admin".DS."templates".DS,
				'*{tpl}',
				GLOB_BRACE, _PS_ROOT_DIR_
			);

			foreach ($moduleFiles as $key => $file)
				if(in_array($file, $overrideFiles))
					if(!unlink(_PS_ROOT_DIR_.$file))
						$output[]["unlink_fail"] = $file;

			if(sizeof($output)) :
				foreach ($output as $k => $v)
					if(isset($output[$k]['unlink_fail']))
						$this->_errors[] = sprintf($this->l('An error occurred while remove the file : "%s".<br><br>'), $output[$k]['unlink_fail']);
				return false;
			endif;

			return true;
		}



	/****************************************************************************************/
	/** RecursiveGlob Methods ***************************************************************/
	/****************************************************************************************/

	/**
	 * recursiveGlob
	 *
	 * Parse l'ensemble du dossier en paramÃ¨tre
	 * et retourne les fichiers correspondant au pattern
	 *
	 * @access public
	 * @param string path Chemin du dossier source
	 * @param string pattern Masque Ã  rechercher
	 * @param string flags Drapeau
	 * @param string subpath Chaine Ã  supprimer
	 * @return array
	 */
		public function recursiveGlob($path = '', $pattern = '*', $flags = 0, $subpath = '')
		{
			if(empty($path))
				return false;

			$paths = glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
			$files = ((glob($path.$pattern, $flags) === false) ? array() : glob($path.$pattern, $flags));
			foreach ($paths as $p)
				if(is_array($files))
					$files = array_merge($files, str_replace($subpath, '', self::recursiveGlob($p, $pattern, $flags)));

			return $files;
		}


	/****************************************************************************************/
	/** MakePath Methods ********************************************************************/
	/****************************************************************************************/

	/**
	 * makePath
	 *
	 * CrÃ©er l'arborescence de dossier
	 *
	 * @access public
	 * @param string path Chemin du dossier cible
	 * @param string is_filename La cible est un fichier
	 * @return bool
	 */
		public function makePath($path, $is_filename = true) {
			if($is_filename)
				$path = substr($path, 0, strrpos($path, '/'));

			if (is_dir($path) || file_exists($path))
				return true;
			return mkdir($path, 0777, true);
		}
}