<?php
class Async {
	public const AS_DIR = __DIR__ . '/threads/';
	public const AS_EXT = '.th'; 
	public static function query($url) {   
	    $curl = curl_init(); 

	    $pid = self::generatePid();

	    curl_setopt($curl, CURLOPT_URL, APP_URL . $url . (strpos($url, '?')===false?'?':'&') . 'pid=' . $pid);
	    curl_setopt ($curl, CURLOPT_POST, TRUE);

	    curl_setopt($curl, CURLOPT_USERAGENT, 'api');

	    curl_setopt($curl, CURLOPT_TIMEOUT, 1); 
	    curl_setopt($curl, CURLOPT_HEADER, 0);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
	    curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
	    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
	    curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 10); 

	    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

	    curl_exec($curl);   

	    curl_close($curl);

    	return self::thread($pid);		
	}

	public static function isWorkedThreadByPid($pid) {
		return file_exists(self::AS_DIR . $pid . self::AS_EXT);
	}

	public static function thread($pid = null) {
		return	new class($pid, self::AS_DIR, self::AS_EXT) {
			private $isParrent, $pid, $AS_DIR, $AS_EXT;
			public const AS_TIMELIMIT = 360;
			public function __construct($pid, $AS_DIR, $AS_EXT) {
				$this->pid = $pid;

				$this->AS_DIR = $AS_DIR;
				$this->AS_EXT = $AS_EXT;

				if($pid) {
					$this->isParrent = true;
					
					if(!file_exists($this->AS_DIR . $this->pid . $this->AS_EXT)) {
						$this->createFile();
					}

					$success = false;
					for ($i=0; $i < self::AS_TIMELIMIT; $i++) { 
						sleep(1);
						if($this->getPositon()['is_worked'] ?? false) {
							$success = true;	
							break;
						}
					}
					if(!$success) {
						$this->die();
						throw new Exception("Thread " . $this->pid . " not started!", 1);
					}

				} else {
					if($this->pid = ($_GET['pid'] ?? '')) {
						$this->isParrent = false; sleep(1);
						
						$success = false;
						for ($i=0; $i < self::AS_TIMELIMIT; $i++) { 
							sleep(1);
							if($this->getPositon()) {
								$success = true;	
								$data['is_worked'] = true;
								$this->setPosition($data);
								break;
							}
						}
						if(!$success) {
							$this->die();
							throw new Exception("Thread " . $this->pid . " not started!", 1);
						}
					}
					else {
						$this->die();
						throw new Exception("Thread " . $this->pid . " not found!", 1);
					}
				}				
			}
			public function getPid() {
				return $this->pid;
			}

			public function send($callbackdata) {
				if($this->isParrent) {
					$data['send'] = ['data' => $callbackdata, 'set' => true];
					$data['fetch']['set'] = false;
					$this->setPosition($data);

					$success = false;
					for ($i=0; $i < self::AS_TIMELIMIT; $i++) { 
						sleep(1);
						$data = $this->getPositon();
						if($data['fetch']['set'] ?? false) {
							$success = true;	
							$data['fetch']['set'] = false;
							$this->setPosition($data);
							break;
						}
					}
					if(!$success) {
						$this->die();
						throw new Exception("Thread " . $this->pid . " not response!", 1);
					}

					return $data['fetch']['data'];
				} else {
					while (true) { 
						$die = false; 
						sleep(1); 
						$data = $this->getPositon();
						if(!($data['is_worked'] ?? false)) {
							@unlink($this->AS_DIR . $this->pid . $this->AS_EXT);
							throw new Exception("Thread " . $this->pid . " is die!", 1);
						} else if($data['send']['set'] ?? false) {	 
							$data['fetch'] = ['data' => $callbackdata($data['send']['data']), 'set' => true];
							$data['send']['set'] = false;
							$this->setPosition($data);
						}
					}
				}
			}

			public function die() {
				/*$data['is_worked'] = false;
				$this->setPosition($data);*/
				@unlink($this->AS_DIR . $this->pid . $this->AS_EXT);
			}

			private function createFile() {
				$data = [
						'send' => [
									'data' => [],
									'set' => false,
									],
						'fetch' => [
									'data' => [],
									'set' => false,
									],
						'is_worked' => false
					
				];

				$this->setPosition($data);
			}

			private function setPosition($data) {
				if(!is_dir($this->AS_DIR)) {
					mkdir($this->AS_DIR);
				}

				if(!file_exists($this->AS_DIR . $this->pid . $this->AS_EXT)) {
					file_put_contents($this->AS_DIR . $this->pid . $this->AS_EXT, json_encode([]));
				}

				$file = json_decode(file_get_contents($this->AS_DIR . $this->pid . $this->AS_EXT), true);

				foreach ($data as $key => $value) {
					$file[$key] = $value;
				}

				
				file_put_contents($this->AS_DIR . $this->pid . $this->AS_EXT, json_encode($file));
			}

			private function getPositon() {
				if(!is_dir($this->AS_DIR)) {
					mkdir($this->AS_DIR);
				}

				if(!file_exists($this->AS_DIR . $this->pid . $this->AS_EXT)) {
					file_put_contents($this->AS_DIR . $this->pid . $this->AS_EXT, json_encode([]));
				}

				return json_decode(file_get_contents($this->AS_DIR . $this->pid . $this->AS_EXT), true);	
			}
		};
	}

	private static function generatePid() {
		$scan = is_dir(self::AS_DIR) ? array_diff(scandir(self::AS_DIR), ['.', '..']) : [];
		
		$pid = 0;
		do {
			$pid = rand(1000, 9999);
		} while(array_search($pid . self::AS_EXT, $scan));

		return $pid;
	}
}