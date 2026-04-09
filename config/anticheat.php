<?php

return [
    'event_points' => [
        'face_missing'    => 10,
        'multiple_faces'  => 20,
        'looking_away'    => 5,
        'phone_detected'  => 25,
        'tab_switch'      => 15,
    ],
    'event_labels' => [
        'face_missing'    => 'Face Not Detected',
        'multiple_faces'  => 'Multiple Faces Detected',
        'looking_away'    => 'Looking Away',
        'phone_detected'  => 'Phone Detected',
        'tab_switch'      => 'Tab / Window Switch',
    ],
    'default_threshold' => 60,
];
