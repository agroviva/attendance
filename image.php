<?php

use EGroupware\Api;
use EGroupware\Api\Egw;
use EGroupware\Api\Contacts;

$GLOBALS['egw_info']['flags']['currentapp'] = 'attendance';
$_GET['cd'] = 'no';

ob_start();
include '../header.inc.php';
ob_get_clean();

class AttendancePhoto extends Contacts
{
    public function __construct()
    {
        parent::__construct();
        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            if (is_numeric($id) && $id) {
                ob_start();
                $contact_id = $GLOBALS['egw']->accounts->id2name($id,'person_id');

                if (!($contact = $this->read($contact_id)) ||
                    empty($contact['jpegphoto']))
                {
                    Egw::redirect(Api\Image::find('addressbook','photo'));
                }
                // use an etag over the image mapp
                $etag = '"'.$contact_id.':'.$contact['etag'].'"';
                if (!ob_get_contents())
                {
                    header('Content-type: image/jpeg');
                    header('ETag: '.$etag);
                    // if etag parameter given in url, we can allow browser to cache picture via an Expires header
                    // different url with different etag parameter will force a reload
                    if (isset($_GET['etag']))
                    {
                        Api\Session::cache_control(30*86400);   // cache for 30 days
                    }
                    // if servers send a If-None-Match header, response with 304 Not Modified, if etag matches
                    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)
                    {
                        header("HTTP/1.1 304 Not Modified");
                    }
                    elseif(!empty($contact['jpegphoto']))
                    {
                        header('Content-length: '.bytes($contact['jpegphoto']));
                        echo $contact['jpegphoto'];
                    }
                    else
                    {
                        header('Content-length: '.$size);
                        readfile($url);
                    }
                    exit();
                }
                ob_get_clean();
            }
        }
    }
}

new AttendancePhoto();