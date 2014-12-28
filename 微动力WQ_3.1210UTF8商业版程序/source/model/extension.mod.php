<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: origins/source/model/extension.mod.php : v 866195d935cc : 2014/05/16 09:42:08 : veryinf $
 */
defined('IN_IA') or exit('Access Denied');

function ext_module_convert($manifest) {
	$module = array(
		'name' => $manifest['application']['identifie'],
		'title' => $manifest['application']['name'],
		'version' => $manifest['application']['version'],
		'type' => $manifest['application']['type'],
		'ability' => $manifest['application']['ability'],
		'description' => $manifest['application']['description'],
		'author' => $manifest['application']['author'],
		'url' => $manifest['application']['url'],
		'settings'  => intval($manifest['application']['setting']),
		'subscribes' => iserializer(is_array($manifest['platform']['subscribes']) ? $manifest['platform']['subscribes'] : array()),
		'handles' => iserializer(is_array($manifest['platform']['handles']) ? $manifest['platform']['handles'] : array()),
		'isrulefields' => intval($manifest['platform']['isrulefields']),
		'cover' => $manifest['bindings']['cover'],
		'rule' => $manifest['bindings']['rule'],
		'menu' => $manifest['bindings']['menu'],
		'home' => $manifest['bindings']['home'],
		'profile' => $manifest['bindings']['profile'],
		'shortcut' => $manifest['bindings']['shortcut'],
		'issystem' => 0
	);
	return $module;
}

function ext_module_manifest_parse($xml) {
	$dom = new DOMDocument();
	$dom->loadXML($xml);
	if($dom->schemaValidateSource(ext_module_manifest_validate())) {
		// 0.51xml
		$root = $dom->getElementsByTagName('manifest')->item(0);
		$vcode = explode(',', $root->getAttribute('versionCode'));
		$manifest['versions'] = array();
		if(is_array($vcode)) {
			foreach($vcode as $v) {
				$v = trim($v);
				if(!empty($v)) {
					$manifest['versions'][] = $v;
				}
			}
			$manifest['versions'][] = '0.52';
		}
		$manifest['install'] = $root->getElementsByTagName('install')->item(0)->textContent;
		$manifest['uninstall'] = $root->getElementsByTagName('uninstall')->item(0)->textContent;
		$manifest['upgrade'] = $root->getElementsByTagName('upgrade')->item(0)->textContent;
		$application = $root->getElementsByTagName('application')->item(0);
		$manifest['application'] = array(
			'name' => trim($application->getElementsByTagName('name')->item(0)->textContent),
			'identifie' => trim($application->getElementsByTagName('identifie')->item(0)->textContent),
			'version' => trim($application->getElementsByTagName('version')->item(0)->textContent),
			'type' => trim($application->getElementsByTagName('type')->item(0)->textContent),
			'ability' => trim($application->getElementsByTagName('ability')->item(0)->textContent),
			'description' => trim($application->getElementsByTagName('description')->item(0)->textContent),
			'author' => trim($application->getElementsByTagName('author')->item(0)->textContent),
			'url' => trim($application->getElementsByTagName('url')->item(0)->textContent),
			'setting' => trim($application->getAttribute('setting')) == 'true',
		);
		$platform = $root->getElementsByTagName('platform')->item(0);
		if(!empty($platform)) {
			$manifest['platform'] = array(
				'subscribes' => array(),
				'handles' => array(),
				'isrulefields' => false,
			);
			$subscribes = $platform->getElementsByTagName('subscribes')->item(0);
			if(!empty($subscribes)) {
				$messages = $subscribes->getElementsByTagName('message');
				for($i = 0; $i < $messages->length; $i++) {
					$t = $messages->item($i)->getAttribute('type');
					if(!empty($t)) {
						$manifest['platform']['subscribes'][] = $t;
					}
				}
			}
			$handles = $platform->getElementsByTagName('handles')->item(0);
			if(!empty($handles)) {
				$messages = $handles->getElementsByTagName('message');
				for($i = 0; $i < $messages->length; $i++) {
					$t = $messages->item($i)->getAttribute('type');
					if(!empty($t)) {
						$manifest['platform']['handles'][] = $t;
					}
				}
			}
			$rule = $platform->getElementsByTagName('rule')->item(0);
			if(!empty($rule) && $rule->getAttribute('embed') == 'true') {
				$manifest['platform']['isrulefields'] = true;
			}
		}
		$bindings = $root->getElementsByTagName('bindings')->item(0);
		if(!empty($bindings)) {
			global $points;
			if (!empty($points)) {
				$ps = array_keys($points);
				$manifest['bindings'] = array();
				foreach($ps as $p) {
					$define = $bindings->getElementsByTagName($p)->item(0);
					$manifest['bindings'][$p] = _ext_module_manifest_entries($define);
				}
			}
		}
	} else {
		$err = error_get_last();
		if($err['type'] == 2) {
			return $err['message'];
		}
	}
	return $manifest;
}

