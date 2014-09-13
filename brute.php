<?php
include "vendor/autoload.php";

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

if ($mock = false) {
    $crawler = new Crawler();
    $crawler->addHtmlContent(file_get_contents('example.html'));
} else {
    $client = new Client();
    $client->request('GET', 'http://habrahabr.ru/company/innopolis_university/blog/236647/');
    $crawler = $client->getCrawler();
}

$users = [];
$crawler->filter('.comment_body')->each(function (Crawler $node) use (&$users) {
    preg_match_all('/([a-f0-9]{32})/', $node->text(), $matches);
    if ($matches[1]) {
        $user = $node->filter('.username')->first()->text();
        foreach ($matches[1] as $md5) {
            $users[$user][] = $md5;
        }
    }
});

$answers = [];
for ($i = 0; $i <= 99; $i++) {
    $answer = str_pad($i, 2, '0', STR_PAD_LEFT);
    foreach ($users as $user => $md5List) {
        foreach ($md5List as $k => $md5) {
            if (md5($user.$answer) == $md5) {
                $answers[$answer][] = $user;
                unset($users[$user][$k]);
            }
        }
    }
}

echo json_encode($answers, JSON_PRETTY_PRINT);
