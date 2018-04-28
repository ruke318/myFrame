<?php
namespace Face;

use PDO;
use Lib\Request as Str;

class DB
{
	/**
	 * config
	 * @var [type]
	 */
	private static $config;

	/**
	 * DB 实例
	 * @var [type]
	 */
	private static $self;

	/**
	 * pdo 实例
	 * @var [type]
	 */
	private static $pdo;

	/**
	 * 主键
	 * @var [type]
	 */
	private static $pk;

	/**
	 * 获取结果的类型, OBJ是对象, ASSOC是数组
	 * @var string
	 */
	private static $ext = 'OBJ';

	/**
	 * 表字段
	 * @var array
	 */
	private static $fields = [];

	/**
	 * 表名字
	 * @var [type]
	 */
	private static $tableName;

	/**
	 * 数据库连接方式, 默认mysql
	 * @var string
	 */
	private static $sqlType = 'mysql';

  /**
   * where条件数组
   * @var array
   */
	private $where = [];

	/**
	 * 参数数组
	 * @var array
	 */
	private $params = [];

	/**
	 * orWhere 参数数组
	 * @var array
	 */
	private $or = [];

	/**
	 * 操作 select, update, update, insert, count
	 * @var [type]
	 */
	private $action;

	/**
	 * 需要查询的字段
	 * @var string
	 */
	private $fieldsList = ' * ';

	/**
	 * 更新字段数组
	 * @var array
	 */
	private $updateList = [];

	/**
	 * order 排序字符串
	 * @var [type]
	 */
	private $order;

	/**
	 * 分组字符串
	 * @var [type]
	 */
	private $group;

	/**
	 * insert 数组
	 * @var array
	 */
	private $insertData = [];

	/**
	 * 无需预处理的插入数组
	 * @var array
	 */
	private $insertList = [];

	/**
	 * limit 字段
	 * @var string
	 */
	private $limitList = '';

	/**
	 * 是否过滤
	 * @var boolean
	 */
	private $isCreate = false;

	/**
	 * 统计字段
	 * @var string
	 */
	private $countList = '';

	/**
	 * 表别名
	 * @var [type]
	 */
	private $tableAlias;

	/**
	 * join 字段
	 * @var [type]
	 */
	private $joinStr;

	private function __construct()
	{
		if (!self::$config) {
			self::$config = Config::get(self::$sqlType);
		}
	}
	
	/**
	 * @todo set mysql connet config
	 *
	 * @param [array] $arr set array
	 * @return self
	 */
	public static function setConfig($arr)
	{
		self::$config = Config::get(self::$sqlType);
		foreach ($arr as $k => $v) {
			if (array_key_exists($k, self::$config)) {
				self::$config[$k] = $v;
			}
		}
		return self::$config;
	}

	/**
	 * @todo 初始化pdo
	 *
	 * @return void
	 */
	private static function _init()
	{
		if (!self::$config) {
			self::$config = Config::get(self::$sqlType);
		}
		if (!self::$self) {
			self::$self = new self;
		}
		if (!self::$pdo) {
			try {
				self::$pdo = new \PDO(
					self::$sqlType.':host='.self::$config['host'].';port='.self::$config['port'].';dbname='.self::$config['dbname'],
					self::$config['user'],
					self::$config['pwd']
				);
			} catch(\PDOException $e) {
				$str = new Str;
				$str->log('DB/'.date('Y-m-d').'.log', $e->getMessage());
				return error($e->getMessage());
			}
			
		}
	}

	/**
	 * @todo 设置主键
	 *
	 * @param [type] $pk
	 * @return void
	 */
	public static function setPk ($pk)
	{
		self::$pk = $pk;
	}


	/**
	 * @todo 设置数据库类型
	 * @param [type] $type [description]
	 */
	public static function setType($type)
	{
		self::$sqlType = $type;
	}

	/**
	 * @todo  设置获取结果集的类型, OBJ,ASSOC(array)
	 * @param [type] $ext [description]
	 */
	public static function setExt($ext)
	{
		self::$ext = $ext;
	}

	/**
	 * @todo 初始化dpo并选定表
	 *
	 * @param [string] $table 表名
	 * @return void
	 */
	public static function table($table)
	{
		$tabs = explode(' ', trim($table));
		if (!$table) {
			return error('table 必须');
		}

		//初始化pdo
		self::_init();
		if (count($tabs) == 2) {
			$table = $tabs[0];
			self::$self->tableAlias = $tabs[1];
		} else if (count($tabs) == 3) {
			if ($tabs[1] != 'as') return error('错误的关键字_'.$tabs[1]);
			$table = $tabs[0];
			self::$self->tableAlias = $tabs[2];
		}
		$res = self::$pdo->query('desc '.$table);
		if (!$res) {
			return error('table `'.$table.'` not exists');
		}

		$ret = $res->fetchAll(PDO::FETCH_ASSOC);
		//获取所有字段
		self::$fields = array_column($ret, 'Field');
		self::$tableName = ' '.$table.' ';
		return self::$self;
	}

