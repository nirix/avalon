<?php
/*
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 *
 * @author Jack P. <nrx@nirix.net>
 * @copyright Jack P.
 * @license New BSD License
 */

require SYSPATH . '/core/error.php';
require SYSPATH . '/core/load.php';
require SYSPATH . '/core/controller.php';
require SYSPATH . '/core/avalon.php';
require SYSPATH . '/core/database.php';
require SYSPATH . '/database/driver.php';

Load::lib('request');
Load::lib('router');
Load::lib('output');
Load::lib('view');