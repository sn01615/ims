<?php

/**
 * @desc job class
 * @author YangLong
 * @date 2015-08-14
 */
abstract class MongoQueue
{

    public static $database = null;

    public static $connection = null;

    public static $environment = null;

    public static $context = null;

    public static $collectionName = 'mongo_queue';

    protected static $environmentLoaded = false;

    /**
     * @desc push a job
     * @param string $className            
     * @param string $methodName            
     * @param array $parameters            
     * @param int $when            
     * @param string $batch            
     * @param string $priority
     * @author YangLong
     * @date 2015-08-14
     * @return null
     */
    public static function push($className, $methodName, $parameters, $when, $batch = false, $priority = null)
    {
        if (! $batch) {
            $collection = self::getCollection();
            return $collection->save(array(
                'object_class' => $className,
                'object_method' => $methodName,
                'parameters' => $parameters,
                'when' => $when,
                'priority' => $priority,
                'locked' => null,
                'locked_at' => null,
                'batch' => 1
            ));
        } else {
            $db = self::getDatabase(array(
                'object_class' => $className,
                'object_method' => $methodName,
                'parameters' => $parameters
            ));
            $collection = $db->selectCollection(self::$collectionName);
            
            $job = $db->command(array(
                'findandmodify' => self::$collectionName,
                'query' => array(
                    'object_class' => $className,
                    'object_method' => $methodName,
                    'parameters' => $parameters,
                    'locked' => null
                ),
                'update' => array(
                    '$inc' => array(
                        'batch' => 1
                    )
                ),
                'upsert' => true,
                'new' => true
            ));
            
            if ($job['ok']) {
                $job = $job['value'];
                $touched = false;
                
                // take the lower 'when'
                if (! isset($job['when']) || $job['when'] > $when) {
                    $job['when'] = $when;
                    $touched = true;
                }
                
                // take the higher 'priority'
                if (! isset($job['priority']) || ($priority !== null && $job['priority'] < $priority)) {
                    $job['priority'] = $priority;
                    $touched = true;
                }
                
                if ($touched) {
                    return $collection->save($job);
                }
            }
        }
    }

    /**
     * @desc job is run?
     * @param string $class_name            
     * @param string $method_name            
     * @return boolean
     */
    public static function hasRunnable($class_name = null, $method_name = null)
    {
        $collection = self::getCollection();
        
        $query = array(
            '$or' => array(
                array(
                    'when' => array(
                        '$lte' => time()
                    )
                ),
                array(
                    'when' => null
                )
            ),
            'locked' => null
        );
        $fields = array(
            '_id' => 0,
            'when' => 1
        );
        
        if ($class_name)
            $query['object_class'] = $class_name;
        
        if ($method_name)
            $query['object_method'] = $method_name;
        
        return ($collection->findOne($query, $fields) != null);
    }

    /**
     * @desc jobs count
     * @return number jobs number
     */
    public static function count()
    {
        $collection = self::getCollection();
        
        $query = array(
            '$or' => array(
                array(
                    'when' => array(
                        '$lte' => time()
                    )
                ),
                array(
                    'when' => null
                )
            ),
            'locked' => null
        );
        return $collection->count($query);
    }

