<?php
require __DIR__ . '/vendor/autoload.php';

function post_json(string $url, array $data): array {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($curl);
    
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if ($status_code > 399) {
        die("http post failed:\nurl: $url\nstatus_code: $status_code");
    }

    curl_close($curl);

    return json_decode($response, true);
}

function get_article_for_term(string $term): string {
    // TODO: improve the prompt
    $response = post_json('http://localhost:11434/api/generate', array(
        'model' => 'mistral',
        'system' => 'You generate wikipedia articles for search terms. When presented with a search term you will answer in markdown format without a markdown code block and without the search term as a title. Start with a consice explanation of the search term. Do not start with a heading. Always generate multiple sections.',
        'prompt' => $term,
        'stream' => false,
    ));

    // TODO: cache this content in the database
    $content = $response['response'];


    // wrap every word in a link to itself as a search term
    $with_links = preg_replace('/(\w+)/', '<a href="/?term=$1">$1</a>', $content);

    // parse the markdown
    $parser = new \cebe\markdown\Markdown();
    $parser->html5 = true;
     
    $article = $parser->parse($with_links);

    $escaped = htmlspecialchars($with_links);
    return $article . "<br><details><summary>Source</summary><pre>$escaped</pre></details>";
}
?>
<html>
    <head>
        <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        * {
            margin: 0;
        }

        body {
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            font-family: sans-serif;
        }

        img, picture, video, canvas, svg {
            display: block;
            max-width: 100%;
        }

        input, button, textarea, select {
            font: inherit;
        }

        p, h1, h2, h3, h4, h5, h6 {
            overflow-wrap: break-word;
        }

        #root, #__next {
            isolation: isolate;
        }
        </style>
    </head>

    <body>
        <header style="padding: 1rem; background-color: #eee; display: flex; flex-direction: row; gap: 2rem; align-items: center; justify-content: space-between;">
            <a href="/">
                <h1>AI Wikipedia</h1>
            </a>
            <form method="GET">
                <input required name="term" />
                <button type="submit">Search</button>
            </form>
        </header>
        <main>
            <?php
            if(array_key_exists('term', $_GET)) {
                $term = $_GET['term'];
                echo("<h1>$term</h1>" . get_article_for_term($term));
            } else {
                echo("You are on the homepage, search the wiki using the search bar (top)");
            }
            ?>
        </main>
    </body>

</html>
