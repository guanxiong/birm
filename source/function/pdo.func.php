<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: origins/source/function/pdo.func.php : v 79bfcdb13bc9 : 2014/06/12 01:20:22 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');
/**
 * 初始化 pdo 对象实例
 * @return object->PDO
 */
function pdo() {
	global $_W;
	static $db;
	if(empty($db)) {
		$db = new DB($_W['config']['db']);
	}
	return $db;
}

/**
 * 执行一条非查询语句
 *
 * @param string $sql
 * @param array or string $params
 * @return mixed
 *		  成功返回受影响的行数
 *		  失败返回FALSE
 */
function pdo_query($sql, $params = array()) {
	return pdo()->query($sql, $params);
}

/**
 * 执行SQL返回第一个字段
 *
 * @param string $sql
 * @param array $params
 * @param int $column 返回查询结果的某列，默认为第一列
 * @return mixed
 */
function pdo_fetchcolumn($sql, $params = array(), $column = 0) {
	return pdo()->fetchcolumn($sql, $params, $column);
}
/**
 * 执行SQL返回第一行
 *
 * @param string $sql
 * @param array $params
 * @return mixed
 */
function pdo_fetch($sql, $params = array()) {
	return pdo()->fetch($sql, $params);
}
/**
 * 执行SQL返回全部记录
 *
 * @param string $sql
 * @param array $params
 * @return mixed
 */
function pdo_fetchall($sql, $params = array(), $keyfield = '') {
	return pdo()->fetchall($sql, $params, $keyfield);
}

/**
 * 更新记录
 *
 * @param string $table
 * @param array $data
 *		  要更新的数据数组
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param array $params
 *		  更新条件
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param string $glue
 *		  可以为AND OR
 * @return mixed
 */
function pdo_update($table, $data = array(), $params = array(), $glue = 'AND') {
	return pdo()->update($table, $data, $params, $glue);
}

/**
 * 更新记录
 *
 * @param string $table
 * @param array $data
 *		  要更新的数据数组
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param boolean $replace
 *		  是否执行REPLACE INTO
 *		  默认为FALSE
 * @return mixed
 */
function pdo_insert($table, $data = array(), $replace = FALSE) {
	return pdo()->insert($table, $data, $replace);
}

/**
 * 删除记录
 *
 * @param string $table
 * @param array $params
 *		  更新条件
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param string $glue
 *		  可以为AND OR
 * @return mixed
 */
function pdo_delete($table, $params = array(), $glue = 'AND') {
	return pdo()->delete($table, $params, $glue);
}

/**
 * 返回lastInsertId
 *
 */
function pdo_insertid() {
	return pdo()->insertid();
}

function pdo_begin() {
	pdo()->begin();
}

function pdo_commit() {
	pdo()->commit();
}

function pdo_rollback() {
	pdo()->rollBack();
}

/**
 * 获取pdo操作错误信息列表
 * @param bool $output 是否要输出执行记录和执行错误信息
 * @param array $append 加入执行信息，如果此参数不为空则 $output 参数为 false
 * @return array
 */
function pdo_debug($output = true, $append = array()) {
	return pdo()->debug($output, $append);
}
/**
 * 执行SQL文件
 */
function pdo_run($sql) {
	return pdo()->run($sql);
}

function pdo_fieldexists($tablename, $fieldname = '') {
	return pdo()->fieldexists($tablename, $fieldname);
}

function pdo_indexexists($tablename, $indexname = '') {
	return pdo()->indexexists($tablename, $indexname);
}
/**
 * 获取所有字段,用于过滤字段
 * @param string $tablename 原始表名
 * @return array 所有表名 array('col1','col2');
 */
function pdo_fetchallfields($tablename){
	$fields = pdo_fetchall("DESCRIBE {$tablename}", array(), 'Field');
	$fields = array_keys($fields);
	return $fields;
}
