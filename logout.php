<?php
require __DIR__ . '/app/functions.php';

start_app_session();
session_destroy();

redirect('login.php');
