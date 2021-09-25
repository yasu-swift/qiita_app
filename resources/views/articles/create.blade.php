<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    @if (!empty($errors))
        <div class="error">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif
    <form action="{{ route('articles.store') }}" method="post">
        @csrf

        <select name="private" id="private" required>
            <option value="true" selected>非公開</option>
            <option value="false" disabled>公開</option>
        </select>
        <div>
            <input type="text" name="title" id="title" placeholder="タイトル">
        </div>
        <div>
            <input type="text" name="tags" id="tags" placeholder="知識に関するタグをスペース区切りで5つまで入力(例: Ruby Rails)" required>
        </div>
        <div>
            <textarea name="body" id="body" cols="30" rows="10" placeholder="エンジニアに関わる知識をMarkdown記法で書いて共有しよう"
                required></textarea>
        </div>
        <input type="submit" value="投稿する">
    </form>
    <button type="button" onclick="location.href='{{ route('articles.index') }}'">一覧へ戻る</button>
</body>

</html>
