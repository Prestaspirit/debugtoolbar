<?php

/**
 * Debugtoolbar  Module For Prestashop
 * Ce module à été développé par Prestaspirit.fr
 * Utilisation sous licence (incluse dans l'archive).
 * Prestaspirit.fr tout droits réservé.
 * http://www.prestaspirit.fr
 * @package Debugtoolbar
 * @author Pichard Franck - PrestaSpirit <contact@prestaspirit.fr>
 * @copyright  20010-2012 PrestaSpirit
 * @version 1.0
 */


if (!defined('_PS_VERSION_'))
	exit;

if (!defined('_MYSQL_ENGINE_'))
	define('_MYSQL_ENGINE_', 'MyISAM');


/**
 * DebugToolBar Class
 */
class DebugToolbar extends Module
{

	/* var bool debugtoolbar */
	public $debugtoolbar = false;

	/****************************************************************************************/
	/** Construct Method ********************************************************************/
	/****************************************************************************************/

	/**
	 * __construct
	 *
	 * @access public
	 */
		public function __construct()
		{
			$this->name                   = 'debugtoolbar';
			$this->tab                    = 'administration';
			$this->version                = '1.0';
			$this->author                 = 'Prestaspirit';
			$this->need_instance          = 1;
			$this->ps_versions_compliancy = array('min' => '1.5','max' => '1.6');
			$this->module_key             = '';

			parent::__construct();

			$this->displayName = $this->l('DebugToolbar Module');
			$this->description = $this->l('DebugToolbar Module By Prestaspirit.');

			if ((self::isInstalled($this->name) && $this->_isConfigurable()) || ($this->need_instance))
			{
				if (!is_bool($msg = $this->_isConfigured()))
					$this->context->controller->warning[] = $msg;
			}

			if ($this->isEnable())
				$this->debugtoolbar = true;
		}


	/****************************************************************************************/
	/** Install Methods *********************************************************************/
	/****************************************************************************************/

	/**
	 * install
	 *
	 * @access public
	 * @return bool
	 */
		public function install()
		{
			if (!parent::install())
				return false;
			return true;
		}


	/****************************************************************************************/
	/** Uninstall Methods *******************************************************************/
	/****************************************************************************************/

	/**
	 * uninstall
	 *
	 * @access public
	 * @return bool
	 */
		public function uninstall()
		{
			$filesOverrideRm = array(
				_PS_OVERRIDE_DIR_.'classes'.DS.'controller'.DS.'Controller.php',
				_PS_OVERRIDE_DIR_.'classes'.DS.'module'.DS.'Module.php',
				_PS_OVERRIDE_DIR_.'classes'.DS.'ObjectModel.php',
				_PS_OVERRIDE_DIR_.'classes'.DS.'db'.DS.'Db.php',
				_PS_OVERRIDE_DIR_.'classes'.DS.'Hook.php'
			);

			foreach ($filesOverrideRm as $file)
				if (isset($file))
					@unlink($file);

			if (!parent::uninstall() ||
				!$this->_deleteContent())
				return false;
			return true;
		}


	/****************************************************************************************/
	/** getContent Method *******************************************************************/
	/****************************************************************************************/

	/**
	 * getContent
	 *
	 * @access public
	 */
		public function getContent()
		{
			$message = '';

			if (Tools::isSubmit('saveOptions'.ucfirst($this->name)))
				$this->_saveContent();

			$helperOptions = $this->_initForm();
			return $helperOptions->generateOptions($this->fields_options);
		}


	/****************************************************************************************/
	/** _saveContent Method *****************************************************************/
	/****************************************************************************************/

	/**
	 * _saveContent
	 *
	 * @access private
	 */
		private function _saveContent()
		{
			if (!Configuration::updateValue('MOD_DTB_ENABLE', Tools::getValue('MOD_DTB_ENABLE')))
				$this->context->controller->errors[] = $this->l('There was an error while saving your settings');
			elseif (!Configuration::updateValue('MOD_DTB_IP', Tools::getValue('MOD_DTB_IP')))
				$this->context->controller->errors[] = $this->l('There was an error while saving your settings');
			else
				$this->context->controller->confirmations[] = $this->l('Your settings have been saved');
		}


