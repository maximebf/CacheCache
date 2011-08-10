<?php

namespace CacheCache\Profilers;

use CacheCache\Profiler;

class Text implements Profiler
{
    public function log($text)
    {
        printf("PROFILER: %s\n", $text);
    }

    public function logOperation($operation, $key = null, $time = null, $data = null)
    {
        $template = "%s";
        $args = array(strtoupper($operation));

        if ($key !== null) {
            $template .= " -> %s";
            $args[] = implode(', ', (array) $key);
        }
        if ($time !== null) {
            $template .= " in %s ms";
            $args[] = $time;
        }
        if ($data !== null) {
            if (in_array($operation, array('add', 'set', 'setMulti'))) {
                $template .= " (expire = %s)";
                $args[] = $data['expire'] ?: 'infinity';
            }
        }

        array_unshift($args, $template);
        $this->log(call_user_func_array('sprintf', $args));
    }
}