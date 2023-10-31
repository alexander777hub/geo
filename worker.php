<?php

require_once __DIR__ . '/vendor/autoload.php';

use GO\Scheduler;

// File worker.php
$scheduler = new Scheduler();
$scheduler->php('scheduler.php');
$scheduler->work();
