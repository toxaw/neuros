<?php
set_time_limit(3600);
require 'requires/prolog.php';
$block = file_get_contents('blocks/ru.conversations.txt');
//$in = 'Ща пожру, тебя доделаю и тикать';
//echo '<pre>';

$block = explode("\n", $block);

$firstSecond = [];
$before = '';
foreach ($block as $key => $value) {
	if(!(!$before || !$value)) {
		$firstSecond[] = [$before, $value];
	}
	$before = $value;
}
//die(print_r($firstSecond));
//--neuro
//$textIn1 = 'А вот знаешь, я тебя сейчас буду делить на                         ЭлеМентарные частицы)';
//$textIn2 = 'Отлично! Потом мы увидим, как нейросеть обучилась!';

$evolutor = new Remember;

//$textIn1 = 'Привет друг!';
//$textIn2 = 'Здарова братан!';

$twains = Splitter::generateTwains($textIn1, $textIn2);
//die(print_r(Splitter::getElements($textIn1)));
/*
for ($i=0; $i < 5; $i++) { 
	print_r($twains->fetch());
}
var_dump($twains);
*/
//$t = time();
/*while ($res = $twains->fetch()) {
	$evolutor->evolute($res);
}*/ //die('['.count($firstSecond).']');
//$i = 0;
foreach (array_slice($firstSecond, 80000, 500) as $value) {
	
	$twains = Splitter::generateTwains($value[0], $value[1]);
	while ($res = $twains->fetch()) {
		$evolutor->evolute($res);
	}
	if($twains)
		$evolutor->addObject($value[1]);
//	$i++;if($i==200) break;
}

//$evolutor->cacheEvolute();

//print_r($evolutor->getCount($in));

$thread = Async::thread();

$thread->send(function($data) use($evolutor) {
	if($data['message'] ?? false) {
		return $evolutor->find($data['message']);
	}
});
//echo $evolutor->find($in);
//die('['.(time()-$t).']');
