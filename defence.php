<?php
/**
 * Расчёт Hit Points (HP) по шагам.
 * Версия 1.0.
 * В данной версии вводятся из консоли входные данные :
 *  Endurance - характеристика выносливости;
 *  Damage - интегральный урон;
 *  Environmental influence - интегральный фактор воздействия среды;
 *  Regeneration factor - интегральный фактор восстановления.
 * В данной версии выводятся в консоль выходные данные :
 *  HP max - максимальное значение HP;
 *  HP current - текущее значение значение HP;
 *  Health of... - текущее состояние частей тела.
 */

/**
 * Массив HP с дополнительными параметрами:
 *  hp_max - максимальное значение HP;
 *  hp_curr - текущее значение HP;
 *  regeneration - фактор восстановления;
 *  health - массив состояния частей тела;
 *  def - массив факторов защиты;
 *  env - массив факторов воздействия среды;
 *  dam - массив урона;
 *      ключи:
 *          head - голова; corp - туловище; hl - л.рука, hr - пр.рука, ll - л.нога, lr - пр.нога;
 * @var array $hp_arr
 */
$hp_arr = [
	'hp_max' => 0,
	'hp_curr' => 0,
	'regeneration' => 0,
	'health' => ['head' => 0,'corp' => 0,'hl' => 0,'hr' => 0,'ll' =>0,'lr' => 0],
	'def' => ['head' => 0,'corp' => 0,'hl' => 0,'hr' => 0,'ll' => 0,'lr' => 0],
	'env' => ['head' => 0,'corp' => 0,'hl' => 0,'hr' => 0,'ll' => 0,'lr' => 0],
	'dam' => ['head' => 0,'corp' => 0,'hl' => 0,'hr' => 0,'ll' => 0,'lr' => 0]
];

/**
 * Функция распределения урона по частям тела:
 * @param $dam_arr - массив распределения урона по частям тела;
 * @param $dam - интегральный урон;
 * @return array
 */
function rand_damage_alloc($dam_arr, $dam){
    $max = $dam * 6;
	foreach($dam_arr as $key => $val){
		$dam_arr[$key] = rand(0, 100);
	}
	$s = array_sum($dam_arr);
	foreach($dam_arr as $key => $val){
		$dam_arr[$key] = round($val / $s * $max);
	}
	return $dam_arr;	
}

/**
 * Функция задания начального значения Hit Points:
 * @param $hp_arr - массив Hit Points;
 * @param $Endurance - характеристика выносливости;
 * @return array
 */
function hp_start($hp_arr,$Endurance){
	$hp_arr['hp_max'] = 90 + $Endurance * 20;
	$hp_arr['hp_curr'] = $hp_arr['hp_max'];
	foreach($hp_arr['health'] as $key => $val){
		$hp_arr['health'][$key] = $hp_arr['hp_curr'];
	}
	return $hp_arr;
}

/**
 * Функция изменения Hit Points за один ход:
 * @param $hp_arr - массив Hit Points;
 * @param int $dam - интегральный урон;
 * @param int $env - интегральный фактор воздействия среды;
 * @param int $dam_flag - флаг распределения урона;
 * @param int $env_flag - флаг распределения фактора воздействия среды;
 * @return array
 */
function health_main($hp_arr, $dam, $env = 0, $dam_flag = 0, $env_flag = 0){
	if (!$dam_flag){
		$hp_arr['dam'] = rand_damage_alloc($hp_arr['dam'], $dam);
	}
	if (!$env_flag){
		foreach($hp_arr['env'] as $key => $val){
			$hp_arr['env'][$key] = $env;
		}
	}
	$total_loss = 0;
	foreach($hp_arr['health'] as $key => $val){
		$loss = $hp_arr['dam'][$key] - $hp_arr['def'][$key];
		$total_loss += $loss;
		$hp_arr['health'][$key] = $hp_arr['health'][$key] - $loss - $hp_arr['env'][$key] + $hp_arr['regeneration'];
		if ($hp_arr['health'][$key] < 0){
            $hp_arr['health'][$key] = 0;
        }
		if ($hp_arr['health'][$key] > $hp_arr['hp_max']){
            $hp_arr['health'][$key] = $hp_arr['hp_max'];
        }
	}	
	$hp_arr['hp_curr'] -= (int)($total_loss / 6);
	$hp_arr['hp_curr'] += $hp_arr['regeneration'];
	$hp_arr['hp_curr'] -= $env;
	if($hp_arr['hp_curr'] > $hp_arr['hp_max']){
        $hp_arr['hp_curr'] = $hp_arr['hp_max'];
    }
	if($hp_arr['hp_curr'] < 0){
        $hp_arr['hp_curr'] = 0;
    }
	return $hp_arr;
}
//==========================================================================================
$resource  = fopen ("php://stdin","r");
echo PHP_EOL;
echo "Enter Endurance : ";
$Endurance = fgets($resource);
$hp_arr = hp_start($hp_arr,$Endurance);
while(true){
	echo "Enter Damage : ";
	$dam = fgets($resource);
	echo "Enter Environmental influence : ";
	$env = fgets($resource);
	echo "Enter Regeneration factor: ";
	$hp_arr['regeneration'] = fgets($resource);
	echo PHP_EOL;
	$hp_arr = health_main($hp_arr,$dam,$env);
	echo "HP max = ".$hp_arr['hp_max'].PHP_EOL;
	echo "HP current = ".$hp_arr['hp_curr'].PHP_EOL;
	foreach($hp_arr['health'] as $key => $val){
		echo ($val)? "Health of " . $key . " = " . $val . PHP_EOL : $key . " dead " . PHP_EOL;
	}
	if (!$hp_arr['hp_curr']){
		echo "Soldier is dead. Game over." . PHP_EOL;
		break;
	}else{echo PHP_EOL;}
}
?>