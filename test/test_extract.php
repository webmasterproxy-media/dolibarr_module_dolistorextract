<?php

require_once '../class/dolistoreMail.class.php';

$html = file_get_contents('ex_info_produit.html');

$dolistoreMail = new dolistoreMail($html);
$datas = $dolistoreMail->extractData();

var_dump($datas);

//img[@class="date"]