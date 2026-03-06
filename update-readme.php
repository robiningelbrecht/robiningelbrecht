<?php

$feedUrl = 'https://api.rss2json.com/v1/api.json?rss_url=https://medium.com/feed/@ingelbrechtrobin';
$response = file_get_contents($feedUrl);

if ($response === false) {
    echo "Failed to fetch Medium feed\n";
    exit(1);
}

$feed = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($feed['items'])) {
    echo "Failed to parse feed or no posts found\n";
    exit(1);
}

function extractFirstImage(string $html): ?string
{
    if (preg_match('/<img[^>]+src="([^"]+)"/', $html, $matches)) {
        $src = $matches[1];
        if (strpos($src, 'medium.com/_/stat') !== false) {
            return null;
        }
        return $src;
    }
    return null;
}

$columns = 2;
$cells = [];

foreach ($feed['items'] as $post) {
    $title = htmlspecialchars($post['title'], ENT_QUOTES);
    $link = $post['link'];
    $pubDate = date('M d, Y', strtotime($post['pubDate']));
    $image = $post['thumbnail'] ?: extractFirstImage($post['description'] ?? '');

    $cell = '<td align="center" width="50%">' . "\n";
    if ($image) {
        $cell .= '  <a href="' . $link . '"><img src="' . $image . '" alt="' . $title . '" width="400"></a><br>' . "\n";
    }
    $cell .= '  <a href="' . $link . '"><strong>' . $title . '</strong></a><br>' . "\n";
    $cell .= '  <sub>' . $pubDate . '</sub>' . "\n";
    $cell .= '</td>';

    $cells[] = $cell;
}

$rows = array_chunk($cells, $columns);
$tableRows = [];
foreach ($rows as $row) {
    while (count($row) < $columns) {
        $row[] = '<td></td>';
    }
    $tableRows[] = "<tr>\n" . implode("\n", $row) . "\n</tr>";
}

$blogPostsMarkdown = "<table>\n" . implode("\n", $tableRows) . "\n</table>";

$readmePath = __DIR__ . '/README.md';
$readme = file_get_contents($readmePath);

$pattern = '/(<!-- START-BLOG-POSTS-WRAPPER -->).*?(<!-- END-BLOG-POSTS-WRAPPER -->)/s';
$replacement = "$1\n{$blogPostsMarkdown}\n$2";
$readme = preg_replace($pattern, $replacement, $readme);

file_put_contents($readmePath, $readme);

echo "README.md updated with " . count($feed['items']) . " blog posts\n";