function ext_module_manifest($modulename, $withRemote = false) {
	if(!preg_match('/^[a-z\d_]+$/', $modulename)) {
		return array();
	}
	if($withRemote) {
		$manifest = cloud_m_manifest($modulename);
		if(!empty($manifest)) {
			return ext_module_manifest_parse($manifest);
		}
	}
	$filename = IA_ROOT . '/source/modules/' . $modulename . '/manifest.xml';
	if (!file_exists($filename)) {
		return array();
	}
	$xml = file_get_contents($filename);
	return ext_module_manifest_parse($xml);
}

function _ext_module_manifest_entries($elm) {
	$ret = array();
	if(!empty($elm)) {
		$call = $elm->getAttribute('call');
		if(!empty($call)) {
			$ret[] = array('call' => $call);
		}
		$entries = $elm->getElementsByTagName('entry');
		for($i = 0; $i < $entries->length; $i++) {
			$entry = $entries->item($i);
			$row = array(
				'title' => $entry->getAttribute('title'),
				'do' => $entry->getAttribute('do'),
				'direct' => $entry->getAttribute('direct') == 'true',
				'state' => $entry->getAttribute('state')
			);
			if(!empty($row['title']) && !empty($row['do'])) {
				$ret[] = $row;
			}
		}
	}
	return $ret;
}

