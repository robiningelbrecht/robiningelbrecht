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

$tableRows = [];

foreach ($feed['items'] as $post) {
    $title = htmlspecialchars($post['title'], ENT_QUOTES);
    $link = $post['link'];
    $pubDate = date('M d, Y', strtotime($post['pubDate']));
    $image = $post['thumbnail'] ?: extractFirstImage($post['description'] ?? '');

    $imgCell = '<td width="150">';
    if ($image) {
        $imgCell .= "\n" . '  <a href="' . $link . '"><img src="' . $image . '" alt="' . $title . '" width="150"></a>';
    }
    $imgCell .= "\n</td>";

    $description = trim(strip_tags($post['description'] ?? ''));
    $description = preg_replace('/\s+/', ' ', $description);
    if (mb_strlen($description) > 120) {
        $description = mb_substr($description, 0, 120) . '...';
    }

    $textCell = '<td>' . "\n";
    $textCell .= '  <a href="' . $link . '"><strong>' . $title . '</strong></a><br>' . "\n";
    $textCell .= '  <sub>' . htmlspecialchars($description, ENT_QUOTES) . '</sub><br>' . "\n";
    $textCell .= '  <sub>' . $pubDate . '</sub>' . "\n";
    $textCell .= '</td>';

    $tableRows[] = "<tr>\n" . $imgCell . "\n" . $textCell . "\n</tr>";
}

$blogPostsMarkdown = "<table>\n" . implode("\n", $tableRows) . "\n</table>";

$readmePath = __DIR__ . '/README.md';
$readme = file_get_contents($readmePath);

$pattern = '/(<!-- START-BLOG-POSTS-WRAPPER -->).*?(<!-- END-BLOG-POSTS-WRAPPER -->)/s';
$replacement = "$1\n{$blogPostsMarkdown}\n$2";
$readme = preg_replace($pattern, $replacement, $readme);

file_put_contents($readmePath, $readme);

echo "README.md updated with " . count($feed['items']) . " blog posts\n";