	/**
	 * @todo 拼接where条件
	 *	2个参数时，参数1为字段，2为等于的值
	 *  3个参数时，参数1为字段，2为条件， 3为值
	 * @return void
	 */
	public function where()
	{
		$list = func_get_args();
		$num = func_num_args();
		if ($num === 3) {
			$field = $list[0];
			$eq = $list[1];
			$where = $list[2];
			if (!is_numeric($where) && !is_bool($where) && !is_null($where)) {
				$where = "'$where'";
			}
			
			$fs = ":".str_replace('.', '_', $field).$this->paramsCount();
			$this->where[] = "$field $eq $fs";
			$this->params[$fs] = $where;
		} else if ($num == 2) {
			list($field, $where) = $list;
			if (!is_numeric($where) && !is_bool($where) && !is_null($where)) {
				$where = "'$where'";
			}
			$fs = ":".$field.$this->paramsCount();
			$this->where[] = "$field = $fs";
			$this->params[$fs] = $where;
		} else {
			return error('至少2个参数');
		}
		return $this;
	}

	/**
	 * @todo 设置orWhere 条件
	 *	2个参数时，参数1为字段，2为等于的值
	 *  3个参数时，参数1为字段，2为条件， 3为值
	 * @return void
	 */
	public function orWhere()
	{
		$list = func_get_args();
		$num = func_num_args();
		$paramsCount = count($this->params);
		if ($num === 3) {
			$field = $list[0];
			$eq = $list[1];
			$where = $list[2];
			if (!is_numeric($where) && !is_bool($where) && !is_null($where)) {
				$where = "'$where'";
			}
			$fs = ":".str_replace('.', '_', $field).$this->paramsCount();
			$this->or[] = "$field $eq $fs";
			$this->params[$fs] = $where;
		} else if ($num == 2) {
			list($field, $where) = $list;
			if (!is_numeric($where) && !is_bool($where) && !is_null($where)) {
				$where = "'$where'";
			}
			$fs = ":".$field.$this->paramsCount();
			$this->or[] = "$field = $fs";
			$this->params[$fs] = $where;
		} else {
			return error('至少2个参数');
		}
		return $this;
	}

	/**
	 * @todo 统计预处理绑定参数个数
	 *
	 * @return void
	 */
	private function paramsCount() {
		return count($this->params);
	}

	/**
	 * @todo 设置查询字段
	 *
	 * @return void
	 */
	public function select()
	{
		$args = func_get_args();
		if (empty($args)) {
			$this->fieldsList = ' * ';
		} else {
			$this->fieldsList = ' '.implode(',', $args).' ';
		}
		return $this;
	}

	private function catchWhere()
	{
		$ret = '';
		$where = implode(' AND ', $this->where);
		$orwhere = implode(' OR ', $this->or);
		if ($orwhere) {
			$orwhere = ' OR '.$orwhere;
		}
		if ($where || $orwhere) {
			$ret .= ' where ';
			if ($where) {
				$ret .= $where;
			}
			if ($orwhere) {
				$ret .= $orwhere;
			}
		}
		return $ret;
	}

	/**
	 * @todo 返回sql语句
	 *
	 * @return void
	 */
	public function _sql()
	{
		switch ($this->action) {
			case 'SELECT':
				$sql = $this->action.$this->fieldsList.'from'.self::$tableName.' '.$this->tableAlias.$this->joinStr;
				$catchWhere = $this->catchWhere();
				$sql .= $catchWhere;
				if (!empty($this->order)) {
					$sql .= 'ORDER BY '.implode(' , ', $this->order);
				}
				if (!empty($this->group)) {
					$sql .= 'GROUP BY '.implode(' , ', $this->group);
				}
				$sql .= $this->limitList;
				return $sql;
			
			case 'UPDATE':
				$sql = $this->action.self::$tableName.'set ';
				$sql .= implode(',', $this->updateList);
				$sql .= $this->catchWhere();
				return $sql;

			case 'DELETE':
				$sql = $this->action.' from '.self::$tableName;
				$sql .= $this->catchWhere();
				return $sql;

			case 'INSERT':
				$sql = $this->action.' into '.self::$tableName;
				$sql .= ' ('.implode(',', $this->insertList).') VALUES ';
				$onSql = [];
				foreach ($this->insertData as $d) {
					$onSql[] = '('.implode(',', $d).')';
				}
				$sql .= implode(',', $onSql);
				return $sql;

			case 'COUNT':
				$sql = 'SELECT '.$this->countList.'from '.self::$tableName;
				$sql .= $this->catchWhere();
				return $sql;
		}

	}

