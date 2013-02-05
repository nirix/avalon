<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 *
 * This file is part of Avalon.
 *
 * Avalon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; version 3 only.
 *
 * Avalon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Avalon. If not, see <http://www.gnu.org/licenses/>.
 */

namespace avalon\core;

use avalon\http\Request;
use avalon\http\Router;
use avalon\output\Body;
use avalon\output\View;

/**
 * Controller
 *
 * @since 0.3
 * @package Avalon
 * @subpackage Core
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Controller
{
    public $_render = array(
        'action' => true,     // Call the routed action, or not
        'view'   => false,    // View to render, set in __construct()
        'layout' => 'default' // Layout to render
    );

    public function __construct()
    {
        $called_class = explode('\\', get_called_class());
        unset($called_class[0], $called_class[1]);

        $this->_render['view'] = str_replace('\\', '/', implode('/', $called_class) . '/' . Router::$method);

        // Check if the route has an extension
        if (Router::$extension !== null) {
            $this->_render['view'] = $this->_render['view'] . Router::$extension;

            // Lets make sure the view for the extension exists
            if (View::exists($this->_render['view']) === false) {
                $this->show_404();
            } else {
                $this->_render['layout'] = 'plain';
            }
        }

        // Allow the views to access the app,
        // even though its not good practice...
        View::set('app', $this);
    }

    /**
     * Used to display the 404 page.
     */
    public function show_404()
    {
        // Send the request to the view and
        // change the view file to error/404.php
        // and disable the calling of the routed
        // controller method.
        View::set('request', Request::requestUri());
        $this->_render['view'] = 'error/404';
        $this->_render['action'] = false;
    }

    public function __shutdown()
    {
        if ($this->_render['view']) {
            $content = View::render($this->_render['view']);
        } else {
            $content = '';
        }

        // Are we wrapping the view in a layout?
        if ($this->_render['layout']) {
            Body::append(View::render("layouts/{$this->_render['layout']}", array('output' => $content)));
        }

        // Set the X-Powered-By header and render the layout with the content
        header("X-Powered-By: Avalon/" . Kernel::version());
        print(Body::body());
    }
}
