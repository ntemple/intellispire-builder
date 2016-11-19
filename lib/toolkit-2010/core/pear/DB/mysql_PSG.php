<?php
require_once "DB/mysql.php";

class DB_mysql_PSG extends DB_mysql
{
    function nextId($name, $null=false) 
    {
      /*
      ** Note that REPLACE query below correctly creates a new sequence
      ** when needed
      */
        $result = $this->getOne("SELECT GET_LOCK('sequence_lock',10)");
        if (DB::isError($result)) {
            return $this->raiseError($result);
        }
        if ($result == 0) {
            // Failed to get the lock, bail with a DB_ERROR_NOT_LOCKED error
            return $this->mysqlRaiseError(DB_ERROR_NOT_LOCKED);
        }

        $id = $this->getOne("SELECT id FROM sequence WHERE name = '$name'") + 1;
        if (DB::isError($id)) {
            return $this->raiseError($id);
        }

        $result = $this->query("REPLACE INTO sequence VALUES ('$name', '$id')");
        if (!$result) {
            return $this->raiseError($result);
        }

        // Release the lock
        $result = $this->getOne("SELECT RELEASE_LOCK('sequence_lock')");
        if (DB::isError($result)) {
            return $this->raiseError($result);
        }
      return $id;
    }
}
?>
