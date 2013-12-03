{*
 *  _  _  _ _______ ______  _______ _____ _______ _______
 *  |  |  | |______ |_____] |______   |      |    |______
 *  |__|__| |______ |_____] ______| __|__    |    |______
 *  ______  _______ _    _ _______         _____   _____   _____  _______ _______ _______ __   _ _______
 *  |     \ |______  \  /  |______ |      |     | |_____] |_____] |______ |  |  | |______ | \  |    |
 *  |_____/ |______   \/   |______ |_____ |_____| |       |       |______ | www.website-developpement.fr
 * -----------------------------------------------------------------------------------------------------
 *
 * http://www.prestaspirit.fr
 * @author Pichard Franck - PrestaSpirit <contact@prestaspirit.fr>
 * @copyright  20010-2013 PrestaSpirit
 * @version 1.0
 *
 * -----------------------------------------------------------------------------------------------------
*}

{extends file="helpers/options/options.tpl"}
{block name="input"}
	{if $field['type'] == 'debugtoolbar_ip'}
		{$field['script_ip']}
		<div class="col-lg-9 ">
			<div class="row">
				<div class="col-lg-8">
					<input type="text"{if isset($field['id'])} id="{$field['id']}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else} 5{/if}" name="{$key}" value="{$field['value']|escape:'htmlall':'UTF-8'}" />
				</div>
				<div class="col-lg-1">
					{$field['link_remove_ip']}
				</div>
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}