<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 17.06.2018
 * Time: 17:27
 */

namespace system\engine;


use test\system\Test;

class ControllerTest extends Test {
    /**
     * @test
     * @covers Controller::setBreadcrumbs()
     */
    public function setBreadcrumbs() {
        $controller = new ControllerChild($this->registry);
        $bc = $controller->getBreadcrumbs();
        print_r($bc);
        $this->assertTrue($bc[1]['href'] == '/moomi-daeri.com/admin/index.php?route=inter/mediate&amp;arg1=value1&amp;token=token1');
    }
}

class ControllerChild extends Controller {
    public function getBreadcrumbs() {
        $this->session->data['token'] = 'token1';
        $this->setBreadcrumbs([[
            'text' => 'Intermediate',
            'route' => 'inter/mediate',
            'args' => ['arg1' => 'value1']
        ]]);
        return $this->data['breadcrumbs'];
    }
}