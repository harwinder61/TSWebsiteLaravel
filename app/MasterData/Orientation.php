<?php

namespace App\MasterData;

class Orientation extends Master
{
   
    public static $data = [
        "PASSIVE" => [
            'label' => 'Passive',
            'value' => 'Passive'
        ],
        "ACTIVE" => [
            'label' => 'Active',
            'value' => 'Active'
        ],
        "VERSATILE" => [
            'label' => 'Versatile',
            'value' => 'Versatile'
        ]
        
    ];
}