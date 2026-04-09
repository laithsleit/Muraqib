<?php

return [
    'event_points' => [
        'face_missing'    => 10,
        'multiple_faces'  => 20,
        'looking_away'    => 5,
        'face_changed'    => 30,
        'phone_detected'  => 25,
        'tab_switch'      => 15,
    ],
    'event_labels' => [
        'face_missing'    => 'Face Not Detected',
        'multiple_faces'  => 'Multiple Faces Detected',
        'looking_away'    => 'Looking Away',
        'face_changed'    => 'Different Face Detected',
        'phone_detected'  => 'Phone Detected',
        'tab_switch'      => 'Tab / Window Switch',
    ],
    'event_descriptions' => [
        'face_missing'    => "Student's face is not visible in the camera",
        'multiple_faces'  => 'More than one person is visible in the frame',
        'looking_away'    => 'Student appears to be looking off-screen',
        'face_changed'    => 'A different person appeared in front of the camera',
        'phone_detected'  => 'A phone or external device is detected',
        'tab_switch'      => 'Student navigated away from the quiz tab',
    ],
    'screenshot_disk' => 'anticheat',
    'default_threshold' => 60,
];
