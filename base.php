<?php
$DB_acces = array("host" => 'localhost',  "username" => 'task_root', "passwd" => 'Geikinotakakunaidesu',"dbname" => 'php_junkie_bbs');
  define("DEBUG",FALSE);
//最大記事数（1ページにいくつの記事を表示するか）
  define("ARTICLE_MAX_NUM","3");
//最初のページ番号（初めて表示したときに何ページ目を表示するか）
  define("FIRST_VISIT_PAGE","1");

function page_no_calculation($next_no,$output_count,$pagetype){
  if($next_no=="0"){
    $result["start_count"] = "1";
    $result["end_count"] = $output_count;
    return $result;
  }elseif($pagetype=="next"){
    $result["start_count"] = $next_no;
    $result["end_count"] = (($next_no+$output_count)-1);
    return $result;
  }elseif($pagetype=="back"){
    $result["start_count"] = ($next_no-(ARTICLE_MAX_NUM-1));
    $result["end_count"] = $next_no;
    return $result;
  }
}
?>