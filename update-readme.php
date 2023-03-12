<?php

$readme = file_get_contents('README.md');
$dayTimeSummary = file_get_contents('https://raw.githubusercontent.com/robiningelbrecht/github-commit-history/master/build/markdown/commit-history-day-time-summary.md');
$weekDaySummary = file_get_contents('https://raw.githubusercontent.com/robiningelbrecht/github-commit-history/master/build/markdown/commit-history-week-day-summary.md');
$mostRecentCommitsSummary = file_get_contents('https://raw.githubusercontent.com/robiningelbrecht/github-commit-history/master/build/markdown/most-recent-commits.md');

$readme = replaceReadMeSection('commits-per-day-time', $dayTimeSummary, $readme);
$readme = replaceReadMeSection('commits-per-weekday', $weekDaySummary, $readme);
$readme = replaceReadMeSection('most-recent-commits', $mostRecentCommitsSummary, $readme);

file_put_contents('README.md', $readme);

function replaceReadMeSection(string $sectionName, string $replaceWith, string $subject)
{
    return preg_replace(
        sprintf('/<!--START_SECTION:%s-->[\s\S]+<!--END_SECTION:%s-->/', $sectionName, $sectionName),
        implode("\n", [
            sprintf('<!--START_SECTION:%s-->', $sectionName),
            $replaceWith,
            sprintf('<!--END_SECTION:%s-->', $sectionName),
        ]),
        $subject
    );
}