<?php

return [
    'duration_minutes' => [
        'LOW' => 5,
        'MEDIUM' => 10,
        'HIGH' => 20,
        'CRITICAL' => 30,
    ],
    'classes' => [
        'D' => ['responder_hp' => 100, 'investigator_hp' => 100, 'duration_multiplier' => 1.00, 'effectiveness' => 10, 'promotion_xp' => 100],
        'C' => ['responder_hp' => 115, 'investigator_hp' => 115, 'duration_multiplier' => 0.90, 'effectiveness' => 12, 'promotion_xp' => 250],
        'B' => ['responder_hp' => 130, 'investigator_hp' => 130, 'duration_multiplier' => 0.80, 'effectiveness' => 15, 'promotion_xp' => 500],
        'A' => ['responder_hp' => 170, 'investigator_hp' => 170, 'duration_multiplier' => 0.70, 'effectiveness' => 18, 'promotion_xp' => null],
    ],
    'anomaly_hp' => ['LOW' => 30, 'MEDIUM' => 60, 'HIGH' => 100, 'CRITICAL' => 150],
    'xp' => ['investigation_completed' => 15, 'response_resolved' => 50],
];
