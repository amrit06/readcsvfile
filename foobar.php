#!/bin/php


<?php
for ($i = 1; $i < 101; $i++) {
    $value = "{$i}, ";

    if ($i % 3 == 0 || $i % 5 == 0) {
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
