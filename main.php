<?php

//under php5.4

require_once('codebird.php');

\Codebird\Codebird::setConsumerKey('your key', 'your key');
$cb = \Codebird\Codebird::getInstance();
$cb->setToken('your key', 'your key');

$params = array(
                'screen_name' => 'about_hiroppy',
                'count' => 10
                );

$tweets = (array) $cb->statuses_userTimeline($params);
array_pop($tweets);

$user_name;
$screen_name;
$description;
$profile_image_url;
$tweet_array; //debug
$info;

$flg = false;

foreach($tweets as $tweet){
    if(!$flg){
        $user_name          = trim($tweet -> user -> name);
        $screen_name        = trim($tweet -> user -> screen_name);
        $description        = trim($tweet -> user -> description);
        $profile_image_url  = trim($tweet -> user ->  profile_image_url);
        $flg                = true;

        $info['info'] = array(
                              'user_name' => $user_name,
                              'screen_name' => $screen_name,
                              'description' => $description,
                              'profile_image_url' => $profile_image_url
                              );
    }
    $tweet_array[] = $tweet -> text;
}

$info['tweet'] = $tweet_array;

$json_info = json_unescaped($info);

//result 
echo jsonCnv($json_info);



function json_unescaped($value){
    $v = json_encode($value);
    $v = preg_replace_callback(
                               "/\\\\u([0-9a-zA-Z]{4})/", 
                               create_function(
                                               '$matches',
                                               'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UTF-16");'
                                               ),
                               $v
                               );
    $v = preg_replace('/\\\\\//', '/', $v);
    return $v;
}


//referring to "http://null.style.coocan.jp/?json%C0%B0%B7%C1";

function jsonCnv($json){
    $def = array(
                 '/([{\[,])/'        => "$1\n",
                 '/([}\]])/'         => "\n$1",
                 '/([^\s]):([^\s])/' => '$1 : $2',
                 );

    $p = array_keys($def);
    $r = array_values($def);
    $s = preg_replace($p, $r, $json);

    $buf    = explode("\n", $s);
    $indent = 0;
    $out    = array();
    foreach ($buf as $line) {
        if (preg_match('/[}\]],?$/', $line)) {
            $indent--;
        }
        $out[] = str_repeat("\t", $indent) . $line;
        if (preg_match('/[{\[]$/', $line)) {
            $indent++;
        }
    }

    return implode("\n", $out);
}

?>
