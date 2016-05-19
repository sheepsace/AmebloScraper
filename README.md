# AmebloScraper
アメブロのスクレイピング用のクラスです(テーマ指定可)

# 機能
アメーバID、テーマID、何ページ目を読み込むかなどを指定して、アメブロの記事データを取得します。    
アメブロ公式のRSSでは、テーマを指定しての記事取得が出来ないため、このクラスを作りました。

# サンプル
1.テーマを指定して、アメブロの記事データを取得する場合
~~~php
require_once("AmebloScraper.php");

$scraper = new AmebloScraper("アメーバIDを入力してください");
$articles = $scraper->getArticles("テーマIDを数値形式で入力してください");
//途中でエラーが発生したら、エラーメッセージに値が入ります
if($articles == false){
    echo $scraper->errorMessage;
}
~~~

# 動作確認出来ているブログ(2016/5/19時点)
http://ameblo.jp/famigeki/
(結構ブログによってclass名とか違うのですね…パターンを見つけないと…)
