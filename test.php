<?php

$field = 'goods.*.name.*.id';
// goods.0.name
// goods.1.name

$arr = [
    'goods' => [
        1 => ['name' => [
            ['id' => 1],
            ['id' => 0],
            ['id' => -1],
        ]],
        3 => ['name' => [
            ['id' => 2],
            4 => ['id' => 3],
            ['id' => 4],
        ]],
    ],
];
// goods.1.name
// goods.3.name


$ss = [];
$path = '';
foreach (explode('*', $field) as $x) {
    $path .= $x;

    $startWith = $x[0] === '.';
    $endWith = substr($x, -1) === '.';

    if ($startWith && $endWith) {
        $x = substr($x, 0, -1);
        foreach ($ss as &$yy) {
            $yy .= $x;
        }
        unset($yy);

        $tmpSS = [];
        foreach ($ss as $yy) {
            $tmp =& $arr;
            foreach (explode('.', $yy) as $zz) {
                $tmp =& $tmp[$zz];
            }

            foreach ($tmp as $k => $v) {
                $tmpSS[] = "{$yy}.{$k}";
            }
            unset($tmp);
        }
        $ss = $tmpSS;
    } elseif ($startWith) {
        foreach ($ss as &$yy) {
            $yy .= $x;
        }
        unset($yy);
    } elseif ($endWith) {
        $x = substr($x, 0, -1);
        foreach ($arr[$x] as $k => $v) {
            $ss[] = "{$path}{$k}";
        }
    }
}

var_export($ss);
