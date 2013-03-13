<?php
final class Log {
	private $filename;
	
	public function __construct($filename) {
		$this->filename = $filename;
	}
	
	public function write($message) {
        $trace = debug_backtrace();
        array_shift($trace);
        $callerArray = $trace[0];
        $caller = isset($callerArray['class']) ? $callerArray['class'] . "::" . $callerArray['function'] : $callerArray['function'];

		$file = DIR_LOGS . $this->filename;
		$handle = fopen($file, 'a+');
		fwrite($handle, date('Y-m-d G:i:s') . "\t$caller()\t" . $message . "\n");
		fclose($handle);
	}
}