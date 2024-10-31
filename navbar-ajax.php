<?php

require_once('../../../wp-config.php');

check_ajax_referer('navbar-nonce');

$options = $NavBar->get_options();

if ($_POST['type'] == "links")
{
    $options['links'] = $_POST['links'];
}
elseif ($_POST['type'] == "width")
{
    $options['width'] = $_POST['width'];
}
elseif ($_POST['type'] == "hide")
{
    $options['hide'] = ($_POST['hide'] == "true" ? true : false);
}
elseif ($_POST['type'] == "position")
{
    $options['position'] = $_POST['position'];
}

update_option($NavBar->DB_option, $options);

?>