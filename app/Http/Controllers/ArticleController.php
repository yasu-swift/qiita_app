<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
// Guzzle読み込み
use GuzzleHttp\Client;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return view('articles.index')->with(compact('articles'));
        $method = 'GET';
        $tag_id = 'PHP';
        $per_page = 30;

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/tags/' . $tag_id .
            '/items?' . 'per_page=' . $per_page;

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        // try catchでエラー時の処理を書く
        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $articles = null;
        }


        // ここから自分の記事取得
        $method = 'GET';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/authenticated_user/items';
        
        // api / v2 / authenticated_user / items

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        // try catchでエラー時の処理を書く
        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $my_articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $my_articles = null;
        }

        return view('articles.index')->with(compact('articles', 'my_articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return view('articles.index')->with(compact('articles'));
        $method = 'POST';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items';
        // 'ここにスペース入れる'
        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function($tag){
            return ['name' => $tag];
        }, $tag_array);
        // dd($tags);

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => $request->private == 'true' ?
                true : false ,
            'tags' => $tags,
        ];
        // dd($data);
        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
                'Content-Type' => 'application/json'
            ],
            'json' => $data,
        ];
        
        

        // Client(接続する為のクラス)を生成
        $client = new Client();

        // try catchでエラー時の処理を書く
        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // dd($e);
            // return back()->with(['error' => $e->getResponse()->getReasonPhrase()]);
            return back()->withErrors(['error' => '投稿に失敗しました！']);
        }
        $message = new \Illuminate\Support\HtmlString("記事の投稿に成功しました");
        return redirect() -> route('articles.index')->with('flash_message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        // return view('articles.index')->with(compact('articles'));
        $method = 'GET';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items/' . $id;

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        // try catchでエラー時の処理を書く
        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);
            // 変換するクラスをインスタンス化して設定を追加
            $parser = new  \cebe\markdown\GithubMarkdown();
            $parser->keepListStartNumber = true;
            //改行
            $parser->enableNewlines = true;
            // MarkdownをHTML文字列に変換し、HTMLに変換(エスケープする)
            $html_string = $parser->parse($article->body);
            $article->html = new \Illuminate\Support\HtmlString($html_string);

        } catch (\Throwable $th) {
            return back();
        }


        // ユーザー情報の取得
        $method = 'GET';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/authenticated_user';

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        // try catchでエラー時の処理を書く
        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $user = json_decode($body, false);
            
        } catch (\Throwable $th) {
            return back();
        }

        return view('articles.show')->with(compact('article', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        // return view('articles.index')->with(compact('articles'));
        $method = 'GET';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items/' . $id;

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        // try catchでエラー時の処理を書く
        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);

            $tag_array = array_map(function($tag){
                return $tag->name;
            }, $article->tags);
            $article->tags = implode(' ', $tag_array);

        } catch (\Throwable $th) {
            return back();
        }

        return view('articles.edit')->with(compact('article'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // return view('articles.index')->with(compact('articles'));
        $method = 'PATCH';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items/'. $id;
        // 'ここにスペース入れる'
        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);
        // dd($tags);

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => $request->private == 'true' ?
            true : false,
            'tags' => $tags,
        ];
        // dd($data);
        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
                'Content-Type' => 'application/json'
            ],
            'json' => $data,
        ];



        // Client(接続する為のクラス)を生成
        $client = new Client();

        // try catchでエラー時の処理を書く
        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // dd($e);
            // return back()->with(['error' => $e->getResponse()->getReasonPhrase()]);
            return back()->withErrors(['error' => '更新に失敗しました！']);
        }
        $message = new \Illuminate\Support\HtmlString("記事の更新に成功しました");
        return redirect()->route('articles.index')->with('flash_message', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $method = 'DELETE';

        $url = config('qiita.url'). '/api/v2/items/'. $id;
        $options = [
            'headers' =>[
                'Authorization' => 'Bearer ' . config('qiita.token')
            ]
            ];

            $client = new Client();

            try {
                $client->request($method, $url, $options);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                dd($e);
                return back()->withErrors([
                    'error' => '削除処理を失敗しました'
                ]);
            }
            return redirect()->route('articles.index')->with('flash_message', '記事を削除しました');
    }
}
