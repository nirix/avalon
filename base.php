<?php
/**
 * Avalon
 * Copyright (C) 2011 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

require SYSPATH . '/core/error.php';
require SYSPATH . '/core/load.php';
require SYSPATH . '/core/controller.php';
require SYSPATH . '/core/avalon.php';
require SYSPATH . '/core/database.php';

Load::lib('request');
Load::lib('router');
Load::lib('output');
Load::lib('view');