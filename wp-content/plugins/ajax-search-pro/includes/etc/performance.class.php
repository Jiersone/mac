<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_Performance')) {
    class wpd_Performance {

        private $records;
        private $default = array(
            'run_count' => 0,
            'average_runtime' => 0,
            'average_memory' => 0,
            'last_runtime' => 0,
            'last_memory' => 0
        );
        private $runtime;
        private $memory;
        private $key;

        function __construct($key = "plugin_performance") {
            $this->key = $key;
            $this->records = get_option($key, $this->default);
        }

        function reset() {
            delete_option($this->key);
        }

        function get_data() {
            return $this->records;
        }

        function start_measuring() {
            $this->runtime = microtime(true);
            $this->memory = memory_get_usage(true);
        }

        function stop_measuring() {
            $this->runtime = microtime(true) - $this->runtime;
            $this->memory = memory_get_peak_usage(true) - $this->memory;
            $this->save();
        }

        function dump_data() {
            var_dump($this->records);
        }

        private function save() {
            $this->count_averages();

            $this->records['last_runtime'] = $this->runtime > 15 ? 15 : $this->runtime;
            $this->records['last_memory'] = $this->memory;
            ++$this->records['run_count'];

            update_option($this->key, $this->records);
        }

        private function count_averages() {
            $this->records['average_runtime'] =
                ($this->records['average_runtime'] * $this->records['run_count'] + $this->runtime) / ($this->records['run_count'] + 1);
            $this->records['average_memory'] =
                ($this->records['average_memory'] * $this->records['run_count'] + $this->memory) / ($this->records['run_count'] + 1);
        }
    }
}