function ext_module_checkupdate($modulename) {
	global $_W;
	$manifest = ext_module_manifest($modulename);
	if (!empty($manifest) && is_array($manifest)) {
		$version = $manifest['application']['version'];
		if ($version > $_W['modules'][$modulename]['version']) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function ext_module_manifest_compat($modulename) {
	$manifest = array();
	$filename = IA_ROOT . '/source/modules/' . $modulename . '/manifest.xml';
	if (!file_exists($filename)) {
		return array();
	}
	$xml = str_replace(array('&'), array('&amp;'), file_get_contents($filename));
	$xml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if (empty($xml)) {
		return array();
	}
	$dom = new DOMDocument();
	@$dom->load($filename);
	if(@$dom->schemaValidateSource(ext_module_manifest_validate_050())) {
		$attributes = $xml->attributes();
		$manifest['versions'] = explode(',', strval($attributes['versionCode']));
		if(is_array($manifest['versions'])) {
			foreach($manifest['versions'] as &$v) {
				$v = trim($v);
				if(empty($v)) {
					unset($v);
				}
			}
		}
		$manifest['version'] = '0.5';
		$manifest['install'] = strval($xml->install);
		$manifest['uninstall'] = strval($xml->uninstall);
		$manifest['upgrade'] = strval($xml->upgrade);
		$attributes = $xml->application->attributes();
		$manifest['application'] = array(
			'name' => trim(strval($xml->application->name)),
			'identifie' => trim(strval($xml->application->identifie)),
			'version' => trim(strval($xml->application->version)),
			'ability' => trim(strval($xml->application->ability)),
			'description' => trim(strval($xml->application->description)),
			'author' => trim(strval($xml->application->author)),
			'url' => trim(strval($xml->application->url)),
			'setting' => trim(strval($attributes['setting'])) == 'true',
		);
		$rAttrs = array();
		if($xml->platform && $xml->platform->rule) {
			$rAttrs = $xml->platform->rule->attributes();
		}
		$mAttrs = array();
		if($xml->platform && $xml->platform->menus) {
			$mAttrs = $xml->platform->menus->attributes();
		}
		$manifest['platform'] = array(
			'subscribes' => array(),
			'handles' => array(),
			'isrulefields' => trim(strval($rAttrs['embed'])) == 'true',
			'options' => array(),
			'ismenus' => trim(strval($mAttrs['embed'])) == 'true',
			'menus' => array()
		);
		if($xml->platform->subscribes->message) {
			foreach($xml->platform->subscribes->message as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['subscribes'][] = trim(strval($attrs['type']));
			}
		}
		if($xml->platform->handles->message) {
			foreach($xml->platform->handles->message as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['handles'][] = trim(strval($attrs['type']));
			}
		}
		if($manifest['platform']['isrulefields'] && $xml->platform->rule->option) {
			foreach($xml->platform->rule->option as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['options'][] = array('title' => trim(strval($attrs['title'])), 'do' => trim(strval($attrs['do'])), 'state' => trim(strval($attrs['state'])));
			}
		}
		if($manifest['platform']['ismenus'] && $xml->platform->menus->menu) {
			foreach($xml->platform->menus->menu as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['menus'][] = array('title' => trim(strval($attrs['title'])), 'do' => trim(strval($attrs['do'])));
			}
		}
		$hAttrs = array();
		if($xml->site && $xml->site->home) {
			$hAttrs = $xml->site->home->attributes();
		}
		$pAttrs = array();
		if($xml->site && $xml->site->profile) {
			$pAttrs = $xml->site->profile->attributes();
		}

		$mAttrs = array();
		if($xml->site && $xml->site->menus) {
			$mAttrs = $xml->site->menus->attributes();
		}
		$manifest['site'] = array(
			'home' => trim(strval($hAttrs['embed'])) == 'true',
			'profile' => trim(strval($pAttrs['embed'])) == 'true',
			'ismenus' => trim(strval($mAttrs['embed'])) == 'true',
			'menus' => array()
		);
		if($manifest['site']['ismenus'] && $xml->site->menus->menu) {
			foreach($xml->site->menus->menu as $msg) {
				$attrs = $msg->attributes();
				$manifest['site']['menus'][] = array('title' => trim(strval($attrs['title'])), 'do' => trim(strval($attrs['do'])));
			}
		}
	} else {
		$attributes = $xml->attributes();
		$manifest['version'] = strval($attributes['versionCode']);
		$manifest['install'] = strval($xml->install);
		$manifest['uninstall'] = strval($xml->uninstall);
		$manifest['upgrade'] = strval($xml->upgrade);
		$attributes = $xml->application->attributes();
		$manifest['application'] = array(
			'name' => strval($xml->application->name),
			'identifie' => strval($xml->application->identifie),
			'version' => strval($xml->application->version),
			'ability' => strval($xml->application->ability),
			'description' => strval($xml->application->description),
			'author' => strval($xml->application->author),
			'setting' => strval($attributes['setting']) == 'true',
		);
		$hooks = @(array)$xml->hooks->children();
		if (!empty($hooks['hook'])) {
			foreach ((array)$hooks['hook'] as $hook) {
				$manifest['hooks'][strval($hook['name'])] = strval($hook['name']);
			}
		}
		$menus = @(array)$xml->menus->children();
		if (!empty($menus['menu'])) {
			foreach ((array)$menus['menu'] as $menu) {
				$manifest['menus'][] = array(strval($menu['name']), strval($menu['value']));
			}
		}
	}

	$ret = array();
	$ret['meta'] = $manifest;
	$ret['meta']['compact'] = $manifest['version'];
	global $points;
	if($ret['meta']['compact'] == '0.41' || $ret['meta']['compact'] == '0.4') {
		//Compact 0.41
		$ret['convert'] = ext_module_convert($manifest);
		$ret['convert']['compact'] = $manifest['version'];
		$ret['convert']['type'] = 'other';
		foreach($points as $p => $row) {
			$ret['convert'][$p] = array();
		}

		$handles = iunserializer($ret['convert']['handles']);
		if($ret['meta']['hooks'] && $ret['meta']['hooks']['rule']) {
			$handles[] = 'text';
			$ret['convert']['isrulefields'] = true;
		}
		$ret['convert']['handles'] = iserializer($handles);
		if(is_array($ret['meta']['menus'])) {
			foreach($ret['meta']['menus'] as $row) {
				$opt = array();
				$opt['title'] = $row[0];
				$urls = parse_url($row[1]);
				parse_str($urls['query'], $vars);
				$opt['do'] = $vars['do'];
				$opt['state'] = $vars['state'];
				if(!empty($opt['title']) && !empty($opt['do'])) {
					$ret['convert']['rule'][] = $opt;
				}
			}
		}

		$m = $ret['convert'];
		$m['install'] = $manifest['install'];
		$m['uninstall'] = $manifest['uninstall'];
		$m['upgrade'] = $manifest['upgrade'];
		$m['handles'] = iunserializer($m['handles']);
		$versions = IMS_VERSION;
		$setting = $m['settings'] ? 'true' : 'false';
		$handles = '';
		foreach($m['handles'] as $h) {
			$handles .= "\r\n\t\t\t<message type=\"{$h}\" />";
		}
		$rule = $m['isrulefields'] ? 'true' : 'false';
		$bindings = '';
		foreach($points as $p => $row) {
			if(is_array($m[$p]) && !empty($m[$p])) {
				$piece = "\r\n\t\t<{$p}{$calls[$p]}>";
				foreach($m[$p] as $entry) {
					if(!empty($entry['title']) && !empty($entry['do'])) {
						$direct = $entry['direct'] ? 'true' : 'false';
						$piece .= "\r\n\t\t\t<entry title=\"{$entry['title']}\" do=\"{$entry['do']}\" state=\"{$entry['state']}\" direct=\"{$direct}\" />";
					}
				}
				$piece .= "\r\n\t\t</{$p}>";
				$bindings .= $piece;
			}
		}
		$tpl = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="http://www.we7.cc" versionCode="{$versions}">
	<application setting="{$setting}">
		<name><![CDATA[{$m['title']}]]></name>
		<identifie><![CDATA[{$m['name']}]]></identifie>
		<version><![CDATA[{$m['version']}]]></version>
		<type><![CDATA[{$manifest['application']['type']}]]></type>
		<ability><![CDATA[{$m['ability']}]]></ability>
		<description><![CDATA[{$m['description']}]]></description>
		<author><![CDATA[{$m['author']}]]></author>
		<url><![CDATA[{$m['url']}]]></url>
	</application>
	<platform>
		<handles>{$handles}
		</handles>
		<rule embed="{$rule}" />
	</platform>
	<bindings>{$bindings}
	</bindings>
	<install><![CDATA[{$m['install']}]]></install>
	<uninstall><![CDATA[{$m['uninstall']}]]></uninstall>
	<upgrade><![CDATA[{$m['upgrade']}]]></upgrade>
</manifest>
TPL;
		$ret['manifest'] = ltrim($tpl);
		return $ret;
	}
	if($ret['meta']['compact'] == '0.5') {
		// Compact 0.5
		$ret['convert'] = ext_module_convert($manifest);
		$ret['convert']['compact'] = $manifest['version'];
		$ret['convert']['type'] = 'other';
		foreach($points as $p => $row) {
			$ret['convert'][$p] = array();
		}
		if(is_array($manifest['platform']['options'])) {
			foreach($manifest['platform']['options'] as $opt) {
				$entry = array();
				$entry['title'] = $opt['title'];
				$entry['do'] = $opt['do'];
				$entry['state'] = $opt['state'];
				if(!empty($entry['title']) && !empty($entry['do'])) {
					$ret['convert']['rule'][] = $entry;
				}
			}
		}
		if(is_array($manifest['platform']['menus'])) {
			foreach($manifest['platform']['menus'] as $opt) {
				$entry = array();
				$entry['title'] = $opt['title'];
				$entry['do'] = $opt['do'];
				$entry['state'] = $opt['state'];
				if(!empty($entry['title']) && !empty($entry['do'])) {
					$ret['convert']['menu'][] = $entry;
				}
			}
		}
		if(is_array($manifest['site']['menus'])) {
			foreach($manifest['site']['menus'] as $opt) {
				$entry = array();
				$entry['title'] = $opt['title'];
				$entry['do'] = $opt['do'];
				$entry['state'] = $opt['state'];
				if(!empty($entry['title']) && !empty($entry['do'])) {
					$ret['convert']['menu'][] = $entry;
				}
			}
		}
		$calls = array();
		if(!empty($manifest['site']['home'])) {
			$calls['home'] = ' call="getHomeTiles"';
			$ret['convert']['home'][] = array('call' => 'getHomeTiles');
		}
		if(!empty($manifest['site']['profile'])) {
			$calls['profile'] = ' call="getProfileTiles"';
			$ret['convert']['profile'][] = array('call' => 'getProfileTiles');
		}

		$m = $ret['convert'];
		$versions = IMS_VERSION;
		$setting = $m['settings'] ? 'true' : 'false';
		$subscribes = '';
		foreach($manifest['platform']['subscribes'] as $s) {
			$subscribes .= "\r\n\t\t\t<message type=\"{$s}\" />";
		}
		$handles = '';
		foreach($manifest['platform']['handles'] as $h) {
			$handles .= "\r\n\t\t\t<message type=\"{$h}\" />";
		}
		$rule = $m['isrulefields'] ? 'true' : 'false';
		$bindings = '';
		foreach($points as $p => $row) {
			if(is_array($m[$p]) && !empty($m[$p])) {
				$piece = "\r\n\t\t<{$p}{$calls[$p]}>";
				foreach($m[$p] as $entry) {
					if(!empty($entry['title']) && !empty($entry['do'])) {
						$direct = $entry['direct'] ? 'true' : 'false';
						$piece .= "\r\n\t\t\t<entry title=\"{$entry['title']}\" do=\"{$entry['do']}\" state=\"{$entry['state']}\" direct=\"{$direct}\" />";
					}
				}
				$piece .= "\r\n\t\t</{$p}>";
				$bindings .= $piece;
			}
		}
		$tpl = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="http://www.we7.cc" versionCode="{$versions}">
	<application setting="{$setting}">
		<name><![CDATA[{$m['title']}]]></name>
		<identifie><![CDATA[{$m['name']}]]></identifie>
		<version><![CDATA[{$m['version']}]]></version>
		<type><![CDATA[{$manifest['application']['type']}]]></type>
		<ability><![CDATA[{$m['ability']}]]></ability>
		<description><![CDATA[{$m['description']}]]></description>
		<author><![CDATA[{$m['author']}]]></author>
		<url><![CDATA[{$m['url']}]]></url>
	</application>
	<platform>
		<subscribes>{$subscribes}
		</subscribes>
		<handles>{$handles}
		</handles>
		<rule embed="{$rule}" />
	</platform>
	<bindings>{$bindings}
	</bindings>
	<install><![CDATA[{$m['install']}]]></install>
	<uninstall><![CDATA[{$m['uninstall']}]]></uninstall>
	<upgrade><![CDATA[{$m['upgrade']}]]></upgrade>
</manifest>
TPL;
		$ret['manifest'] = ltrim($tpl);
		return $ret;
	}
	return array();
}

function ext_module_manifest_validate_050() {
	$xsd = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<xs:schema xmlns="http://www.we7.cc" xmlns:xs="http://www.w3.org/2001/XMLSchema">
	<xs:element name="manifest">
		<xs:complexType>
			<xs:sequence>
				<xs:element name="application" minOccurs="1" maxOccurs="1">
					<xs:complexType>
						<xs:sequence>
							<xs:element name="name" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="identifie" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="version" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="type" type="xs:string"  minOccurs="0" maxOccurs="1" />
							<xs:element name="ability" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="description" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="author" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="url" type="xs:string"  minOccurs="1" maxOccurs="1" />
						</xs:sequence>
						<xs:attribute name="setting" type="xs:boolean" />
					</xs:complexType>
				</xs:element>
				<xs:element name="platform" minOccurs="0" maxOccurs="1">
					<xs:complexType>
						<xs:sequence>
							<xs:element name="subscribes" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="message" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="type" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
								</xs:complexType>
							</xs:element>
							<xs:element name="handles" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="message" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="type" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
								</xs:complexType>
							</xs:element>
							<xs:element name="rule" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="option" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="title" type="xs:string" />
												<xs:attribute name="do" type="xs:string" />
												<xs:attribute name="state" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
									<xs:attribute name="embed" type="xs:boolean" />
									<xs:attribute name="single" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
							<xs:element name="menus" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="menu" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="title" type="xs:string" />
												<xs:attribute name="do" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
						</xs:sequence>
					</xs:complexType>
				</xs:element>
				<xs:element name="site" minOccurs="0" maxOccurs="1">
					<xs:complexType>
						<xs:sequence>
							<xs:element name="home" minOccurs="1" maxOccurs="1">
								<xs:complexType>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
							<xs:element name="profile" minOccurs="1" maxOccurs="1">
								<xs:complexType>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
							<xs:element name="menus" minOccurs="1" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="menu" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="title" type="xs:string" />
												<xs:attribute name="do" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
						</xs:sequence>
					</xs:complexType>
				</xs:element>
				<xs:element name="install" type="xs:string" minOccurs="1" maxOccurs="1"/>
				<xs:element name="uninstall" type="xs:string" minOccurs="1" maxOccurs="1" />
				<xs:element name="upgrade" type="xs:string" minOccurs="1" maxOccurs="1" />
			</xs:sequence>
			<xs:attribute name="versionCode" type="xs:string" />
		</xs:complexType>
	</xs:element>
</xs:schema>
TPL;
	return trim($xsd);
}

function ext_module_manifest_validate() {
	$xsd = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<xs:schema xmlns="http://www.we7.cc" targetNamespace="http://www.we7.cc" xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
	<xs:element name="entry">
		<xs:complexType>
			<xs:attribute name="title" type="xs:string" />
			<xs:attribute name="do" type="xs:string" />
			<xs:attribute name="direct" type="xs:boolean" />
			<xs:attribute name="state" type="xs:string" />
		</xs:complexType>
	</xs:element>
	<xs:element name="message">
		<xs:complexType>
			<xs:attribute name="type" type="xs:string" />
		</xs:complexType>
	</xs:element>
	<xs:element name="manifest">
		<xs:complexType>
			<xs:all>
				<xs:element name="application" minOccurs="1" maxOccurs="1">
					<xs:complexType>
						<xs:all>
							<xs:element name="name" type="xs:string" minOccurs="1" maxOccurs="1" />
							<xs:element name="identifie" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="version" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="type" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="ability" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="description" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="author" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="url" type="xs:string"  minOccurs="1" maxOccurs="1" />
						</xs:all>
						<xs:attribute name="setting" type="xs:boolean" />
					</xs:complexType>
				</xs:element>
				<xs:element name="platform" minOccurs="0" maxOccurs="1">
					<xs:complexType>
						<xs:all>
							<xs:element name="subscribes" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="message" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
								</xs:complexType>
							</xs:element>
							<xs:element name="handles" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="message" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
								</xs:complexType>
							</xs:element>
							<xs:element name="rule" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
						</xs:all>
					</xs:complexType>
				</xs:element>
				<xs:element name="bindings" minOccurs="0" maxOccurs="1">
					<xs:complexType>
						<xs:all>
							<xs:element name="cover" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="entry" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
									<xs:attribute name="call" type="xs:string" />
								</xs:complexType>
							</xs:element>
							<xs:element name="rule" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="entry" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
									<xs:attribute name="call" type="xs:string" />
								</xs:complexType>
							</xs:element>
							<xs:element name="menu" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="entry" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
									<xs:attribute name="call" type="xs:string" />
								</xs:complexType>
							</xs:element>
							<xs:element name="home" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="entry" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
									<xs:attribute name="call" type="xs:string" />
								</xs:complexType>
							</xs:element>
							<xs:element name="profile" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="entry" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
									<xs:attribute name="call" type="xs:string" />
								</xs:complexType>
							</xs:element>
							<xs:element name="shortcut" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element ref="entry" minOccurs="0" maxOccurs="unbounded" />
									</xs:sequence>
									<xs:attribute name="call" type="xs:string" />
								</xs:complexType>
							</xs:element>
						</xs:all>
					</xs:complexType>
				</xs:element>
				<xs:element name="install" type="xs:string" minOccurs="0" maxOccurs="1" />
				<xs:element name="uninstall" type="xs:string" minOccurs="0" maxOccurs="1" />
				<xs:element name="upgrade" type="xs:string" minOccurs="0" maxOccurs="1" />
			</xs:all>
			<xs:attribute name="versionCode" type="xs:string" />
		</xs:complexType>
	</xs:element>
</xs:schema>
TPL;
	return trim($xsd);
}

function ext_template_manifest($tpl) {
	$manifest = array();
	$filename = IA_ROOT . '/themes/mobile/' . $tpl . '/manifest.xml';
	if (!file_exists($filename)) {
		return array();
	}
	$xml = str_replace(array('&'), array('&amp;'), file_get_contents($filename));
	$xml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if (empty($xml)) {
		return array();
	}
	$manifest['name'] = strval($xml->identifie);
	if(empty($manifest['name']) || $manifest['name'] != $tpl) {
		return array();
	}
	$manifest['title'] = strval($xml->title);
	if(empty($manifest['title'])) {
		return array();
	}
	$manifest['description'] = strval($xml->description);
	$manifest['author'] = strval($xml->author);
	$manifest['url'] = strval($xml->url);
	
	if($xml->settings->item) {
		foreach($xml->settings->item as $msg) {
			$attrs = $msg->attributes();
			$manifest['settings'][trim(strval($attrs['variable']))] = trim(strval($attrs['content']));
		}
	}
	return $manifest;
}
