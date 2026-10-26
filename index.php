<?php

// Front controller for hosting where the whole app sits inside the web folder (see .htaccess).
// Running from here keeps the app's base URL at its own folder (e.g. /bookstore) instead of /bookstore/public.
require __DIR__.'/public/index.php';
