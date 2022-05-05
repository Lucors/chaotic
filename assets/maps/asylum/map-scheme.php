<?php
    $mapScheme = array(
        "theme" => array(
            "--color-substr-border"   => "#59ff9b",
            "--color-substr-shadow"   => "#59ff9b9e",
            "--color-body-gradient-1" => "#2bff9340"
        ),
        "background" => array(
            "chunks"    => true,
            "size"      => 5,
            "line"      => 5
        ),
        "nodes" => array(
            "start" => array(
                "x"     => 72,
                "y"     => 175,
                "next"  => 1,
                "type"  => 1
            ),

            "1" => array(
                "x"     => 226,
                "y"     => 91,
                "next"  => 2,
                "type"  => 2
            ),
            "2" => array(
                "x"     => 303,
                "y"     => 242,
                "next"  => 3,
                "type"  => 2
            ),
            "3" => array(
                "x"     => 397,
                "y"     => 96,
                "next"  => "f1",
                "type"  => 2
            ),
            "f1" => array(
                "x"     => 484,
                "y"     => 244,
                "next"  => 4,
                "alt"   => 6,
                "type"  => 6
            ),
            "4" => array(
                "x"     => 554,
                "y"     => 100,
                "next"  => 5,
                "type"  => 2
            ),
            "5" => array(
                "x"     => 688,
                "y"     => 99,
                "next"  => 7,
                "alt"   => 9,
                "type"  => 4
            ),
            "6" => array(
                "x"     => 628,
                "y"     => 266,
                "next"  => 7,
                "type"  => 2
            ),
            "7" => array(
                "x"     => 783,
                "y"     => 264,
                "next"  => 8,
                "type"  => 2
            ),
            "8" => array(
                "x"     => 824,
                "y"     => 100,
                "next"  => 9,
                "type"  => 2
            ),
            "9" => array(
                "x"     => 943,
                "y"     => 188,
                "next"  => 10,
                "type"  => 2
            ),
            "10" => array(
                "x"     => 1033,
                "y"     => 72,
                "next"  => "f2",
                "type"  => 2
            ),
            "f2" => array(
                "x"     => 1110,
                "y"     => 206,
                "next"  => 11,
                "alt"   => 13,
                "type"  => 6
            ),
            "11" => array(
                "x"     => 1228,
                "y"     => 83,
                "next"  => 12,
                "type"  => 2
            ),
            "12" => array(
                "x"     => 1391,
                "y"     => 75,
                "next"  => "finish",
                "alt"   => 13,
                "type"  => 4
            ),
            "13" => array(
                "x"     => 1248,
                "y"     => 267,
                "next"  => 14,
                "type"  => 2
            ),
            "14" => array(
                "x"     => 1394,
                "y"     => 265,
                "next"  => "finish",
                "alt"   => 11,
                "type"  => 4
            ),
            "finish" => array(
                "x"     => 1581,
                "y"     => 170,
                "type"  => 3
            )
        )
    );
?>