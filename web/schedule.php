<?php

function lesson_sorted($str)
{
$lesson = substr($str, 0, strpos($str, ',' ));
$position = strlen($str)-strlen($lesson)-1;	
$str = substr($str, -$position);	
$teacher = substr($str, 0, strpos($str, ',' ));
$position = strlen($str)-strlen($lesson)-3;	
$str = substr($str, -$position);	
$classroom = $str;
$sort .= "Предмет: ";
$sort .= $lesson;
$sort .= "\n";
$sort .= "Преподаватель: ";
$sort .= $teacher;
$sort .= "\n";
$sort .= "Аудитория: ";
$sort .= $classroom;
//$sort .= 
	return $sort;
}
function schedule($group, $day)
{
	$string = '';
	//$day = 'ПН';
	//$group = 3;
	
	$day_arr = array('ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ');
	$indexOfDayArr = array_search($day, $day_arr);  
	$html = file_get_contents('http://fkn.univer.omsk.su/academics/Schedule/schedule3_1.htm');
	$saw = new nokogiri($html);
	$table = $saw->get('table'); 
	$tr = $table->get('tr');
	
	$str = 0;		//строка
	$t = 1; 	//время
	
//	var_dump( $tr->toArray()[$str]['td'][$group]["#text"] );
//	echo count( $tr->toArray()[$str]['td'][$group]["#text"] );
	/*__________________________________________*/ // Ищем нужный день (индекс строки, где нужный день)
	while (cell($tr, $str, 0) !== $day)
	{$str++;}
	
	/*__________________________________________*/ //Парсим группу, выводим группу и день
	$egroup = cell($tr, 0, $group);
	$string.= $egroup;
	$string.= "\n";
	$string.= $day;
	$string.= "\n";
	/*__________________________________________*/

	$time = cell($tr, $str, $t);	//парсим время
	$span = colspan($tr, $str, $group);
	$lesson = cell($tr, $str, $group-$span);	//парсим пару
	if($lesson !== Null)	//если  пара есть, выводим время и пару
	{
		$lesson = lesson_sorted($lesson);
		$string .= $time;
		$string .= "\n";
		$string .= $lesson;
		$string .= "\n";
	}

	$group--;	//сдвиг влево из-за того, что был день недели
	$t--;
	$str++;		//следующая строка
	
	while (cell($tr, $str, 0) !== $day_arr[$indexOfDayArr+1])
	{
		$time = cell($tr, $str, $t);
		$span = colspan($tr, $str, $group);
		$lesson = cell($tr, $str, $group-$span);

		if($lesson !== Null)
		{
		$lesson = lesson_sorted($lesson);
		$string .= $time;
		$string .= "\n";
		$string .= $lesson;
		$string .= "\n";
		}
		$str++;
	} 
	if($string === $egroup . "\n" . $day . "\n")
	{$string .= "Пар нет &#128526; &#128526; &#128526;";}
	return $string;
	
}


function cell($tab, $x, $y)
{
	if(is_array($tab->toArray()[$x]['td'][$y]["#text"]))
	{
		$content_count = count($tab->toArray()[$x]['td'][$y]["#text"]);
	}
	else
	{
		$content_count = 0;
	}
	if ($content_count>1)
	{
	for ($i = 0; $i < $content_count; $i++)
	{
		$str .= $tab->toArray()[$x]['td'][$y]["#text"][$i]; //+ '\n';
		//$str .= '\n';
	}
	return $str;
	}
	return $tab->toArray()[$x]['td'][$y]["#text"][0];
}

function colspan($tab, $x, $y)
{
	$span = 0;
//	$size = count($tab->toArray()[$x]['td']);
	for ($i = 0; $i < $y; $i++)
	{
		if ($tab->toArray()[$x]['td'][$i]["colspan"][0] > 0)
		{
			$span++;
		}
	}
	return $span;
}


?>