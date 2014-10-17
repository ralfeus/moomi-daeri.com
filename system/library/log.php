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
        $line = isset($callerArray['line']) ? $callerArray['line'] : 0;

		$file = DIR_LOGS . $this->filename;
		$handle = fopen($file, 'a+');
		fwrite($handle, date('Y-m-d G:i:s') . "\t$caller():$line\t" . $message . "\n");
		fclose($handle);
	}
}