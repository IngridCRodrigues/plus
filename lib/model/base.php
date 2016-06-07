<?php
    //Important: To use that class and your properties correctly,
    //you will go need to name your database tables
    //with the same name of their correspondent model concatenated to 's'..
    // Sample:
    // - Model: User
    // - Correspondent database table: users

    namespace Model;

    use \PDO;

    require_once('../helpers/session.php');
    require_once('../helpers/connection.php');

    abstract class Base {

        /**
         * @var string [Sets the sufix to complete database views name]
         */
        const DB_VIEW = '_vw';

        /**
         * Field list wich defines database fillables.
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [];

        /**
         * Field list wich entities have an n2n relatioship.
         *
         * @var array
         */
        protected $relatedEntities = [];

        /**
         * Generic dynamic construct for models
         * @param array $attributes [field list for search on database table/view]
         */
        function __construct($attributes) {
            $this->id = isset($attributes['id']) ? $attributes['id'] : null;
            (!empty($attributes['password'])) ? static::encryptPass($attributes['password']) : true;
            foreach ($attributes as $key => $value) {
                if(in_array($key, $this->fillable)) {
                    $this->$key = $value;
                }
            }
            if($relatedEntities) {
                foreach ($attributes as $key => $value) {
                    if(in_array($key, $this->relatedEntities)) {
                        $this->$key = $value;
                    }
                }
            }
            unset($this->fillable);
        }

        /**
         * MySQL database connection
         *
         * @return object \PDO
         */
        protected static function connect() {
            return Connection::start();
        }

        /**
         * Encryptes an inserted Password
         *
         * @param  string $password
         * @return void
         */
        private function encryptPass(&$password) {
            array_push($this->fillable, 'password');
            $password = md5($password);
        }

        /**
         * Formats entity's name to search database tables
         *
         * @param  boolean $isView [TRUE(default) to search on an view or FALSE to search on an table.]
         * @return string
         */
        protected static function entity($isView = true) {
            $isView ? $suffix = self::DB_VIEW : $suffix = NULL;
            return lcfirst(get_called_class()).'s'.$suffix;
        }

        /**
         * Verify credentials for system access
         *
         * @return object [Informations about the correspondent user.]
         */
        public function login() {
            $connect = self::connect();
            $stm = $connect->prepare('SELECT * FROM `'.self::entity(false).'` WHERE email = :email AND password = :password LIMIT 1');
            $stm->BindValue(':email',$this->email, PDO::PARAM_STR);
            $stm->BindValue(':password',$this->password, PDO::PARAM_STR);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ);
        }

        /**
         * Querys all lines from the entity's table view
         *
         * @param  string $order [Sets the ORDER BY param and sorting to SQL query.]
         * @return object
         */
        public static function all($order = 'id ASC', $limit = null) {
            if (!is_null($limit)) $limit = ' LIMIT '.$limit;
            $connect = self::connect();
            $stm = $connect->query('SELECT * FROM `'.self::entity().'` ORDER BY '.$order.$limit);
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }

        /**
         * Querys one line from the entity's table view
         *
         * @param  integer $id [primary key]
         * @return object
         */
        public static function one($id = null) {
            $connect = self::connect();
            if (!$id) $id = $_GET['id'];
            $stm = $connect->prepare('SELECT * FROM `'.self::entity().'` WHERE id = '.$id.' LIMIT 1');
            $stm->bindParam(":id", $id, PDO::PARAM_INT);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ);
        }

        /**
         * Inserts one line in the entity's table
         *
         * @return boolean [TRUE on success or FALSE on failure.]
         */
        public function insert() {
            protected $response = [];
            $connect = self::connect();
            $connect->beginTransaction();

            try {

                $attr = (array)$this;
                $stm = $connect->prepare('INSERT INTO `'.self::entity(false).'`('.implode(',  ', array_keys($attr)).', createdAt, updatedAt) VALUES (:'.implode(', :', array_keys($attr)).', NOW(), NOW())');
                $response = $stm->execute($attr);

                if ($this->relatedEntities){
                    $lastId = (int)$connect->lastInsertedId();
                    array_push($response, self->insertReletedsEntity($lastId, $connect));
                }

                $connect->commit();
                return $response;
            } catch (Exception $e) {
                $connect->rollback();
                return false;
            }
        }

        /**
         * Inserts on line in every table relation
         * fazê no padrao masterRslave
         *
         * @return boolean [TRUE on success or FALSE on failure.]
         */
        public function insertReletedsEntity($lastId, PDO $connect) {
            private $response = [];

            foreach ($relatedEntities as $entity) {
                if(!empty($this->$entity)) {
                    $stm = $connect->prepare('INSERT INTO `'.self::entity(false).'R'.$entity.'`('.self::entity(false).', '.$entity.', createdAt, updatedAt) VALUES (:'.self::entity(false).', :'.$entity.', NOW(), NOW())');
                }
                $response[$entity] = $stm->execute();
            }

            return $response;
        }

        /**
         * Update one line from the entity's table
         *
         * @return boolean [TRUE on success or FALSE on failure.]
         */
        public function update() {
            $connect = self::connect();
            $attr = (array)$this;
            foreach ($attr as $key => $value) {
                $sets .= $key . ' = :' . $key . ', ';
            }
            $stm = $connect->prepare('UPDATE `'.self::entity(false).'` SET '.$sets.' updatedAt = NOW() WHERE id = '.$this->id);
            return $stm->execute($attr);
        }

        /**
         * Remove one line from the entity's table
         *
         * @return boolean [TRUE on success or FALSE on failure.]
         */
        public function remove() {
            $connect = self::connect();
            $stm = $connect->prepare('DELETE FROM `'.self::entity(false).'` WHERE id = '.$this->id);
            return $stm->execute();
        }

        /**
         * Execute any MySQL Query weel-formed
         *
         * @param  string $sql [an MySQL Query weel-formed]
         * @param  array  $attr   [description]
         *
         * @return object's array
         */
        public function query($sql, $attr = array()) {
            $connect = self::connect();
            $stm = $connect->prepare($sql);
            $stm->execute($attr);
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
    }
