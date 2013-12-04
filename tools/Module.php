<?php
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
			else
			{
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
		}
}