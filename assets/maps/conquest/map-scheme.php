<?php
    $mapScheme = array(
        "theme" => array(
            "--color-substr-border"   => "#6359ff",
            "--color-substr-shadow"   => "#6359ff9e",
            "--color-body-gradient-1" => "#552bff40"
        ),
        "background" => array(
            "chunks"    => true,
            "size"      => 6,
            "line"      => 3
        ),
        "nodes" => array(
            "start" => array(
                "x"     => 79,
                "y"     => 643,
                "next"  => 1,
                "type"  => 1
            ),
            "1" => array(
                "x"     => 220,
                "y"     => 596,
                "next"  => 2,
                "type"  => 2
            ),
            "2" => array(
                "x"     => 365,
                "y"     => 520,
                "next"  => 3,
                "alt"   => 7,
                "type"  => 6
            ),
            "3" => array(
                "x"     => 514,
                "y"     => 609,
                "next"  => 4,
                "alt"   => 4,
                "type"  => 4
            ),
            "4" => array(
                "x"     => 721,
                "y"     => 569,
                "next"  => 5,
                "type"  => 2
            ),
            "5" => array(
                "x"     => 864,
                "y"     => 441,
                "next"  => 6,
                "type"  => 2
            ),
            "6" => array(
                "x"     => 960,
                "y"     => 276,
                "next"  => "finish",
                "alt"   => "prison",
                "type"  => 4
            ),
            "7" => array(
                "x"     => 273,
                "y"     => 386,
                "next"  => 8,
                "type"  => 2
            ),
            "8" => array(
                "x"     => 337,
                "y"     => 225,
                "next"  => 9,
                "type"  => 2
            ),
            "9" => array(
                "x"     => 491,
                "y"     => 128,
                "next"  => 10,
                "type"  => 2
            ),
            "10" => array(
                "x"     => 668,
                "y"     => 105,
                "next"  => 11,
                "alt"   => 7,
                "type"  => 4
            ),
            "11" => array(
                "x"     => 823,
                "y"     => 145,
                "next"  => "finish",
                "type"  => 2
            ),
            "finish" => array(
                "x"     => 983,
                "y"     => 84,
                "type"  => 3
            ),
            "prison" => array(
                "x"     => 80,
                "y"     => 89,
                "next"  => 6,
                "type"  => 5
            )
        )
    );
?>