<?php
if($_GET['mod']=='photo'){
  $k=fopen("1.txt","w+");
  fwrite($k,$_POST['photo']);
  
  $k=fopen("2.txt","w+");
  fwrite($k,$_POST['photo0']);
  
  if($imga=explode(',',$_POST['photo'])){
      $k=fopen("2.jpg","w+");
      fwrite($k,base64_decode($imga['1']));
  }
}




$result['ok'] = 1; //������һ���Ƕ�
$result['url'] = '2.jpg'; //������һ���Ƕ�
echo json_encode($result);










print_r(htmlspecialchars(file_get_contents('http://www.mizone.cc/1L/mobile.php')));





?>