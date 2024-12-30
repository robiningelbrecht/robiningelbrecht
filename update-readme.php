<?php

file_put_contents(
    __DIR__.'/assets/medium-blog-posts.svg',
    file_get_contents('https://medium-rss-github.robiningelbrecht.be/@ingelbrechtrobin/0,1,2,3/layout:two-col')
);

file_put_contents(
    __DIR__.'/assets/github-streak-stats.svg',
    file_get_contents('https://github-readme-streak-stats.herokuapp.com/?user=robiningelbrecht'),
);
