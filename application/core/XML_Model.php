<?php

/**
 * XML-persisted collection.
 * 
 * @author		Kabir Cahill
 * ------------------------------------------------------------------------
 */
class XML_Model extends Memory_Model
{
//---------------------------------------------------------------------------
//  Housekeeping methods
//---------------------------------------------------------------------------

	/**
	 * Constructor.
	 * @param string $origin Filename of the XML file
	 * @param string $keyfield  Name of the primary key field
	 * @param string $entity	Entity name meaningful to the persistence
	 */
	function __construct($origin = null, $keyfield = 'id', $entity = null)
	{
		parent::__construct();

		// guess at persistent name if not specified
		if ($origin == null)
			$this->_origin = get_class($this);
		else
			$this->_origin = $origin;

		// remember the other constructor fields
		$this->_keyfield = $keyfield;
		$this->_entity = $entity;

		// start with an empty collection
		$this->_data = array(); // an array of objects
		$this->fields = array(); // an array of strings
		// and populate the collection
		$this->load();
	}

	/**
	 * Load the collection state appropriately, depending on persistence choice.
	 * OVER-RIDE THIS METHOD in persistence choice implementations
	 */
	protected function load()
    {
        //---------------------
        $xml = simplexml_load_file($this->_origin) or die("Error: Cannot create object");

        //echo $xml->taskitem->children() . "<br>";
        foreach($xml->taskitem->children() as $key => $value) {
            $this->_fields[] = (string)$key;
            //echo $key . "<br>";
        }

        foreach($xml->children() as $task) {
            $record = new stdClass();

            $record->{"id"} = (int) $task->id;
            $record->{"task"} = (string) $task->task;
            $record->{"priority"} = (int) $task->priority;
            $record->{"size"} = (int) $task->size;
            $record->{"group"} = (int) $task->group;
            $record->{"deadline"} = (int) $task->deadline;
            $record->{"status"} = (int) $task->status;
            $record->{"flag"} = (int) $task->flag;

            $key = $record->{$this->_keyfield};
            $this->_data[$key] = $record;
        }

        // --------------------
		// rebuild the keys table
		$this->reindex();
	}

	/**
	 * Store the collection state appropriately, depending on persistence choice.
	 * OVER-RIDE THIS METHOD in persistence choice implementations
	 */
	protected function store()
	{
		// rebuild the keys table
		$this->reindex();
    
       // $xml = simplexml_load_file($this->_origin) or die("Error: Cannot create object");
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        
        $root = $dom->appendChild($dom->createElement('todo'));
        
        foreach($this->_data as $key => $record){
            $taskItem = $root->appendChild($dom->createElement('taskitem'));
            
            $id = $taskItem->appendChild($dom->createElement('id'));
            $id->appendChild($dom->createTextNode($record->{"id"}));
            
            $task = $taskItem->appendChild($dom->createElement('task'));
            $task->appendChild($dom->createTextNode($record->{"task"}));
            
            $priority = $taskItem->appendChild($dom->createElement('priority'));
            $priority->appendChild($dom->createTextNode($record->{"priority"}));
            
            $size = $taskItem->appendChild($dom->createElement('size'));
            $size->appendChild($dom->createTextNode($record->{"size"}));
            
            $group = $taskItem->appendChild($dom->createElement('group'));
            $group->appendChild($dom->createTextNode($record->{"group"}));
            
            $deadline = $taskItem->appendChild($dom->createElement('deadline'));
            $deadline->appendChild($dom->createTextNode($record->{"deadline"}));
            
            $status = $taskItem->appendChild($dom->createElement('status'));
            $status->appendChild($dom->createTextNode($record->{"status"}));
            
            $flag = $taskItem->appendChild($dom->createElement('flag'));
            $flag->appendChild($dom->createTextNode($record->{"flag"}));
        }
        
        $dom->save($this->_origin);
		/**
		if (($handle = fopen($this->_origin, "w")) !== FALSE)
		{
			fputcsv($handle, $this->_fields);
			foreach ($this->_data as $key => $record)
				fputcsv($handle, array_values((array) $record));
			fclose($handle);
		}
		**/
	}

}
