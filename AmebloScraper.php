<?php

/**
 * アメブロスクレイピングクラス
 * Class AmebloScraper
 */
class AmebloScraper
{
    /**
     * アメーバID
     * @var string
     */
    private $amebaId;
    /**
     * ブログの形式
     * ソース内に<article>が使用されている ⇒ new
     * ソース内に<article>が使用されてない ⇒ old
     * (私はこの2種類しか確認していないのですが、他にもあったら教えてください…)
     * @var
     */
    private $blogType;
    /**
     * テーマID
     * @var string
     */
    private $themeId;
    /**
     * エラーメッセージ
     * @var
     */
    public $errorMessage;

    /**
     * コンストラクタ
     * @param string $amebaId アメーバID
     */
    public function __construct($amebaId, $blogType = "new")
    {
        $this->amebaId = $amebaId;
        $this->blogType = $blogType;
        $this->errorMessage = "";
    }

    /**
     * アメブロの記事を取得する
     * @param string $themeId テーマID
     * @param int $page 取得するページ番号
     * @param string $returnFormat 返却時の型式(json: json、array: 配列)
     * @return array|bool|string
     */
    public function getArticles($themeId = 0, $page = 1, $returnFormat = "array")
    {
        try {
            $this->themeId = $themeId;

            //パラメータのバリデーションチェックする
            if ($this->amebaId === "") {
                throw new Exception("AmebaID is required.");
            }
            if (!is_int($themeId)) {
                throw new Exception("Parameter 'themeId' must be integer.");
            }
            if (!is_int($page)) {
                throw new Exception("Parameter 'page' must be integer.");
            }

            //アメブロのコンテンツを取得する
            $url = "http://ameblo.jp/" . $this->amebaId . "/";
            if ($this->themeId != 0) {
                //テーマ指定ありの場合
                $url .= "theme" . $page . "-" . $this->themeId . ".html";
            } else {
                //テーマ指定なしの場合
                $url .= "page-" . $page . ".html";
            }

            //スクレイピングでHTMLを取得する
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            $contents = curl_exec($ch);
            curl_close($ch);

            //日付、タイトル、記事URLの文字列を取得する
            if($this->blogType == "new"){
                $pDate = '/<time datetime=".*" pubdate="pubdate">(.*?)<\/time>/';
                $pTitle = '/<a href="http:\/\/ameblo.jp\/'.$this->amebaId.'\/entry-(.*?).html" class="skinArticleTitle" rel="bookmark">(.*?)<\/a>/';
                $pTheme = '/<span class="articleTheme">テーマ：.*<a href="http:\/\/ameblo.jp\/'.$this->amebaId.'\/theme-(.*?).html" rel="tag">(.*?)<\/a>.*<\/span>/';
            }else{
                $pDate = '/<span class="date">(.*?)<\/span>/';
                $pTitle = '/<h3 class="title">.*\n';
                $pTitle .= '<a href="http:\/\/ameblo.jp\/' . $this->amebaId . '\/entry-(.*?).html">(.*?)<\/a>\n';
                $pTitle .= '.*<\/h3>/';
                $pTheme = '/<span class="theme">テーマ：.*<a href="http:\/\/ameblo.jp\/'.$this->amebaId.'\/theme-(.*?).html">(.*?)<\/a>.*<\/span>/';
            }
            if (!preg_match_all($pDate, $contents, $dates, PREG_SET_ORDER)) {
                throw new Exception("Failed to fetch date.");
            }
            if (!preg_match_all($pTitle, $contents, $titles, PREG_SET_ORDER)) {
                throw new Exception('Failed to fetch url and title.');
            }
            if (!preg_match_all($pTheme, $contents, $themes, PREG_SET_ORDER)) {
                throw new Exception('Failed to fetch themeId and themeTitle.');
            }

            $articles = array();
            //日付のマッチ配列分だけ回す(タイトルなども同じ数だけあるはずだからインデックスは共用とする)
            for ($i = 0; $i < count($dates); $i++) {
                $article = new stdClass;
                $article->date = explode(" ", $dates[$i][1])[0];
                $article->time = explode(" ", $dates[$i][1])[1];

                $article->articleId = $titles[$i][1];
                $article->title = $titles[$i][2];

                $article->themeId = $themes[$i][1];
                $article->themeTitle = $themes[$i][2];

                array_push($articles, $article);
            }

            switch ($returnFormat) {
                case "json":
                    $articles = json_encode($articles);
                    break;
                case "array":
                default:
                    break;
            }
            return $articles;

        } catch (Exception $e) {
            //どこかで失敗したらfalseを返す
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }
}