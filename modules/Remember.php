<?php
class Remember {
	private $neurones, $objects, $mcount;

	public function __construct() {
		$this->neurones = [];
		$this->objects = [];
		$this->mcount = 0;

	}

	public function evolute($result) {
		$this->mcount = 0;
		$neuron = $result['element_left'] . '$' . $result['element_right'];
		$this->neurones[$neuron] = ($this->neurones[$neuron] ?? 0) + 1;
	}

	public function loadCacheEvolute() {
		$this->neurones = json_decode(file_get_contents('/../remembers/neurones.json'), true);
		$this->objects = json_decode(file_get_contents('/../remembers/objects.json'), true);
	}

	public function cacheEvolute() {
		file_put_contents('/../remembers/neurones.json', json_encode($this->neurones));
		file_put_contents('/../remembers/objects.json', json_encode($this->objects));
	}

	public function addObject($object) {
		if(!array_search($object, $this->objects)) {
			$this->objects[] = $object;
		}
	}

	public function getCount() {
		return ['objects' => count($this->objects), 'neurones' => count($this->neurones)];
	}

	public function find($object1) {
		$candidates = [];

		$elements1 = Splitter::getElements($object1, true);

		foreach ($this->objects as $object2) {
			$elements2 = Splitter::getElements($object2, true);
			$candidates[$object2] = $this->getCoeff($elements1, $elements2);
		}

		foreach ($candidates as &$value) {
			$value = $value[0]*($value[1]/$this->mcount); 
		}

		asort($candidates);
		$candidates = array_reverse($candidates, true);
		
		return array_shift(array_keys($candidates));
	}

	public function getCoeff($elements1, $elements2) {
		$summ = 0;
		$count = 0;
		foreach ($elements1 as $key1 => $value1) {
			foreach ($elements2 as $key2 => $value2) {
				$neuronCount =( $this->neurones[$key1 . '$' . $key2] ?? 0 );
				//echo $key1 . ' ' . $key2 . ' ' . $neuronCount . ' ' . $value1 . ' ' . $value2 . '<br>';
				$summ = $summ + ($value1 * $neuronCount * $value2);
				
				if($neuronCount) {
					$count++;
				}			
			}			
		}
		
		$this->mcount = max($this->mcount, $count);

		return [$summ, $count];
	}	
}

/*class Remember {
	private $db, $neuroTable, $objectTable;

	public function __construct() {
		$this->db = new MySQLiAdaptor(DB_NAME, DB_USER, DB_PASS, DB_BASE);
		$this->neuroTable = '`element_relation`';
		$this->objectTable = '`object`';
	}

	public function evolute($result) {
		$and = $this->fieldsForSql($result, 'AND');	
		$list = $this->fieldsForSql($result);

		if($id = ($this->db->query("SELECT id FROM {$this->neuroTable} WHERE $and")->row['id'] ?? 0)) {
			$this->db->query("UPDATE {$this->neuroTable} SET `neuron_effect` = `neuron_effect`+1 WHERE id = '$id'");
		} else {
			$this->db->query("INSERT INTO {$this->neuroTable} SET $list");
		}
	}

	private function fieldsForSql($result, $delimiter = ',') {
		$sqlArr = [];

		foreach ($result as $field => $value) {
			$sqlArr[] = "`$field` = '|$value|'";
		}

		return implode(' ' . $delimiter . ' ', $sqlArr);
	}
}*/