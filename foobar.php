#!/bin/php


<?php
for ($i = 1; $i < 101; $i++) {
    $value = "{$i}, "; // value

    if ($i % 3 == 0 || $i % 5 == 0) { 
        // if value isdivisible by 3 0r 5 set value = "" and print foor or bar accordingly
        $value = "";
        if ($i % 3 == 0) {
            $value .= "foo";
        }

        if ($i % 5 == 0) {
            $value .= "bar";
        }
        $value .= ", ";
    }

    echo "{$value} ";
}
