<?php

return [
    'duration_minutes' => [
        'LOW' => 5,
        'MEDIUM' => 6,
        'HIGH' => 7,
        'EXTREME' => 7,
        'CRITICAL' => 8,
    ],
    'duration_seconds' => [
        'LOW' => 294,      // 50 HP / 0.17 HP/s = ~4:54
        'MEDIUM' => 320,   // 80 HP / 0.25 HP/s = ~5:20
        'HIGH' => 367,     // 110 HP / 0.30 HP/s = ~6:07
        'EXTREME' => 371,  // 130 HP / 0.35 HP/s = ~6:11
        'CRITICAL' => 429, // 150 HP / 0.35 HP/s = ~7:09
    ],
    'dps' => [
        'LOW' => 0.17,
        'MEDIUM' => 0.25,
        'HIGH' => 0.30,
        'EXTREME' => 0.35,
        'CRITICAL' => 0.35,
    ],
    'responder_damage_pct' => [
        'LOW' => 0.10,      // Max ~10% HP loss over entire containment
        'MEDIUM' => 0.15,   // Max ~15% HP loss
        'HIGH' => 0.22,     // Max ~22% HP loss
        'EXTREME' => 0.28,  // Max ~28% HP loss
        'CRITICAL' => 0.30, // Max ~30% HP loss
    ],
    'class_mitigation' => [
        'D' => 1.00,
        'C' => 0.85,
        'B' => 0.70,
        'A' => 0.55,
    ],
    'classes' => [
        'D' => ['responder_hp' => 100, 'investigator_hp' => 100, 'duration_multiplier' => 1.00, 'effectiveness' => 10, 'promotion_xp' => 100],
        'C' => ['responder_hp' => 120, 'investigator_hp' => 120, 'duration_multiplier' => 0.90, 'effectiveness' => 12, 'promotion_xp' => 250],
        'B' => ['responder_hp' => 145, 'investigator_hp' => 145, 'duration_multiplier' => 0.80, 'effectiveness' => 15, 'promotion_xp' => 500],
        'A' => ['responder_hp' => 170, 'investigator_hp' => 170, 'duration_multiplier' => 0.70, 'effectiveness' => 18, 'promotion_xp' => null],
    ],
    'anomaly_hp' => [
        'LOW' => 50,
        'MEDIUM' => 80,
        'HIGH' => 110,
        'EXTREME' => 130,
        'CRITICAL' => 150,
    ],
    'severity_eligibility' => [
        'LOW' => ['D', 'C', 'B', 'A'],
        'MEDIUM' => ['D', 'C', 'B', 'A'],
        'HIGH' => ['C', 'B', 'A'],
        'EXTREME' => ['A'],
        'CRITICAL' => ['A'],
    ],
    'required_minimum_class' => [
        'LOW' => 'D',
        'MEDIUM' => 'D',
        'HIGH' => 'C',
        'EXTREME' => 'A',
        'CRITICAL' => 'A',
    ],
    'xp' => ['investigation_completed' => 15, 'response_resolved' => 50],
];
