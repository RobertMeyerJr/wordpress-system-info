<?php 
class SI_Timers{
    private static $timers = [];

    public static function start($name, $description = null){
        $this->timers[$name] = [
            'start' => microtime(true),
            'desc' => $description,
        ];
    }

    public static function end($name){
        $this->timers[$name]['end'] = microtime(true);
    }

    public static function get(){
        $metrics = [];

        if (count($this->timers)) {
            foreach($this->timers as $name => $timer) {
                $timeTaken = ($timer['end'] - $timer['start']) * 1000;
                $output = sprintf('%s;dur=%f', $name, $timeTaken);

                if ($timer['desc'] != null) {
                    $output .= sprintf(';desc="%s"', addslashes($timer['desc']));
                } 
                $metrics[] = $output;
            }
        }

        return implode($metrics, ', ');
    }
	
	public static function header(){
		header('Server-Timing: '.self::getTimers());
	}
}