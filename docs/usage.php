<?php
/**
 * How to use Services_Libravatar
 */
require_once 'Services/Libravatar.php';

$sla = new Services_Libravatar();
$sla->setSize(48)
    ->setDefault('monsterid');

$blogCommentUsers = array(
    'foo@example.org',
    'someone@example.net'
);

foreach ($blogCommentUsers as $email) {
    $avatarUrl = $sla->getUrl($email);
    echo $email . ': <img src="' . htmlspecialchars($avatarUrl) . '" alt="avatar"/>'
        . '<br/>' . "\n";
}
?>