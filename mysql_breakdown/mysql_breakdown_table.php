<?php

/**
 * Description of mysql_breakdown_table
 *
 * @author Rami Dabain
 * email : brains@okitoo.net
 * skype : ronan_dejhero
 */
class mysql_breakdown_table {

    public $fields = array();
    public $table_name = array();

    /**
     * loads the stdObject from Mysql_fetch_object and parses it
     * @param stdObj $fields
     */
    public function __construct($table_name, $fields) {

        $this->table_name = $table_name;
        foreach ($fields as $fieldData) {
            $field = new stdClass();
            $field->field = '';

            $field->type_length = '';
            $field->null = '';
            $field->key = '';
            $field->default = '';
            $field->extra = '';
            foreach ($fieldData as $key => $val) {
                $field->{strtolower($key)} = $val;
            }//
            $brackPos = strpos($field->type, '(');
            if ($brackPos > 0) {
                $field->type_length = substr($field->type, $brackPos + 1, -1);
                $field->type = substr($field->type, 0, $brackPos);
            }
            $this->fields[] = $field;
        }
    }
    
    public function buildQuery($encloseColumnNames = ''){
        $query = 'CREATE TABLE '.$this->table_name.' (';
        foreach ($this->fields as $field){
            $query.= $encloseColumnNames.$field->field.$encloseColumnNames.' ';
            $query.= $field->type.' ';
            if ($field->type_length !=''){
                 $query.='('.$field->type_length.')';
            }
            $query.= ',';
        }
        $query = substr($query,0,-1).')';
        return $query;
    }
    
    public function convert($from = 'mysql', $to = 'postgres'){
        require_once dirname(__FILE__).'/type_maps/'.$from.'_'.$to.'.php';
        $mapType = 'map_'.$from.'_'.$to;
        $map = new $mapType();
        $newFields = array();

        foreach ($this->fields as $key=>$fieldData) {
          $newFields[$key] = $fieldData;
          $oldFDT = strtoupper($fieldData->type);
          $newFDT = $map->type[$oldFDT];
          $newFields[$key]->type = $newFDT;
          if (in_array($newFDT, $map->remove_length)){
              $newFields[$key]->type_length = '';
          }
        }
        $this->fields = $newFields;
    }

}
