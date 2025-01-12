<?php

$deal_file = "./deal";
$snapshot_file = "./snapshot";


$deal = []; // 账户=>时间=>金额
foreach (explode("\n", file_get_contents($deal_file)) as $v){
    if($v == "" || strpos($v, "产品名") !== false) continue;
    list($acc, $da, $money) = explode("\t",$v);
    if ($money == 0) continue;
    $deal[$acc][$da] = $money; 
}

$snapshot = []; // 账户=>[时间，金额] // 保证是按时间从小到大排列
foreach (explode("\n", file_get_contents($snapshot_file)) as $v){
    if($v == "" || strpos($v, "产品名") !== false) continue;
k r     list($acc, $da, $money) = explode("\t",trim($v));
    if ($money == 0) continue;
    $snapshot[$acc][] = [$da, $money];
}

$ana = [];
foreach($snapshot as $acc=>$v){
    $last_date = ""; // 上一次的周期
    $last_money = 0; // 上一次的钱
    $last_origin_money = 0; // 上一次的本金
    foreach($v as $vv){
        list($da, $money) = $vv;
        if($last_date == ""){
            $last_date = "1990/02/27";
        }
        echo ("deal $acc $da $last_date\n");

        // 本周期内的钱*天数
        $money_days = 0;
        $money_days_all = 0;
        // 本金
        $origin_money = 0;
    
        foreach($deal[$acc] as $deal_date => $deal_money){
            if (strtotime($deal_date) > strtotime($da)) continue;
            $origin_money += $deal_money;
            $this_days = (strtotime($da) - max(strtotime($last_date), strtotime($deal_date))) / 86400;
            $all_days = (strtotime($da) - strtotime($deal_date)) / 86400;
            $money_days += $deal_money* $this_days;
            $money_days_all += $deal_money*$all_days;
            echo ("\t deal each $acc $da, $deal_date, $deal_money, $origin_money, $this_days\n");
        }

        if($money_days == 0 || $money_days_all == 0){
            var_dump($acc, $deal[$acc], $last_date, $last_origin_money, $money, $money_days, $money_days_all);  
            die("kkk");
        }
        // 本周期营利
        $profit = $money - $last_money -($origin_money - $last_origin_money);
        $profit_all = $money - $origin_money;
        $profit_rate = $profit*365 / $money_days;
        $profit_all_rate = $profit_all * 365 / $money_days_all;

        


        $ana[] = [
            $acc,
            $da,
            $last_money,
            $origin_money - $last_origin_money,
            $origin_money,
            $money,
            $profit,
            $profit_all,
            sprintf("%.4f%%", $profit_rate * 100),
            sprintf("%.4f%%", $profit_all_rate * 100),
        ];
        $last_date = $da; // 上一次的周期
        $last_money = $money; // 上一次的钱
        $last_origin_money = $origin_money; // 上一次的本金

    }
}

file_put_contents("./ana", "产品名	记录时间	上个周期金额	本周期内交易金额	本金	当前金额	本周期营利	总营利	本周期年化	总年化\n");
foreach($ana as $v){
    file_put_contents("./ana", implode("\t", $v)."\n", FILE_APPEND);
}
