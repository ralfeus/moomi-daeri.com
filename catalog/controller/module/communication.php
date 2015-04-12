<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 27.7.12
 * Time: 18:39
 * To change this template use File | Settings | File Templates.
 */
class ControllerModuleCommunication extends Controller
{
    public function __construct($registry)
    {
        parent::__construct($registry);

    }

    public function index()
    {
        $this->template = "module/communication.tpl";
        $this->getResponse()->setOutput($this->render());
    }
}