	/**
	 * @todo 获取所有参数
	 *
	 * @return void
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @todo 执行预处理sql语句
	 *
	 * @return void
	 */
	private function doQuery($sql = false)
	{
		if (!$sql) {
			$sql = $this->_sql();
		}
		$stmt = self::$pdo->prepare($sql);
		$ret = $stmt->execute($this->params);
		if ($ret) {
			return $stmt;
		} else {
			return error($sql);
		}
	}

	/**
 * @todo 获取当前表的所有字段
 *
 * @return void
 */
	public function getFields()
	{
		return self::$fields;
	}

	/**
	 * @todo 查询改库下面所有的表
	 *
	 * @return void
	 */
	static function showTables()
	{
		self::_init();
		$res = self::$pdo->query('show tables');
		if (!$res) {
			return [];
		} 
		$ret = $res->fetchAll(PDO::FETCH_ASSOC);
		return $ret;
	}
	/*--------------------------------------------------------GET--------------------------------------------------*/
	/**
	 * @todo 获取查询列表
	 *
	 * @return void
	 */
	public function get()
	{
		$this->action = 'SELECT';
		$res = $this->doQuery();
		$ret = $res->fetchAll(constant('PDO::FETCH_'.self::$ext));
		return $ret;
	}

	/**
	 * @todo 获取查询列表中的第一条
	 *
	 * @return void
	 */
	public function first()
	{
		$this->action = 'SELECT';
		$res = $this->doQuery();
		$ret = $res->fetch(constant('PDO::FETCH_'.self::$ext));
		return $ret;
	}

	/**
	 * @todo 获取第一条中的某个字段
	 *
	 * @param [type] $key
	 * @return void
	 */
	function value($key)
	{
		if (empty($key)) {
			return error('请传入一个字段值');
		}
		$res = $this->first();
		return self::$ext == 'OBJ' ? $res->$key : $res[$key];
	}

	/**
	 * @todo 获取所有之中的某个字段
	 *
	 * @param [type] $key
	 * @return void
	 */
	function pluck($key)
	{
		if (empty($key)){
			return error('请传入一个字段');
		}
		$res = (array)$this->get();
		return array_column($res, $key);
	}


	// ---------------------------------------------query----------------------------------------------
	

	/**
	 * @todo 直接执行sql语句,可进行预处理,查询返回结果, update 和 delete 返回影响行数, insert返回最后一条的id
	 * @param  string $sql    [sql语句]
	 * @param  array  $params [预处理数组]
	 * @return [mix]         [description]
	 */
	static function query($sql, $params = [])
	{
		self::_init();
		$sql = trim($sql);
		if (!empty($params) && substr_count($sql, '?') === count($params)) {
			self::$self->params = $params;
		} else if (substr_count($sql, '?') !== count($params)) {
			return error('预处理数组长度必须和sql中个数相等');
		} else {
			self::$self->params = [];
		}
		$stmt = self::$self->doQuery($sql);
		if (stripos($sql, 'select') === 0) {
			return $stmt->fetchAll(constant('PDO::FETCH_'.self::$ext));
		} else if (stripos($sql, 'update') === 0 || stripos($sql, 'delete') === 0) {
			return $stmt->rowCount();
		} else if (stripos($sql, 'insert') === 0) {
			return self::$pdo->lastInsertId();
		}
	}

	//------------------------------------------------------update--------------------------------
	
	/**
	 * @todo 进行update操作
	 * @param  array  $arr [需要更新的键值对]
	 * @return int      [影响行数]
	 */
	function update(array $arr)
	{
		$isStr = array_filter(array_keys($arr), 'is_string');
		if (empty($isStr)) {
			return error('更新数组必须为键值对');
		}
		if (empty($this->where)) {
			return error('不支持无条件更新');
		}
		if ($this->isCreate) {
			$arr = $this->doCreate($arr);
		}
		$this->action = 'UPDATE';
		foreach ($arr as $k=>$v) {
			if (!in_array($k, self::$fields)) {
				return error('不存在的字段名'.$k);
			}
			$fs = ":".$k.$this->paramsCount();
			$this->updateList[] = "`$k` = $fs";
			$this->params[$fs] = $v;
		}
		$stmt = $this->doQuery();
		return $stmt->rowCount();
	}

	//-----------------------------------------------------delete----------------------------------------------------------
	
	/**
	 * @todo 删除符合条件的数据
	 * @return int 受影响行数
	 */
	function delete()
	{
		if (empty($this->where)) {
			return error('不支持无条件删除');
		}
		$this->action = 'DELETE';
		$stmt = $this->doQuery();
		return $stmt->rowCount();
	}

	//-----------------------------------------------------insert-------------------------------------------------------

