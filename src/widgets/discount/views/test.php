<?php
[
    "type"       => "group",
    "condition"  => "equal",
    "rules_type" => "and",
    "rules"      => [
        [
            "type"      => "rule",
            "condition" => "equal",
            "field"     => "element.tree",
            "value"     => "1432",
        ],
        [
            "type"      => "rule",
            "condition" => "equal",
            "field"     => "element.name",
            "value"     => "Спальни",
        ],
        [
            "type"       => "group",
            "condition"  => "equal",
            "rules_type" => "and",
            "rules"      => [
                [
                    "type"      => "rule",
                    "condition" => "equal",
                    "field"     => "element.name",
                    "value"     => "Спальни",
                ],
            ],
        ],
    ],
];
