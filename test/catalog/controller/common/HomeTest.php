<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\common;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class HomeTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName );
        require_once(DIR_APPLICATION . "/controller/common/home.php");
    }

    protected function setUp() {
        parent::setUp();
        $this->class = new \ControllerCommonHome($this->registry);
    }

    /**
     * @test
     * @covers ControllerCommonHome::index
     */
    public function index() {
        $this->class->index();
    }

    /**
     * @test
     */
    public function checkLatestCount() {
        define('LAYOUT_ID_HOME', 1);
        $this->class->index();

        /// Get amount of the latest modules to be shown
        $modules = $this->registry->get('config')->get('latest_module');
        if (!$modules) {
            return;
        }
        $latestModulesCount = 0;
        foreach ($modules as $module) {
            if ($module['layout_id'] == LAYOUT_ID_HOME && $module['position'] == 'content_bottom' && $module['status']) {
                $latestModulesCount++;
            }
        }

        /// Compare amount of the latest modules actually shown with expected one
        $this->assertEquals(
            $latestModulesCount,
            preg_match_all('/class="box-latest"/', \PHPUnit_Framework_Assert::readAttribute($this->class, 'output'))
        );
    }
}
 