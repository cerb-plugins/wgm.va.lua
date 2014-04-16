<?php
class WgmVaLua_EventCondition extends Extension_DevblocksEventCondition {
	const ID = 'wgm.va.lua.condition';
	
	function render(Extension_DevblocksEvent $event, Model_TriggerEvent $trigger, $params=array(), $seq=null) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('params', $params);
		
		if(!is_null($seq))
			$tpl->assign('namePrefix','condition'.$seq);
			
		$tpl->display('devblocks:wgm.va.lua::config.tpl');
	}
	
	function run($token, Model_TriggerEvent $trigger, $params, DevblocksDictionaryDelegate $dict) {
		//var_dump($params['oper']);
		//var_dump($params['value']);
		
		$tpl_builder = DevblocksPlatform::getTemplateService();
		
		$values = array();
		
		// [TODO] Does this needs _labels and _types?
		foreach($dict->getDictionary() as $k => $v) {
			if(is_float($v)) {
				$values[$k] = floatval($v);
			} elseif(is_numeric($v)) {
				$values[$k] = intval($v);
			} elseif(is_string($v)) {
				$values[$k] = $v;
			}
		}
		
		// [TODO] Run a condition and return a boolean
		// [TODO] Expand
		
		@$lua_script = $params['lua_script'];

		// [TODO] Validate script, print errors
		
		$lua = new Lua();
		
		//$values['_params'] = $params;
		
		$lua->assign('var', $values);
		$lua->registerCallback('get_var', function($key, $values=null) use ($dict) {
			if(!empty($values) && is_array($values))
				$dict = new DevblocksDictionaryDelegate($values);
			
			return $dict->$key;
		});
		
		// Secure it!
		$lua->eval($tpl_builder->fetch('devblocks:wgm.va.lua::lua/wrapper/LuaWrapper.lua'));
		$lua->eval("wrap = make_wrapper(1000, 2000)");
		
		list($out, $err) = $lua->wrap($lua_script);
		
		$out = trim($out);
		//print($out);
		
		$pass = (is_bool($err) && $err) ? true : false;

		//print_r($err);
		
		return $pass;
	}
};