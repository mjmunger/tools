<?php

/**
 * Abstraction of the employees table
 * Generated by Abstractor from hphio\util
 */

namespace Test\Employee;


class ExpectedEmployeesClass
{

    /* <generated_2346ad27d7568ba9896f1b7da6b5991251debdf2> */

    /* <database fields> */

    public $emp_id       = null;
    public $birth_date   = null;
    public $first_name   = null;
    public $last_name    = null;
    public $gender       = null;
    public $hire_date    = null;
    public $last_updated = null;
    public $date_created = null;

    /* </database fields> */

    /**
     * Returns an associative array of values for this class.
     * @return array
     */

    public function getMyValues() : array {
        return [ "emp_id"       => $this->emp_id
               , "birth_date"   => $this->birth_date
               , "first_name"   => $this->first_name
               , "last_name"    => $this->last_name
               , "gender"       => $this->gender
               , "hire_date"    => $this->hire_date
               , "last_updated" => $this->last_updated
               , "date_created" => $this->date_created
               ];
    }

    public function insert() {
        $sql = " INSERT INTO `employees`
                (  `birth_date`
                , `first_name`
                , `last_name`
                , `gender`
                , `hire_date`
                , `last_updated`
                , `date_created`
                )
                VALUES
                ( :birth_date
                , :first_name
                , :last_name
                , :gender
                , :hire_date
                , :last_updated
                , :date_created
                )";
        $values = $this->getMyValues();
        unset($values['emp_id']);

        $this->prepareExecute($sql, $values);

        $this->emp_id = $this->pdo->lastinsertid();
        return $this->emp_id;

    }

    public function update() {
        $sql = "UPDATE `employees`
                SET
                `birth_date` = :birth_date,
                `first_name` = :first_name,
                `last_name` = :last_name,
                `gender` = :gender,
                `hire_date` = :hire_date,
                WHERE `emp_id` = :emp_id
                LIMIT 1";

        $values = $this->getMyValues();
        unset($values['last_updated']);
        unset($values['date_created']);
        $this->prepareExecute($sql, $values);
    }

    /* </generated_2346ad27d7568ba9896f1b7da6b5991251debdf2> */
}