    /**
     * @desc jobs runer.
     * @param string $class_name
     * @param string $method_name
     * @param string $prioritize
     * @author YangLong
     * @date 2015-08-14
     * @return mixed|boolean
     */
    public static function run($class_name = null, $method_name = null, $prioritize = true)
    {
        $db = self::getDatabase();
        $environment = self::initializeEnvironment();
        
        $query = array(
            '$or' => array(
                array(
                    'when' => array(
                        '$lte' => time()
                    )
                ),
                array(
                    'when' => null
                )
            ),
            'locked' => null
        );
        
        if ($class_name)
            $query['object_class'] = $class_name;
        
        if ($method_name)
            $query['object_method'] = $method_name;
        
        $sort = array(
            'when' => 1
        );
        
        if ($prioritize)
            $sort = array(
                'priority' => - 1,
                'when' => 1
            );
        
        $job = $db->command(array(
            "findandmodify" => self::$collectionName,
            "query" => $query,
            "sort" => $sort,
            "update" => array(
                '$set' => array(
                    'locked' => true,
                    'locked_at' => time()
                )
            )
        ));
        
        if ($job['ok']) {
            $jobRecord = $job['value'];
            $jobID = $jobRecord['_id'];
            
            $_runResult = null;
            
            // run the job
            if (isset($jobRecord['object_class'])) {
                $className = $jobRecord['object_class'];
                $method = isset($jobRecord['object_method']) ? $jobRecord['object_method'] : 'perform';
                $parameters = isset($jobRecord['parameters']) ? $jobRecord['parameters'] : array();
                $parameters['_id'] = $jobID;
                
                if (self::$context) {
                    foreach (self::$context as $key => $value) {
                        if (property_exists($className, $key))
                            $className::$$key = $value;
                    }
                }
                
                $_runResult = call_user_func_array(array(
                    new $className(),
                    $method
                ), array(
                    $parameters
                ));
            }
            
            if ($_runResult !== false) {
                // remove the job from the queue
                $db->selectCollection(self::$collectionName)->remove(array(
                    '_id' => $jobID
                ));
            }
            
            return $_runResult;
        }
        
        return false;
    }

    /**
     * @desc get MongoClient object
     * @param string $hint
     * @author YangLong
     * @date 2015-08-14
     * @return MongoClient
     */
    protected static function getConnection($hint = null)
    {
        if (is_array(self::$connection)) {
            $count = count(self::$connection);
            
            if (! $hint)
                $hint = md5(rand());
                
                // convert the hint into an index
            $hint = abs(crc32(serialize($hint)) % $count);
            
            return self::$connection[$hint];
        } else {
            return self::$connection;
        }
    }

    /**
     * @desc get MongoDB object
     * @param string $hint            
     * @throws Exception
     * @author YangLong
     * @date 2015-08-13
     * @return MongoDB
     */
    protected static function getDatabase($hint = null)
    {
        $collection_name = self::$collectionName;
        $connection = self::getConnection($hint);
        
        if (self::$database == null)
            throw new Exception("BaseMongoRecord::database must be initialized to a proper database string");
        
        if ($connection == null)
            throw new Exception("BaseMongoRecord::connection must be initialized to a valid Mongo object");
        
        if (empty($connection->getConnections))
            $connection->connect();
        
        return $connection->selectDB(self::$database);
    }

    /**
     * @desc get MongoCollection object
     * @param string $hint            
     * @throws Exception
     * @author YangLong
     * @date 2015-08-13
     * @return MongoCollection
     */
    protected static function getCollection($hint = null)
    {
        $collection_name = self::$collectionName;
        $connection = self::getConnection($hint);
        
        if (self::$database == null)
            throw new Exception("BaseMongoRecord::database must be initialized to a proper database string");
        
        if ($connection == null)
            throw new Exception("BaseMongoRecord::connection must be initialized to a valid Mongo object");
        
        if (empty($connection->getConnections))
            $connection->connect();
        
        return $connection->selectCollection(self::$database, $collection_name);
    }

    /**
     * @desc initialize environment 
     */
    protected static function initializeEnvironment()
    {
        if (self::$environment && ! self::$environmentLoaded) {
            $environment = self::$environment;
            
            spl_autoload_register(function ($className) use($environment)
            {
                require_once ($environment . '/' . $className . '.php');
            });
            
            self::$environmentLoaded = true;
        }
    }
    
    /**
     * @desc find and rmove these last few days jobs
     * @param string $queueType
     * @param int $startTime
     * @param int $shopId
     * @author YangLong
     * @date 2015-08-14
     * @return mixed
     */
    public static function findMinAndRmove($queueType, $startTime, $shopId)
    {
        $collection = self::getCollection();
        
        $retval = 0;
        
        $cursor = $collection->find(array(
            'parameters.queue_type' => $queueType,
            'parameters.start_time' => array(
                '$gt' => $startTime
            ),
            'parameters.shop_id' => $shopId
        ), array(
            'parameters.start_time'
        ));
        
        foreach ($cursor as $doc) {
            if ($doc['parameters']['start_time'] < $retval || empty($retval)) {
                $retval = $doc['parameters']['start_time'];
            }
            $collection->remove(array(
                '_id' => $doc['_id']
            ));
        }
        
        return $retval;
    }
}
