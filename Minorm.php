<?

namespace Minorm;


/**
 * Примитивное подобие ORM :)
 */
class Minorm {

  protected $link;

  public function __construct() {

    // mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $this->link = mysqli_connect("localhost", "mysql", "mysql", "test");
    mysqli_set_charset($this->link, 'utf8');

    if (!$this->link) {
      echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
      echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
      echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
      exit;
    }
    // echo "Соединение с MySQL установлено!" . PHP_EOL;
  }

  /**
   * Возвращает удобочитаемую ошибку для пользователя и записывает лог в таблицу
   *
   * @param string $error   log ошибки с подробной информацией
   * @return string/array   текст ошибки
   */
  function showError($error) {
    $data = 'Error database!';

    $query = "INSERT INTO `logs` VALUES (NULL, '".$_SERVER['REMOTE_ADDR']."', '". mysqli_real_escape_string( $this->link, $error ) ."')";
    $run = mysqli_query($this->link, $query) or die("Could not execute query. ".mysql_error($this->link));

    $pos = strpos(mysqli_real_escape_string( $this->link, $error ), "Duplicate entry");
    if ($pos !== false) {
      $data = array();
      $data["response"] = "error";
      $data["error"] = "duplicate.subdepartment";
      return json_encode($data);
      exit();
    }
    // ...

    echo $data;
  }


  /**
   * Функция выполняет произвольный sql запрос
   *
   * @param string $query   sql запрос
   * @param bool $debug     режим дебагинга
   * @return void/array     в случае SELECT массив, а иначе void
   */
  public function query($query, $debug=false, $row=true) {
    if ($debug) {
      die( $query );
    }

    $bt = debug_backtrace();
    $caller = array_shift($bt);

    $arr = array();
    $result = mysqli_query($this->link, $query) or $this->showError( $caller['file'].' '.$caller['line'].' => '.mysql_error($this->link) );
    if ($row) {
      while ($r = mysqli_fetch_assoc($result)) {
        array_push($arr, $r);
      }
      return $arr;
    }    
  }


  /**
   * Функция возвращает строку из указанной таблицы по id
   *
   * @param string $table     название таблицы в DB
   * @param number $id        идентификатор записи 
   * @return array строка
   */
  public function get($table, $id) {
    $bt = debug_backtrace();
    $caller = array_shift($bt);

    $query = "SELECT * FROM $table WHERE id=$id";
    $result = mysqli_query($this->link, $query) or $this->showError($caller['file'].' '.$caller['line'].' => '.mysqli_error($this->link)) ;
    $row = mysqli_fetch_assoc($result);

    return $row;
  }


  /**
   * Функция вставляет запись в таблицу
   *
   * @param string $table     название таблицы в DB
   * @param array $values     массив значений
   * @return int/void         id только что добавленной записи, или текст сообщения ошибки
   */
  public function insert($table, $values) {
    $bt = debug_backtrace();
    $caller = array_shift($bt);

    $f = null; $v = null; $r = true;

    for ($i=0; $i<count($values); $i++) {
      $d = ( $i != count($values)-1 ) ? ', ' : null;

      // значения
      $values[$i] = ( is_null($values[$i]) ) ? "NULL" : "'$values[$i]'";
      $v .= $values[$i] . $d;
    }

    $query = "INSERT INTO `$table` $f VALUES ($v)";
    // die($query);
    mysqli_query($this->link, $query) or $this->showError( $caller['file'].' '.$caller['line'].' => '.mysql_error($this->link) );

    return mysqli_insert_id($this->link);

    /*$query = "SELECT @@IDENTITY AS id";
    $run = mysqli_query($this->link, $query) or $this->showError( $caller['file'].' '.$caller['line'].' => '.mysql_error($this->link) );
    $row = mysqli_fetch_assoc($run);*/
  }


  /**
   * Функция обновляет запись в таблице
   *
   * @param string $table     название таблицы в DB
   * @param array $arr        ассоциативный массив, где ключ - это поле в DB
   * @param int $id           уникальный идентификатор записи
   * @return bool/void        true или текст сообщения ошибки
   */
  public function update($table, $arr, $id) {
    $bt = debug_backtrace();
    $caller = array_shift($bt);

    $str = null; $r = true; $v = NULL;

    $numItems = count($arr);
    $i = 0;
    foreach ($arr as $key=>$value) {
      $d = (++$i === $numItems) ? null : ", ";
      $v = ( $value == NULL ) ? 'NULL' : "'$value'";

      $str .= "$key=$v" . $d;
    } 

    $query = "UPDATE $table SET $str WHERE id IN ($id)";
    $run = mysqli_query($this->link, $query) or $this->showError( $caller['file'].' '.$caller['line'].' => '.mysql_error($this->link) );

    return $r;
  }


  /**
   * Функция удаляет строку из указанной таблицы
   *
   * @param string $table       название таблицы в DB
   * @param number/array $id    идентификатор записи 
   * @return bool/void          true или текст сообщения ошибки
   */
  public function delete($table, $id) {
    $bt = debug_backtrace();
    $caller = array_shift($bt);

    $r = true;

    $query = "DELETE FROM $table WHERE id IN ($id)";
    $run = mysqli_query($this->link, $query) or $this->showError( $caller['file'].' '.$caller['line'].' => '.mysql_error($this->link) );

    return $r;
  }
  
}