<?php

/**
 * @desc 数据库访问类父类
 * @author ChenLuoyong
 * @date 2014年10月7日
 */
abstract class BaseDAO
{

    /**
     * @var CDbTransaction
     */
    protected $dbTransaction;

    /**
     * @var CDbConnection
     */
    protected $dbConnection;

    /**
     * @var CDbCommand
     */
    protected $dbCommand;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var string
     */
    protected $created;

    /**
     * @var string
     */
    protected $updated;

    /**
     * @var string
     */
    protected static $paramAlias = ':p';

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var array
     */
    private static $_instances = array();

    /**
     * @desc 创建实例
     * @param string $className
     * @return BaseDAO
     * @author ChenLuoyong
     * @date 2014-10-10
     */
    protected static function createInstance($className)
    {
        if (isset(self::$_instances[$className])) {
            return self::$_instances[$className];
        }
        $instance = self::$_instances[$className] = new $className();
        return $instance;
    }

    /**
     * @desc 释放数据连接
     * @author YangLong
     * @date 2015-10-08
     */
    public function __destruct()
    {
        $this->dbConnection->setActive(false);
    }

    /**
     * @desc 判断表名、主键、传入参数是否为空
     * @param mixed 可变长参数列表
     * @return boolean [true: 有参数为空；false: 没有空字段]
     */
    public function isParamsEmpty()
    {
        if (empty($this->tableName) || empty($this->primaryKey)) {
            return true;
        }
        $paramArray = func_get_args();
        if (! empty($paramArray)) {
            foreach ($paramArray as $param) {
                if (empty($param)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @desc 测试表记录是否已存在
     * @param array $conditions 格式array('fieldName'=>'value')键名为字段名，值为要匹配的值
     * @param boolean $returnPk 是否回填查询到的记录的PrimaryKey
     * @return boolean 存在则返回true，否则返回false
     * @author Weixun Luo
     * @date 2014-10-11
     */
    public function isExists(&$conditions, $returnPk = false)
    {
        if ($this->isParamsEmpty($conditions)) {
            return false;
        }
        $record = $this->findByAttributes($conditions, $this->primaryKey);
        if (empty($record)) {
            return false;
        }
        if ($returnPk) {
            // 记录存在，将record的主建返回给参数
            $conditions[$this->primaryKey] = $record[$this->primaryKey];
        }
        return true;
    }

    /**
     * @desc 根据主键查找记录
     * @param int $pk 主键值
     * @param array $selections 查找需要返回的字段名
     * @return mixed 查找到的第一条记录 或 false
     * @author Weixun Luo
     * @date 2014-10-13
     */
    public function findByPk($pk, $selections = array())
    {
        if ($this->isParamsEmpty($pk)) {
            return false;
        }
        $selects = '*';
        if ($selections) {
            $selects = implode(',', $selections);
        }
        try {
            $result = $this->dbCommand->reset()
                ->select($selects)
                ->from($this->tableName)
                ->where($this->primaryKey . ' = :primaryKey', array(
                ':primaryKey' => $pk
            ))
                ->queryRow();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 根据条件查找一条记录
     * @param mixed $criteria 待匹配记录的(field-value)键值对数组 或者 为CDbCriteria类实例
     * @param mixed $selections 查找需要返回的字段名string eg."a,b,c"或array eg.array('a','b','c')
     * @param mixed $order the columns (and the directions) to be ordered by,
     * either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC'))
     * @return 查找到的第一条记录 或 false
     * @author Weixun Luo
     * @date 2014-10-13
     */
    public function findByAttributes($criteria, $selections = array(), $order = array())
    {
        if ($this->isParamsEmpty($criteria)) {
            return false;
        }
        $conditionArray = $this->toConditionExpress($criteria);
        if ($selections) {
            $conditionArray['select'] = is_array($selections) ? implode(',', $selections) : $selections;
        }
        $conditionArray['select'] = Utility::getArrayValue($conditionArray, 'select', '*');
        $conditionArray['order'] = $order ? $order : Utility::getArrayValue($conditionArray, 'order', '');
        try {
            $result = $this->dbCommand->reset()
                ->select($conditionArray['select'])
                ->from($this->tableName)
                ->where($conditionArray['condition'], $conditionArray['params'])
                ->order($conditionArray['order'])
                ->queryRow();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 根据条件查找所有记录
     * @param mixed $criteria 待匹配记录的(field-value)键值对数组 或者 为CDbCriteria类实例
     * @param array $selections 查找需要返回的字段名
     * @param mixed $order the columns (and the directions) to be ordered by,
     * either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC'))
     * @param integer $limit the limit
     * @param integer $offset the offset
     * @return 查找到的所有条记录 或 false
     * @author Weixun Luo
     * @date 2014-10-13
     */
    public function findAllByAttributes($criteria, $selections = array(), $order = array(), $limit = null, $offset = null)
    {
        if ($this->isParamsEmpty($criteria)) {
            return false;
        }
        $conditionArray = $this->toConditionExpress($criteria);
        if (! empty($selections)) {
            $conditionArray['select'] = $selections;
        }
        if (! empty($order)) {
            $conditionArray['order'] = $order;
        }
        if (! empty($limit)) {
            $conditionArray['limit'] = $limit;
        }
        if (! empty($offset)) {
            $conditionArray['offset'] = $offset;
        }
        $this->dbCommand = $this->buildFindCommand($conditionArray);
        try {
            $result = $this->dbCommand->queryAll();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 根据主键Id删除记录
     * @param mixed $pk 主键id或者id数组
     * @return integer number of rows affected by the execution OR false
     * @author Weixun Luo
     * @date 2014-10-13
     */
    public function deleteByPk($pk)
    {
        if ($this->isParamsEmpty($pk)) {
            return false;
        }
        $this->dbCommand->reset();
        try {
            if (is_array($pk)) {
                $setValues = array();
                $idsExpress = '';
                for ($i = count($pk) - 1; $i >= 0; $i --) {
                    $idsExpress .= ($idsExpress ? ',' : '') . self::$paramAlias . $i;
                    $setValues[self::$paramAlias . $i] = $pk[$i];
                }
                $sql = "delete from " . $this->tableName . " where " . $this->primaryKey . " in(" . $idsExpress . ")";
                $this->dbCommand->setText($sql);
                $affectRows = $this->dbCommand->bindValues($setValues)->execute();
            } else {
                $affectRows = $this->dbCommand->delete($this->tableName, $this->primaryKey . ' = :primartKey', array(
                    ':primartKey' => $pk
                ));
            }
            return $affectRows;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 根据条件删除记录
     * @param mixed $criteria 待匹配记录的(field-value)键值对数组 或者 为CDbCriteria类实例
     * @return integer number of rows affected by the execution OR false
     * @author Weixun Luo
     * @date 2015-04-15
     */
    public function delete($criteria)
    {
        if ($this->isParamsEmpty($criteria)) {
            return false;
        }
        $conditionArray = $this->toConditionExpress($criteria);
        try {
            $affectRows = $this->dbCommand->reset()->delete($this->tableName, $conditionArray['condition'], $conditionArray['params']);
            return $affectRows;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 根据主键Id更新记录
     * @param mixed $pk 记录主键id或id数组
     * @param array $params 字段数组，键名为字段名，值为字段值
     * @return integer number of rows affected by the execution OR false
     * @author Weixun Luo
     * @date 2014-10-13
     */
    public function updateByPk($pk, $params)
    {
        if ($this->isParamsEmpty($pk, $params) || ! is_array($params)) {
            return false;
        }
        if ($this->updated) {
            // 如果该表有定义更新时间戳字段，则追加更新时间戳
            $params[$this->updated] = time();
        }
        $this->dbCommand->reset();
        try {
            if (is_array($pk)) {
                $setValues = array();
                $idsExpress = '';
                $j = count($pk);
                for ($i = $j - 1; $i >= 0; $i --) {
                    $idsExpress .= ($idsExpress ? ',' : '') . self::$paramAlias . $i;
                    $setValues[self::$paramAlias . $i] = $pk[$i];
                }
                $setExpress = '';
                foreach ($params as $columnName => $value) {
                    $setExpress .= ($setExpress ? ',' : '') . $columnName . '=' . self::$paramAlias . $j;
                    $setValues[self::$paramAlias . $j] = $value;
                    $j ++;
                }
                $sql = "update " . $this->tableName . " set {$setExpress} where " . $this->primaryKey . " in(" . $idsExpress . ")";
                $affectRows = $this->dbCommand->setText($sql)
                    ->bindValues($setValues)
                    ->execute();
            } else {
                $affectRows = $this->dbCommand->update($this->tableName, $params, $this->primaryKey . ' = :primartKey', 
                    array(
                        ':primartKey' => $pk
                    ));
            }
            return $affectRows;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 根据条件更新记录
     * @param mixed $criteria 待匹配记录的(field-value)键值对数组 或者 为CDbCriteria类实例
     * @param array $params 要更新的的键值对数组，键名为字段名，值为字段值
     * @return integer number of rows affected by the execution OR false
     * @author Weixun Luo
     * @date 2014-11-20
     */
    public function update($criteria, $params)
    {
        if ($this->isParamsEmpty($criteria, $params) || ! is_array($params)) {
            return false;
        }
        if ($this->updated) {
            // 如果该表有定义更新时间戳字段，则追加更新时间戳
            $params[$this->updated] = time();
        }
        $this->dbCommand->reset();
        try {
            $setValue = "";
            $conditionArray = "";
            foreach ($params as $key => $value) {
                $setValue .= ($setValue ? "," : "") . $key . "={$value}";
            }
            $conditionArray = $this->toConditionExpress($criteria);
            $affectRows = $this->dbCommand->update($this->tableName, $params, $conditionArray['condition'], $conditionArray['params']);
            return $affectRows;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 插入一条记录
     * @param array $params 字段数组，键名为字段名，值为字段值
     * @return mixed 插入数据的ID或false
     * @author Weixun Luo
     * @date 2014-10-27
     */
    public function insert($params, $returnRow = false)
    {
        if ($this->isParamsEmpty($params) || ! is_array($params)) {
            return false;
        }
        if ($this->created) {
            // 如果该表有定义创建时间戳字段，则追加创建时间戳
            $params[$this->created] = time();
        }
        if ($this->updated) {
            // 如果该表有定义更新时间戳字段，则追加更新时间戳
            $params[$this->updated] = time();
        }
        $this->dbCommand->reset();
        try {
            $affectRows = $this->dbCommand->insert($this->tableName, $params);
            if ($returnRow) {
                // 返回执行成功行数
                return $affectRows;
            }
            if ($affectRows) {
                $id = $this->dbConnection->getLastInsertID($this->primaryKey);
                return $id;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 插入多条记录(注意：某条记录待插入字段如果没有值，将默认为String '')
     * @param array $paramsArray 包含多个字段数组的数组，字段数组键名为字段名，值为字段值
     * @return integer number of rows affected by the execution OR false
     * @author Weixun Luo
     * @date 2015-01-27
     */
    public function insertMulti($paramsArray)
    {
        if ($this->isParamsEmpty($paramsArray)) {
            return false;
        }
        
        // Get all columns that need to insert
        $columns = array();
        foreach ($paramsArray as $rowData) {
            foreach ($rowData as $columnName => $columnValue) {
                if (! in_array($columnName, $columns, true))
                    if ($columnName !== null) {
                        $columns[] = $columnName;
                    }
            }
        }
        
        $paramCount = 0;
        $paramValues = array();
        $rowInsertValuesArray = array();
        // 遍历待插入数据数组，构建插入数据sql
        foreach ($paramsArray as $rowData) {
            $rowValues = array();
            foreach ($columns as $columnName) {
                if (isset($rowData[$columnName])) {
                    $rowValues[$columnName] = self::$paramAlias . $paramCount;
                    $paramValues[self::$paramAlias . $paramCount] = $rowData[$columnName];
                } else {
                    $rowValues[$columnName] = '\'\'';
                }
                $paramCount ++;
            }
            $rowInsertValuesArray[] = '(' . implode(',', $rowValues) . ')';
        }
        
        $columnInsertNames = '(' . implode(',', $columns) . ')';
        $rowInsertValues = implode(',', $rowInsertValuesArray);
        $sql = "INSERT INTO `{$this->tableName}` {$columnInsertNames} VALUES {$rowInsertValues};";
        try {
            $affectRows = $this->dbCommand->reset()
                ->setText($sql)
                ->bindValues($paramValues)
                ->execute();
            return $affectRows;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 保存一条记录，记录存在则更新，不存在则插入(不建议使用，效率低，但如果需要如此严格的逻辑的时候可以使用)
     * @param array $params 待更新的(field-value)键值对
     * @param array $conditions 格式array('fieldName'=>'value')键名为字段名，值为要匹配的值
     * @return integer number of rows affected by the execution OR false
     * @author Weixun Luo
     * @date 2015-04-15
     */
    public function save($params, $conditions = array())
    {
        $isNotSame = true;
        if (empty($conditions)) {
            $conditions = $params;
            $isNotSame = false;
        }
        try {
            $isExists = $this->isExists($conditions);
            $affectRows = 1;
            if ($isExists && $isNotSame) {
                $affectRows = $this->update($conditions, $params);
                if ($affectRows === 0) {
                    $affectRows = $this->isExists($conditions) ? 1 : 0;
                }
            } else {
                $affectRows = $this->insert($params, true);
            }
            return $affectRows;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 返回最近插入的一条数据的ID
     * @return integer 最近插入的一条数据的ID
     * @author Weixun Luo
     * @date 2014-11-11
     */
    public function getLastInsertID()
    {
        try {
            $lastInsertId = $this->dbConnection->getLastInsertID($this->primaryKey);
            return $lastInsertId;
        } catch (Exception $ex) {
            return 0;
        }
    }

    /**
     * @desc 统计满足条件的记录条数
     * @param mixed $criteria 待匹配记录的(field-value)键值对数组 或者 为CDbCriteria类实例
     * @return 满足条件的记录条数
     * @author Weixun Luo
     * @date 2015-03-09
     */
    public function count($criteria)
    {
        if ($this->isParamsEmpty($criteria)) {
            return false;
        }
        $conditionArray = $this->toConditionExpress($criteria);
        // Reset the select to count express
        $conditionArray['select'] = "COUNT({$this->primaryKey})";
        $this->dbCommand = $this->buildFindCommand($conditionArray);
        try {
            $rows = $this->dbCommand->queryScalar();
            return intval($rows);
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @desc 返回表的全部字段名的数组
     * @return array 字段名数组
     * @author Weixun Luo
     * @date 2014-10-30
     */
    public function getAllFields()
    {
        return $this->fields;
    }

    /**
     * @desc 返回表名
     * @return string 表名
     * @author Weixun Luo
     * @date 2014-12-01
     */
    public function getTableName()
    {
        if (empty($this->tableName)) {
            return '';
        }
        return $this->tableName;
    }

    /**
     * @desc 返回表主键
     * @return string 表主键
     * @author Weixun Luo
     * @date 2014-11-13
     */
    public function getPk()
    {
        if (empty($this->primaryKey)) {
            return '';
        }
        return $this->primaryKey;
    }

    /**
     * @desc 翻译条件给Where语句匹配用（暂时只支持'and'连接条件）
     * @param mixed $criteria 要匹配记录的条件和值，键名为字段名，值为字段值 或者为CDbCriteria类实例
     * @return mixed
     * @author Weixun Luo
     * @date 2014-10-30
     */
    protected function toConditionExpress($criteria)
    {
        $where = '';
        $params = array();
        if (is_array($criteria)) {
            $i = 0;
            // 键值对数组条件
            foreach ($criteria as $key => $value) {
                // 此处正则匹配有待改进以更全面的支持(暂只支持'>','<','=','>=','<=','<>','!=')
                preg_match('/[^\w\s]+\Z/', $key, $matches);
                $operator = $matches ? $matches[0] : '=';
                $field = str_replace($operator, '', $key);
                $fieldAlias = self::$paramAlias . $i; // 用统一规则参数名, 提高函数效率
                $where .= ($where ? ' and ' : '') . $field . ' ' . $operator . ' ' . $fieldAlias;
                $params[$fieldAlias] = $value;
                $i ++;
            }
        }
        if ($criteria instanceof CDbCriteria) {
            // CDbCriteria条件实例
            return $criteria->toArray();
        }
        return array(
            'condition' => $where,
            'params' => $params
        );
    }

    /**
     * @desc 创建查询command
     * @param array $conditionArray 查询条件数组
     * @return string
     * @author Weixun Luo
     * @date 2015-01-26
     */
    private function buildFindCommand($conditionArray)
    {
        if ($this->isParamsEmpty($conditionArray) || ! is_array($conditionArray)) {
            return $this->dbCommand;
        }
        
        $commandBuilder = $this->dbConnection->getCommandBuilder();
        $alias = Utility::getArrayValue($conditionArray, 'alias');
        $distinct = Utility::getArrayValue($conditionArray, 'distinct', false);
        $select = Utility::getArrayValue($conditionArray, 'select', '*');
        $join = Utility::getArrayValue($conditionArray, 'join');
        $order = Utility::getArrayValue($conditionArray, 'order');
        if (is_array($select)) {
            $select = implode(",", $select);
        }
        if (is_array($join)) {
            $join = implode("\n", $join);
        }
        if (is_array($order)) {
            $order = implode(",", $order);
        }
        
        $sql = ($distinct ? "SELECT DISTINCT" : "SELECT") . " {$select} FROM {$this->tableName} $alias";
        $sql = $commandBuilder->applyJoin($sql, $join);
        $sql = $commandBuilder->applyCondition($sql, Utility::getArrayValue($conditionArray, 'condition'));
        $sql = $commandBuilder->applyGroup($sql, Utility::getArrayValue($conditionArray, 'group'));
        $sql = $commandBuilder->applyHaving($sql, Utility::getArrayValue($conditionArray, 'having'));
        $sql = $commandBuilder->applyOrder($sql, $order);
        $sql = $commandBuilder->applyLimit($sql, Utility::getArrayValue($conditionArray, 'limit', - 1), 
            Utility::getArrayValue($conditionArray, 'offset', - 1));
        
        $this->dbCommand->reset()
            ->setText($sql)
            ->bindValues($conditionArray['params']);
        return $this->dbCommand;
    }

    /**
     * @desc 简易的根据条件把某个字段+1
     * @param string $field 需要自增的字段
     * @param string $conditions 条件SQL表达式
     * @param array $params
     * @author YangLong
     * @date 2015-03-27
     * @return boolean
     */
    public function increase($field, $conditions = 'false', $params = array())
    {
        if (empty($field)) {
            return false;
        }
        try {
            $this->dbCommand->reset();
            $this->dbCommand->setText("UPDATE {$this->tableName} SET {$field} = {$field} + 1 WHERE {$conditions}")->execute($params);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @desc 简易的根据条件把某个字段-1
     * @param string $field 需要自增的字段
     * @param string $conditions 条件SQL表达式
     * @param array $params
     * @author YangLong
     * @date 2015-09-01
     * @return boolean
     */
    public function decrease($field, $conditions = 'false', $params = array())
    {
        if (empty($field)) {
            return false;
        }
        try {
            $this->dbCommand->reset();
            $this->dbCommand->setText("UPDATE {$this->tableName} SET {$field} = {$field} - 1 WHERE {$conditions}")->execute($params);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @desc 省去表名的update
     * @param array $columns the column data (name=>value) to be updated.
     * @param mixed $conditions 条件
     * @param array $params 参数
     * @param string $paramscheck 是否校验params是否重复
     * @author YangLong
     * @date 2015-03-27
     * @return number
     */
    public function iupdate($columns, $conditions, $params, $paramscheck = true)
    {
        $this->dbCommand->reset();
        if ($paramscheck) {
            imsTool::paramsDuplicationCkeck($columns, $params);
        }
        return $this->dbCommand->update($this->tableName, $columns, $conditions, $params);
    }

    /**
     * @desc 根据条件判断记录是否存在，存在更新，不存在插入
     * @param array $columns 列
     * @param mixed $conditions 条件
     * @param array $params 参数
     * @param string $returnpk 返回自增ID
     * @param string $tinsert 使用事务插入
     * @author YangLong
     * @date 2015-03-31
     * @return string|number|mixed
     */
    public function ireplaceinto($columns, $conditions, $params, $returnpk = false, $tinsert = false)
    {
        $tinsert and $this->begintransaction();
        $id = $this->dbCommand->reset()
            ->select($this->primaryKey)
            ->from($this->tableName)
            ->where($conditions, $params)
            ->queryAll();
        $this->dbCommand->reset();
        if (empty($id)) {
            if (! empty($this->created) && ! isset($columns[$this->created])) {
                $columns[$this->created] = time();
            }
            $result = $this->dbCommand->insert($this->tableName, $columns);
            $tinsert and $this->commit();
            if ($returnpk) {
                return $this->dbConnection->lastInsertID;
            } else {
                return $result;
            }
        } else {
            if (! empty($this->updated) && ! isset($columns[$this->updated])) {
                $columns[$this->updated] = time();
            }
            $result = $this->dbCommand->update($this->tableName, $columns, $conditions, $params);
            $tinsert and $this->commit();
            if ($returnpk) {
                if (count($id) == 1) {
                    $id = array_shift($id);
                    $id = array_shift($id);
                    return $id;
                } else {
                    return $id;
                }
            } else {
                return $result;
            }
        }
    }

    /**
     * @desc 省去表名的delete
     * @param mixed $conditions
     * @param array $params
     * @author YangLong
     * @date 2015-03-31
     * @return number
     */
    public function idelete($conditions, $params)
    {
        $this->dbCommand->reset();
        return $this->dbCommand->delete($this->tableName, $conditions, $params);
    }

    /**
     * @desc 省去表名的insert
     * @param array $columns
     * @param boolean $returnpk
     * @author YangLong
     * @date 2015-03-31
     * @return string|number
     */
    public function iinsert($columns, $returnpk = FALSE)
    {
        $this->dbCommand->reset();
        $affectedrows = $this->dbCommand->insert($this->tableName, $columns);
        if ($returnpk) {
            return $this->dbConnection->lastInsertID;
        } else {
            return $affectedrows;
        }
    }

    /**
     * @desc 省去表名select
     * @param mixed $columns a string (e.g. "id, name") or an array (e.g. array('id', 'name'))
     * @param mixed $conditions the conditions that should be put in the WHERE part.
     * @param array $params the parameters (name=>value) to be bound to the query
     * @param mixed $returnall true:queryAll/false:queryRow
     * @param array $joinArray join参数 array(array($table, $conditions, $params),...)
     * @param string $tableAlias 主表别名
     * @param mixed $order string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
     * @param int $limit the limit
     * @param int $offset the offset
     * @param string $option the option /SQL_CALC_FOUND_ROWS
     * @param mixed $groups
     * @author YangLong
     * @date 2015-05-04
     * @return boolean|Ambigous <multitype:, mixed>|mixed
     */
    public function iselect($columns, $conditions, $params, $returnall = true, $joinArray = array(), $tableAlias = '', $order = '', $limit = 0, $offset = null, 
        $option = '', $groups = '')
    {
        if (empty($columns) || empty($conditions)) {
            return false;
        }
        
        $this->dbCommand->reset();
        if (empty($tableAlias)) {
            $this->dbCommand->select($columns, $option)->from($this->tableName);
        } else {
            $this->dbCommand->select($columns, $option)->from($this->tableName . ' ' . $tableAlias);
        }
        if (empty($params)) {
            $this->dbCommand->where($conditions);
        } else {
            $this->dbCommand->where($conditions, $params);
        }
        if (! empty($order)) {
            $this->dbCommand->order($order);
        }
        if (! empty($limit)) {
            $this->dbCommand->limit($limit, $offset);
        }
        if (is_array($joinArray) && ! empty($joinArray)) {
            foreach ($joinArray as $joinUnit) {
                if (isset($joinUnit[0]) && isset($joinUnit[1])) {
                    if (! isset($joinUnit[2])) {
                        $joinUnit[2] = array();
                    }
                    if (isset($joinUnit['left'])) {
                        $this->dbCommand->leftJoin($joinUnit[0], $joinUnit[1], $joinUnit[2]);
                    } elseif (isset($joinUnit['right'])) {
                        $this->dbCommand->rightJoin($joinUnit[0], $joinUnit[1], $joinUnit[2]);
                    } else {
                        $this->dbCommand->join($joinUnit[0], $joinUnit[1], $joinUnit[2]);
                    }
                }
            }
        }
        if (! empty($groups)) {
            $this->dbCommand->group($groups);
        }
        if ($returnall === 'queryScalar') {
            $this->dbCommand->limit(1);
            return $this->dbCommand->queryScalar();
        } elseif ($returnall === 'queryColumn') {
            return $this->dbCommand->queryColumn();
        } elseif ($returnall) {
            return $this->dbCommand->queryAll();
        } else {
            $this->dbCommand->limit(1);
            return $this->dbCommand->queryRow();
        }
    }

    /**
     * @desc 运行SQL返回结果
     * @param string $text
     * @param array $params
     * @param string $querytype queryScalar/queryRow/queryColumn/queryAll/execute
     * @author YangLong
     * @date 2015-09-01
     * @return number
     */
    public function setTextQuery($text, $params = array(), $querytype = 'execute')
    {
        if ($querytype === 'queryScalar') {
            return $this->dbCommand->setText($text)->queryScalar($params);
        } elseif ($querytype === 'queryRow') {
            return $this->dbCommand->setText($text)->queryRow(true, $params);
        } elseif ($querytype === 'queryColumn') {
            return $this->dbCommand->setText($text)->queryColumn($params);
        } elseif ($querytype === 'queryAll') {
            return $this->dbCommand->setText($text)->queryAll(true, $params);
        } elseif ($querytype === 'execute') {
            return $this->dbCommand->setText($text)->execute($params);
        }
    }

    /**
     * @desc 获取记录总数
     * @param string $conditions
     * @param array $params
     * @author YangLong
     * @date 2015-11-23
     * @return number
     */
    public function getCount($conditions, $params)
    {
        return $this->dbCommand->setText('SELECT count(*) FROM `' . $this->tableName . '` WHERE ' . $conditions)->queryScalar($params);
    }

    /**
     * @desc 获取上次dbCommand运行的SQL
     * @author YangLong
     * @date 2015-07-02
     * @return string
     */
    public function getLastSql()
    {
        return $this->dbCommand->text;
    }

    /**
     * @desc 根据主键获取行
     * @param int $pk 主键
     * @param mixed $columns a string (e.g. "id, name") or an array (e.g. array('id', 'name'))
     * @author YangLong
     * @date 2015-04-17
     * @return mixed
     */
    public function getValuesByPk($pk, $columns)
    {
        $conditions = "{$this->primaryKey}=:pk";
        $params = array(
            ':pk' => $pk
        );
        return $this->dbCommand->reset()
            ->select($columns)
            ->from($this->tableName)
            ->where($conditions, $params)
            ->limit(1)
            ->queryRow();
    }

    /**
     * @desc 批量插入数据
     * @param array $data
     *      引用传参,使用后会被释放
     *      data format:
     *      array(array( 'clo1'=>value1,'clo2'=>value2), array('clo1'=>value1,'clo2'=>value2) ...)
     *      或
     *      array(array( value1,value2), array(value1,value2) ...)
     * @param array $fields 可选参数，字段列表，第二种数据格式必须使用，否则使用空值
     *      array('clo1','clo2','clo3')
     * @param number $splitSize 拆分插入的尺寸
     * @param string $useTransaction 是否使用事务，请保持默认值，除非你知道你在做什么
     * @author YangLong
     * @date 2015-04-10
     * @return number|boolean
     */
    function iMultiInsert(&$data, $fields = array(), $splitSize = 10000, $useTransaction = true)
    {
        if (! is_array($data) || ! is_array($fields)) {
            return false;
        }
        $splitSize = (int) $splitSize;
        if ($splitSize <= 0) {
            $splitSize = 10000;
        }
        if (! is_bool($useTransaction)) {
            $useTransaction = (bool) $useTransaction;
        }
        
        $tableName = $this->tableName;
        $rowsSQL = array();
        $toBind = array();
        if (empty($fields)) {
            $columnNames = array_keys($data[0]);
        } else {
            $columnNames = $fields;
        }
        
        $useTransaction and $this->begintransaction();
        try {
            $i = 0;
            $rowCount = 0;
            while (true) {
                $i ++;
                $row = array_pop($data);
                if ($row !== null) {
                    $params = array();
                    foreach ($row as $key => $columnValue) {
                        if (empty($fields)) {
                            $param = ":{$key}_{$i}";
                        } else {
                            $param = ":{$columnNames[$key]}_{$i}";
                        }
                        
                        $params[] = $param;
                        $toBind[$param] = $columnValue;
                    }
                    $rowsSQL[] = "(" . implode(", ", $params) . ")";
                }
                if ($i === $splitSize || $row === null) {
                    $i = 0;
                    $result = $this->iMultiInsertOne($tableName, $columnNames, $rowsSQL, $toBind);
                    $rowCount += $result;
                }
                if ($row === null) {
                    break;
                }
            }
            $useTransaction and $this->commit();
            return $rowCount;
        } catch (Exception $e) {
            file_put_contents('iMultiInsertErr.log', $e->getMessage() . "\n", FILE_APPEND);
            $useTransaction and $this->rollback();
            return false;
        }
    }

    /**
     * @desc 单次批量插入
     * @param string $tableName 表名
     * @param array $columnNames 字段数组
     * @param array $rowsSQL 值组数组
     * @param array $toBind 所有参数数组
     * @author YangLong
     * @date 2015-04-10
     * @return boolean|number
     */
    private function iMultiInsertOne($tableName, $columnNames, &$rowsSQL, &$toBind)
    {
        if (empty($rowsSQL)) {
            return false;
        }
        $sql = "INSERT INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);
        
        $this->dbCommand->reset();
        $this->dbCommand->setText($sql)->prepare();
        foreach ($toBind as $param => $val) {
            $paramtype = null;
            $this->dbCommand->bindValue($param, $val, $paramtype);
        }
        
        $rowsSQL = array();
        $toBind = array();
        
        return $this->dbCommand->execute();
    }

    /**
     * @desc 根据ids(逗号分隔的ID组成的字符串)删除数据
     * @param string $ids
     * @author YangLong
     * @date 2015-03-30
     * @return boolean|number
     */
    public function deleteByIds($ids)
    {
        if (empty($ids)) {
            return false;
        }
        try {
            $conditions = "{$this->primaryKey} in ({$ids})";
            $this->dbCommand->reset();
            return $this->dbCommand->delete($this->tableName, $conditions);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @desc 开始事务
     * @author YangLong
     * @date 2015-03-27
     */
    public function begintransaction()
    {
        $this->dbTransaction = $this->dbConnection->beginTransaction();
        $this->dbCommand = $this->dbConnection->createCommand();
    }

    /**
     * @desc 提交事务
     * @author YangLong
     * @date 2015-03-27
     */
    public function commit()
    {
        $this->dbTransaction->commit();
    }

    /**
     * @desc 回滚事务
     * @author YangLong
     * @date 2015-03-27
     */
    public function rollback()
    {
        $this->dbTransaction->rollback();
    }

    /**
     * @desc 获取属性
     * @param string $name
     * @author YangLong
     * @date 2015-05-04
     * @return mixed|boolean
     */
    public function igetproperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        } else {
            throw new Exception('Undefined Property');
        }
    }
}