	/****************************************************************************************/
	/** _deleteContent Method ***************************************************************/
	/****************************************************************************************/

	/**
	 * _deleteContent
	 *
	 * @access private
	 * @return bool
	 */
		private function _deleteContent()
		{
			if (!Configuration::deleteByName('MOD_DTB_ENABLE') ||
				!Configuration::deleteByName('MOD_DTB_IP'))
				return false;
			return true;
		}


	/****************************************************************************************/
	/** _initForm Method ***************************************************************/
	/****************************************************************************************/

	/**
	 * initForm
	 *
	 * @access protected
	 * @return array
	 */
		protected function _initForm()
		{
			if (_PS_VERSION_ < '1.6')
			{
				$this->fields_options = array(
					'general' => array(
						'title' =>	$this->l('General'),
						'image' =>	$this->getPathUri().'views/assets/img/AdminMaintenance.gif',
						'fields' =>	array(
							'MOD_DTB_ENABLE' => array(
								'title' => $this->l('Enable Debug Toolbar'),
								'desc' => $this->l('Enable or disable the debug toolbar.'),
								'validation' => 'isBool',
								'cast' => 'intval',
								'type' => 'bool'
							),
							'MOD_DTB_IP' => array(
								'title' => $this->l('Authorized IP'),
								'desc' => $this->l('IP addresses authorized to view the debug toolbar. Please use a comma to separate them (e.g. 42.24.4.2,127.0.0.1,99.98.97.96)'),
								'validation' => 'isGenericName',
								'type' => 'debugtoolbar_ip',
								'script_ip' => '
								<script type="text/javascript">
									function addRemoteAddr()
									{
										var length = $(\'input[name=MOD_DTB_IP]\').attr(\'value\').length;
										if (length > 0)
											$(\'input[name=MOD_DTB_IP]\').attr(\'value\',$(\'input[name=MOD_DTB_IP]\').attr(\'value\') +\','.Tools::getRemoteAddr().'\');
										else
											$(\'input[name=MOD_DTB_IP]\').attr(\'value\',\''.Tools::getRemoteAddr().'\');
									}
								</script>',
								'link_remove_ip' => ' &nbsp<a href="#" class="button" onclick="addRemoteAddr(); return false;">'.$this->l('Add my IP', 'Helper').'</a>',
								'size' => 30,
								'default' => ''
							),
						),
						'submit' => array('title' => $this->l('Save'), 'class' => 'button'),
					)
				);
			}
			else
			{
				$this->bootstrap = true;
				$this->fields_options = array(
					'general' => array(
						'title' =>	$this->l('General'),
						'fields' =>	array(
							'MOD_DTB_ENABLE' => array(
								'title' => $this->l('Enable Debug Toolbar'),
								'hint' => $this->l('Enable or disable the debug toolbar.'),
								'validation' => 'isBool',
								'cast' => 'intval',
								'type' => 'bool'
							),
							'MOD_DTB_IP' => array(
								'title' => $this->l('Authorized IP'),
								'hint' => $this->l('IP addresses authorized to view the debug toolbar. Please use a comma to separate them (e.g. 42.24.4.2,127.0.0.1,99.98.97.96)'),
								'validation' => 'isGenericName',
								'type' => 'debugtoolbar_ip',
								'script_ip' => '
								<script type="text/javascript">
									function addRemoteAddr()
									{
										var length = $(\'input[name=MOD_DTB_IP]\').attr(\'value\').length;
										if (length > 0)
											$(\'input[name=MOD_DTB_IP]\').attr(\'value\',$(\'input[name=MOD_DTB_IP]\').attr(\'value\') +\','.Tools::getRemoteAddr().'\');
										else
											$(\'input[name=MOD_DTB_IP]\').attr(\'value\',\''.Tools::getRemoteAddr().'\');
									}
								</script>',
								'link_remove_ip' => '<button type="button" class="btn btn-default" onclick="addRemoteAddr();"><i class="icon-plus"></i> '.$this->l('Add my IP', 'Helper').'</button>',
								'size' => 30,
								'default' => ''
							),
						),
						'submit' => array('title' => $this->l('Save'), 'class' => 'button'),
					)
				);
			}

			$helper = new HelperOptions($this);
			$helper->id = $this->id;
			$helper->module = $this;
			$helper->title = array($this->displayName, $this->l('Configuration'));
			$helper->show_toolbar = true;
			$helper->actions = array('add', 'edit');
			$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&saveOptions'.ucfirst($this->name);
			$helper->table = $this->name;
			$helper->name_controller = Tools::getValue('controller');
			$helper->shopLink = '';
			$helper->simple_header = true;
			$helper->token = Tools::getAdminTokenLite('AdminModules');
			$helper->toolbar_scroll = true;
			$helper->base_tpl = ((_PS_VERSION_ < '1.6') ? $helper->base_tpl : 'options_1.6.tpl');
			$helper->toolbar_btn = array(
				'save' => array(
					'href' => '#',
					'desc' => $this->l('Save')
				)
			);

			return $helper;
		}


	/****************************************************************************************/
	/** _isConfigurable Method ****************************************************************/
	/****************************************************************************************/

	/**
	 * _isConfigurable
	 *
	 * @access private
	 * @return bool
	 */
		private function _isConfigurable()
		{
			return (int)method_exists($this, 'getContent') ? true : false;
		}


	/****************************************************************************************/
	/** _isConfigured Method ****************************************************************/
	/****************************************************************************************/

	/**
	 * _isConfigured
	 *
	 * @access private
	 */
		private function _isConfigured()
		{
			if (!$this->active)
				if (!Configuration::updateValue('MOD_DTB_ENABLE', 0))
					return $this->l('Error configuration.');

			// Ajoute la surcharge de la classe Module
			if ($this->checkOverrideFolderReadableWritable())
			{
				if (!$this->addOverrideModuleCore($this->name))
					return $this->l('Error copy file.');
				return true;
			}
			else
				return $this->l('The override file is not writable, please change the permissions.');
				
		}


	/****************************************************************************************/
	/** isActiveDtb Method ******************************************************************/
	/****************************************************************************************/

	/**
	 * isActiveDtb
	 *
	 * @access private
	 * @return bool
	 */
		protected static function _isActiveDtb()
		{
			if (Configuration::get('MOD_DTB_ENABLE'))
				if (in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('MOD_DTB_IP'))))
					return true;
			return false;
		}


	/****************************************************************************************/
	/** isActive Method *********************************************************************/
	/****************************************************************************************/

	/**
	 * isActive
	 *
	 * @access private
	 * @return bool
	 */
		public static function isEnable()
		{
			return self::_isActiveDtb() ? true : false;
		}


	/****************************************************************************************/
	/** Check Override folder readable/writable Methods *************************************/
	/****************************************************************************************/

	/**
	 * checkOverrideFolderReadableWritable
	 *
	 * Check PHP Version
	 *
	 * @access public
	 * @return bool
	 */
		public function checkOverrideFolderReadableWritable()
		{
			// Check php version
			if (!$this->chmodTest(_PS_OVERRIDE_DIR_))
				return false;
			return true;
		}


	/****************************************************************************************/
	/** chmodTest Methods *******************************************************************/
	/****************************************************************************************/

	/**
	 * Check the permissions of the file passed as a parameter
	 *
	 * @param mixed $f file
	 */
		public static function chmodTest($f)
		{
			if (@is_writable($f) && @is_readable($f))
				return true;
			return false;
		}


	/****************************************************************************************/
	/** AddOverrideModuleCore Methods *******************************************************/
	/****************************************************************************************/

	/**
	 * AddOverrideModuleCore
	 *
	 * Ajoute le fichier de surcharge
	 * de la classe module
	 *
	 * @access public
	 * @param string moduleName Nom du module
	 * @return array
	 */
		public static function addOverrideModuleCore($moduleName)
		{
			if (file_exists(_PS_OVERRIDE_DIR_.'classes'.DS.'module'.DS.'Module.php'))
				return true;

			if (!copy(_PS_MODULE_DIR_.$moduleName.DS.'tools'.DS.'Module.php', _PS_OVERRIDE_DIR_.'classes'.DS.'module'.DS.'Module.php'))
				return false;
			return true;
		}
}