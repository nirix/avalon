<?php
/*!
 * Avalon
 * Copyright (C) 2011-2025 Jack Polgar
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

declare(strict_types=1);

namespace Avalon\Http;

use Avalon\Core\Kernel;
use Avalon\Http\Router;
use Avalon\Output\Body;
use Avalon\Output\View;

/**
 * Controller
 *
 * @since 0.3
 * @package Avalon
 * @subpackage Http
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Controller
{
    /**
     * @var array{action: bool, view: string|bool, layout: 'default.phtml'} $render
     */
    #[\Deprecated('Return a Response from the controller intead', '1.0')]
    public $render = array(
        'action' => true,     // Call the routed action, or not
        'view'   => false,    // View to render, set in __construct()
        'layout' => 'default.phtml' // Layout to render
    );

    #[\Deprecated('Use middleware instead', '1.0')]
    public $before = array();

    #[\Deprecated('Use middleware instead', '1.0')]
    public $after = array();

    public function __construct()
    {
        $called_class = explode('\\', get_called_class());
        unset($called_class[0], $called_class[1]);

        $this->render['view'] = str_replace('\\', '/', implode('/', $called_class) . '/' . Router::$method);
    }

    /**
     * Set global variable or variables for every view.
     *
     * @param string|array $name  String for single variable or array of variables by name => value.
     * @param mixed|null   $value Value of variable or null if mass setting by first parameter.
     */
    public function set(string|array $name, mixed $value = null): void
    {
        View::set($name, $value);
    }

    #[\Deprecated(
        message: "Return a Response from the controller instead",
        since: "1.0",
    )]
    public function __shutdown()
    {
        // Don't render the layout for json content
        if (Router::$extension == 'json') {
            $this->render['layout'] = false;
        }

        // Render the view
        $content = '';
        if ($this->render['view']) {
            Body::append(View::render($this->render['view']));
        }

        // Are we wrapping the view in a layout?
        if ($this->render['layout']) {
            $content = Body::content();
            Body::clear();
            Body::append(View::render("layouts/{$this->render['layout']}", array('content' => $content)));
        } else {
            Body::append($content);
        }

        // Set the X-Powered-By header and render the layout with the content
        header("X-Powered-By: Avalon/" . Kernel::version());
        print(Body::content());
    }
}