	/**
	 * @todo 插入数据,单条数据或者多条数据,多条时,必须保持结构一致
	 * @param  array  $data 需要插入的数组
	 * @return [type]       [description]
	 */
	function insert(array $data)
	{
		$this->action = 'INSERT';
		if (isset($data[0])) {
			$data = $data;
		} else {
			$data = [$data];
		}
		if ($this->isCreate) {
			$data = array_map(function ($item) {
				return $this->doCreate($item);
			}, $data);
		}
		$this->insertPre($data);
		$stmt = $this->doQuery();
		return self::$pdo->lastInsertId();
	}

	/**
	 * @todo  检测处理插入的数组
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	private function insertPre(array $data)
	{
		foreach ($data as $key => $insert) {
			$isStr = array_filter(array_keys($insert), 'is_string');
			if (empty($isStr)){
				return error('必须传入键值对');
			}
			foreach ($insert as $k => $v) {
				if (!in_array($k, self::$fields)) {
					return error('不存在的字段名'.$k);
				}
				$fs = ":".$k.$this->paramsCount();
				$key === 0 ? $this->insertList[] = "$k" : '';
				$this->params[$fs] = "$v";
				$this->insertData[$key][] = $fs;
			}
		}
	}

	//------------------------------------------------------------create---------------------------------------------
	
	/**
	 * @todo 是否对插入或更新的数组进行过滤
	 * @return [type] [description]
	 */
	function create()
	{
		$this->isCreate = true;
		return $this;
	}

	/**
	 * @todo 将数据过滤
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	function doCreate(array $data)
	{
		return array_filter($data, function ($item, $index) {
			return in_array($index, self::$fields);
		}, ARRAY_FILTER_USE_BOTH);
	}


	/**
	 * @todo limit 操作
	 * @return [type] [description]
	 */
	function limit()
	{
		$args = func_get_args();
		$num = func_num_args();
		if ($num === 2) {
			list($prev, $end) = $args;
		} else {
			$prev = 0;
			$end = $args[0];
		}
		$this->limitList = " limit $prev,$end ";
		return $this;
	}


	/**
	 * @todo 分页查询
	 *       page和pageSize的作用顺序是
	 * 				调用参数 > URL query参数 > 默认值
	 * @param  [int] $pageSize [每页大小]
	 * @param  [int] $page     [第几页]
	 * @return [type]           [description]
	 */
	function page($pageSize = null, $page = null)
	{
		$this->action = 'SELECT';
		$str = new Str;
		$page = $page ?: ($str->page ?: 1);
		$page = 1 * $page;
		$pageSize = $pageSize ?: ($str->pageSize ?: 10);
		$offset = ($page - 1) * $pageSize;
		$this->limitList = " limit $offset,$pageSize ";
		$std = new \StdClass;
		$std->data = $this->get();
		$std->currentPage = $page;
		$std->pageSize = $pageSize;
		$std->total = $this->count();
		$std->lastPage = ceil($std->total / $pageSize);
		return $std;
	}


	/**
	 * @todo 统计满足当前条件的数目
	 * @param  string $field [统计的字段 默认是 *]
	 * @return [type]        [description]
	 */
	function count($field = '*')
	{
		$this->action = 'COUNT';
		$asK = 'count_';
		$asK .= $field == '*' ? 'all' : $field;
		$this->countList = " count($field) as $asK ";
		$res = $this->doQuery();
		$ret = $res->fetch(PDO::FETCH_OBJ);
		return (int)$ret->$asK;
	}

	/**
	 * @todo 排序
	 *
	 * @param [type] $order
	 * @return void
	 */
	function orderBy($order) {
		$this->order[] = $order;
		return $this;
	}

	/**
	 * @todo 分组
	 *
	 * @param [type] $group
	 * @return void
	 */
	function groupBy($group) {
		$this->group[] = $group;
		return $this;
	}
  
  /*----------------------------------------------------------join-------------------------------------------*/

  /**
   * [join 关联操作]
   * @param  [type] $joinTable [需要关联的表, 可以 table as t 或者 table t]
   * @param  [type] $left      [等式左边]
   * @param  [type] $eq        [条件]
   * @param  [type] $right     [等式右边]
   * @return [type]            [description]
   */
	function join($joinTable, $left, $eq, $right) {
		$this->joinStr = " join $joinTable on $left $eq $right";
		return $this;
	}

	function leftJoin($joinTable, $left, $eq, $right) {
		$this->joinStr = " left join $joinTable on $left $eq $right";
		return $this;
	}

	function rightJoin($joinTable, $left, $eq, $right) {
		$this->joinStr = " right join $joinTable on $left $eq $right";
		return $this;
	}

	static function __callStatic($method, $params)
	{
		return error('不存在的方法');
	}

	function __call($key, $value) {
		if (strpos($key, 'where') === 0) {
			$this->where(strtolower(substr($key, 5)), ...$value);
			return $this;
		} else {
			return error('不存在的方法');
		}
	}
}