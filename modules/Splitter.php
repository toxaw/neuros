<?php
class Splitter {
	public static function getElements($textIn, $rel = false) {
		if(! ($textIn = self::formatter($textIn)) ) return false;
		
		$elements = [];

		$split = preg_split('//u', $textIn, -1, PREG_SPLIT_NO_EMPTY);
		$length = count($split);

		for ($i=0; $i <= $length; $i++) { 
			for ($j=0; $j < $i; $j++) { 
				$textSlice = implode('', array_slice($split, $j, $length - ( $i - 1 ) ));

				$elements[$textSlice] = ( $length - ( $i - 1 ) ) / $length;
			}
		}

		return  $rel ? $elements : array_keys($elements);
	}

	public static function generateTwains($textIn1, $textIn2) {
		$textIn1 = self::getElements($textIn1);
		$textIn2 = self::getElements($textIn2);
		
		if(!$textIn1 || !$textIn2) return false;

		return new class($textIn1, $textIn2) {
			private $textIn1, $textIn2, $i, $j, $li, $lj;

			public function __construct($textIn1, $textIn2) {
				$this->textIn1 = $textIn1;
				$this->textIn2 = $textIn2;

				$this->i = 0;
				$this->j = 0;

				$this->li = count($textIn1);
				$this->lj = count($textIn2);
			}

			public function fetch() {
				for (; $this->i < $this->li ; ) {
					for (; $this->j < $this->lj ; ) {
						$this->j++; 

						return ['element_left' => $this->textIn1[$this->i], 'element_right' => $this->textIn2[$this->j-1]];
					}
					$this->i++;
					$this->j = 0;
				}
			}
		};

		/*$twains = [];

		foreach ($textIn1 as $text1) {
			foreach ($textIn2 as $text2) {
				$twains[] = [$text1, $text2];
			}
		}

		return count($twains);*/
	}

	private static function formatter($text) {
		return mb_strtolower(trim(preg_replace(['/[^а-яa-z0-9\s]+/ui','/\s{2,}/'], ['', ' '], $text)));
	}
}