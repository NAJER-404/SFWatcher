<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use App\Models\Resource;
use App\Models\User;
use App\Models\WardStation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SpectralSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users (Admin, Investigator, Reporter)
        $admin = User::firstOrCreate(
            ['email' => 'admin@ectonet.gov'],
            [
                'name' => 'Commander R. Vance',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $warden = User::firstOrCreate(
            ['email' => 'warden@ectonet.gov'],
            [
                'name' => 'Warden M. Dagohoy',
                'password' => Hash::make('password'),
                'role' => 'investigator',
            ]
        );

        $investigator = User::firstOrCreate(
            ['email' => 'investigator@ectonet.gov'],
            [
                'name' => 'Lead Investigator S. Reyes',
                'password' => Hash::make('password'),
                'role' => 'investigator',
            ]
        );

        // Class D Responders (seeded FIRST — required for EndToEndWorkflowTest)
        User::firstOrCreate(
            ['email' => 'responder@ectonet.gov'],
            ['name' => 'Responder A. Santos', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'D', 'responder_status' => 'AVAILABLE']
        );
        User::firstOrCreate(
            ['email' => 'domingo.r@ectonet.gov'],
            ['name' => 'Ramon Domingo', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'D', 'responder_status' => 'AVAILABLE']
        );

        // Class C Responders
        User::firstOrCreate(
            ['email' => 'salazar.m@ectonet.gov'],
            ['name' => 'Maria Salazar', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'C', 'responder_status' => 'AVAILABLE']
        );
        User::firstOrCreate(
            ['email' => 'tuazon.j@ectonet.gov'],
            ['name' => 'Jose Tuazon', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'C', 'responder_status' => 'AVAILABLE']
        );

        // Class B Responders
        User::firstOrCreate(
            ['email' => 'cruz.m@ectonet.gov'],
            ['name' => 'Marc Cruz', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'B', 'responder_status' => 'AVAILABLE']
        );
        User::firstOrCreate(
            ['email' => 'mendoza.l@ectonet.gov'],
            ['name' => 'Liza Mendoza', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'B', 'responder_status' => 'AVAILABLE']
        );

        // Class A Responders
        User::firstOrCreate(
            ['email' => 'plaza.j@ectonet.gov'],
            ['name' => 'John Plaza', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'A', 'responder_status' => 'AVAILABLE']
        );
        User::firstOrCreate(
            ['email' => 'ramos.c@ectonet.gov'],
            ['name' => 'Clara Ramos', 'password' => Hash::make('password'), 'role' => 'responder', 'responder_class' => 'A', 'responder_status' => 'AVAILABLE']
        );

        $observer = User::firstOrCreate(
            ['email' => 'morales@ectonet.gov'],
            [
                'name' => 'Observer K. Morales',
                'password' => Hash::make('password'),
                'role' => 'reporter',
            ]
        );

        $civilian = User::firstOrCreate(
            ['email' => 'reporter@ectonet.gov'],
            [
                'name' => 'Field Scout J. Alcantara',
                'password' => Hash::make('password'),
                'role' => 'reporter',
            ]
        );

        // 2. Seed All 27 Official Barangays of San Francisco, Agusan del Sur
        $barangayData = [
            ['name' => 'Alegria',       'lat' => 8.4720, 'lng' => 125.9620],
            ['name' => 'Bayugan 2',     'lat' => 8.5520, 'lng' => 125.9380],
            ['name' => 'Bitan-agan',    'lat' => 8.4890, 'lng' => 125.9920],
            ['name' => 'Borbon',        'lat' => 8.5260, 'lng' => 125.9410],
            ['name' => 'Buenasuerte',   'lat' => 8.4610, 'lng' => 125.9810],
            ['name' => 'Caimpugan',     'lat' => 8.5710, 'lng' => 125.9120],
            ['name' => 'Das-agan',      'lat' => 8.5380, 'lng' => 125.9910],
            ['name' => 'Ebro',          'lat' => 8.4980, 'lng' => 125.9420],
            ['name' => 'Hubang',        'lat' => 8.5310, 'lng' => 125.9730],
            ['name' => 'Karaus',        'lat' => 8.5180, 'lng' => 125.9840],
            ['name' => 'Ladgadan',      'lat' => 8.5020, 'lng' => 125.9540],
            ['name' => 'Lapinigan',     'lat' => 8.4420, 'lng' => 125.9680],
            ['name' => 'Lucac',         'lat' => 8.5290, 'lng' => 125.9980],
            ['name' => 'Mate',          'lat' => 8.4780, 'lng' => 125.9320],
            ['name' => 'New Visayas',   'lat' => 8.5440, 'lng' => 125.9580],
            ['name' => 'Ormaca',        'lat' => 8.4550, 'lng' => 125.9490],
            ['name' => 'Pasta',         'lat' => 8.4830, 'lng' => 125.9730],
            ['name' => 'Pisa-an',       'lat' => 8.5025, 'lng' => 125.9782],
            ['name' => 'Barangay 1',    'lat' => 8.5098, 'lng' => 125.9780],
            ['name' => 'Barangay 2',    'lat' => 8.5085, 'lng' => 125.9760],
            ['name' => 'Barangay 3',    'lat' => 8.5070, 'lng' => 125.9775],
            ['name' => 'Barangay 4',    'lat' => 8.5055, 'lng' => 125.9790],
            ['name' => 'Barangay 5',    'lat' => 8.5065, 'lng' => 125.9790],
            ['name' => 'Rizal',         'lat' => 8.5340, 'lng' => 125.9280],
            ['name' => 'San Isidro',    'lat' => 8.4910, 'lng' => 125.9610],
            ['name' => 'Santa Ana',     'lat' => 8.5150, 'lng' => 125.9520],
            ['name' => 'Tagapua',       'lat' => 8.5630, 'lng' => 125.9450],
        ];

        $barangayMap = [];
        foreach ($barangayData as $b) {
            $record = Barangay::updateOrCreate(
                ['name' => $b['name'], 'municipality' => 'San Francisco'],
                [
                    'province'  => 'Agusan del Sur',
                    'latitude'  => $b['lat'],
                    'longitude' => $b['lng'],
                ]
            );
            $barangayMap[$b['name']] = $record->id;
        }

        // 3. Seed Realistic Fictional Incidents
        $incidentsData = [
            [
                'incident_code' => 'SF-INC-001',
                'reported_by'   => $observer->id,
                'barangay_id'   => $barangayMap['Hubang'] ?? null,
                'incident_type' => 'Ectoplasmic Anomaly',
                'title'         => 'Class-3 Ectoplasmic Surge near Hubang Terminal',
                'description'   => 'High concentration of luminescence and fluctuating electromagnetic readings detected near the Integrated Bus Terminal diversion corridor. Local sensors report thermal drop of 8°C.',
                'latitude'      => 8.5318,
                'longitude'     => 125.9725,
                'incident_date' => now()->subHours(4),
                'severity'      => 'HIGH',
                'status'        => 'UNDER INVESTIGATION',
                'notes'         => 'Ward containment pylon #04 dispatched to dampen local resonance.',
                'evidence_url'  => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'incident_code' => 'SF-INC-002',
                'reported_by'   => $civilian->id,
                'barangay_id'   => $barangayMap['Karaus'] ?? null,
                'incident_type' => 'Spectral Rift',
                'title'         => 'Localized Planar Fracture at Karaus Creek',
                'description'   => 'Visible planar distortion with low-frequency psychic hum. Multiple witnesses reported spectral wisps emerging along the river embankment.',
                'latitude'      => 8.5185,
                'longitude'     => 125.9845,
                'incident_date' => now()->subHours(2),
                'severity'      => 'CRITICAL',
                'status'        => 'PENDING',
                'notes'         => 'Pending lead investigator arrival. Perimeter ward requested.',
                'evidence_url'  => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'incident_code' => 'SF-INC-003',
                'reported_by'   => $observer->id,
                'barangay_id'   => $barangayMap['Barangay 2'] ?? null,
                'incident_type' => 'Poltergeist Disturbance',
                'title'         => 'Kinetic Disturbance in Commercial Center',
                'description'   => 'Intermittent telekinetic movement of unsecured inventory reported after twilight. Minimal structural damage, no civilian injuries.',
                'latitude'      => 8.5085,
                'longitude'     => 125.9760,
                'incident_date' => now()->subHours(8),
                'severity'      => 'MEDIUM',
                'status'        => 'VERIFIED',
                'notes'         => 'Investigator confirmed residual poltergeist frequency. Stabilizer planted.',
                'evidence_url'  => 'https://images.unsplash.com/photo-1508739773434-c26b3d09e071?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'incident_code' => 'SF-INC-004',
                'reported_by'   => $warden->id,
                'barangay_id'   => $barangayMap['Hubang'] ?? null,
                'incident_type' => 'Ward Failure',
                'title'         => 'Micro-Fracture on Pylon Alpha-2',
                'description'   => 'Primary containment boundary at Gaisano junction experienced a 12% drop in spirit ward integrity. Arcane seal requires recalibration.',
                'latitude'      => 8.5135,
                'longitude'     => 125.9815,
                'incident_date' => now()->subHours(10),
                'severity'      => 'HIGH',
                'status'        => 'UNDER INVESTIGATION',
                'notes'         => 'Ward technician assigned. Estimated seal repair in 2 hours.',
                'evidence_url'  => null,
            ],
            [
                'incident_code' => 'SF-INC-005',
                'reported_by'   => $civilian->id,
                'barangay_id'   => $barangayMap['Pisa-an'] ?? null,
                'incident_type' => 'Spirit Activity',
                'title'         => 'Historic Shadow Echo at Pisa-an',
                'description'   => 'Recurring temporal-spectral projection observed near the old highway crossing. Non-hostile class-1 remnant.',
                'latitude'      => 8.5020,
                'longitude'     => 125.9780,
                'incident_date' => now()->subDay(),
                'severity'      => 'LOW',
                'status'        => 'RESOLVED',
                'notes'         => 'Residual energy naturally dissipated. Area cleared.',
                'evidence_url'  => null,
            ],
            [
                'incident_code' => 'SF-INC-006',
                'reported_by'   => $observer->id,
                'barangay_id'   => $barangayMap['Caimpugan'] ?? null,
                'incident_type' => 'Unknown Phenomenon',
                'title'         => 'Unclassified Entity Sighting at Patin-ay Forest Edge',
                'description'   => 'Semi-corporeal spectral form tracked moving northwest towards the Provincial Capitol greenway. Ward grid triggered warning alarm.',
                'latitude'      => 8.5710,
                'longitude'     => 125.9120,
                'incident_date' => now()->subHours(1),
                'severity'      => 'HIGH',
                'status'        => 'VERIFIED',
                'notes'         => 'Surveillance drone dispatched for spectral signature scanning.',
                'evidence_url'  => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'incident_code' => 'SF-INC-007',
                'reported_by'   => $civilian->id,
                'barangay_id'   => $barangayMap['Ladgadan'] ?? null,
                'incident_type' => 'Spectral Residue',
                'title'         => 'Viscous Residue Pools near Ladgadan Waterway',
                'description'   => 'Emerald glowing ectoplasmic discharge discovered on irrigation gates. Low radiation, high magical resonance.',
                'latitude'      => 8.5015,
                'longitude'     => 125.9535,
                'incident_date' => now()->subHours(12),
                'severity'      => 'MEDIUM',
                'status'        => 'VERIFIED',
                'notes'         => 'Extraction team collected 4.2 liters for containment storage.',
                'evidence_url'  => null,
            ],
        ];

        foreach ($incidentsData as $data) {
            $evidenceUrl = $data['evidence_url'] ?? null;
            unset($data['evidence_url']);

            $incident = Incident::updateOrCreate(
                ['incident_code' => $data['incident_code']],
                $data
            );

            if ($evidenceUrl) {
                IncidentEvidence::create([
                    'incident_id'  => $incident->id,
                    'file_path'    => $evidenceUrl,
                    'file_name'    => 'spectral_scan_capture.jpg',
                    'file_type'    => 'image/jpeg',
                    'description'  => 'Sensor snapshot of anomaly',
                    'uploaded_by'  => $incident->reported_by,
                ]);
            }

            // Create initial investigation note
            if ($incident->status !== 'PENDING') {
                Investigation::create([
                    'incident_id'        => $incident->id,
                    'investigator_id'    => $warden->id,
                    'notes'              => $incident->notes ?? 'Initial field assessment logged.',
                    'investigation_date' => now()->subMinutes(30),
                    'result'             => $incident->status,
                ]);
            }
        }

        // 4. Seed Spirit Ward Stations
        $wardData = [
            [
                'code'              => 'WS-001',
                'name'              => 'Hubang Master Spirit Ward Station',
                'barangay_id'       => $barangayMap['Hubang'] ?? null,
                'latitude'          => 8.5310,
                'longitude'         => 125.9730,
                'status'            => 'active',
                'shield_level'      => 96,
                'energy_level'      => 94,
                'frequency'         => '432.8 THz',
                'radius_meters'     => 1400,
                'last_recalibrated' => now()->subDays(2),
            ],
            [
                'code'              => 'WS-002',
                'name'              => 'San Franz Town Center Ward Array',
                'barangay_id'       => $barangayMap['Barangay 2'] ?? null,
                'latitude'          => 8.5085,
                'longitude'         => 125.9760,
                'status'            => 'active',
                'shield_level'      => 92,
                'energy_level'      => 90,
                'frequency'         => '428.1 THz',
                'radius_meters'     => 1200,
                'last_recalibrated' => now()->subDay(),
            ],
            [
                'code'              => 'WS-003',
                'name'              => 'ASSCAT Campus Containment Pylon',
                'barangay_id'       => $barangayMap['Karaus'] ?? null,
                'latitude'          => 8.5180,
                'longitude'         => 125.9840,
                'status'            => 'degraded',
                'shield_level'      => 64,
                'energy_level'      => 55,
                'frequency'         => '419.5 THz',
                'radius_meters'     => 950,
                'last_recalibrated' => now()->subDays(8),
            ],
            [
                'code'              => 'WS-004',
                'name'              => 'Doctors Hospital Sanctuary Ward',
                'barangay_id'       => $barangayMap['Hubang'] ?? null,
                'latitude'          => 8.5040,
                'longitude'         => 125.9830,
                'status'            => 'active',
                'shield_level'      => 99,
                'energy_level'      => 98,
                'frequency'         => '440.0 THz',
                'radius_meters'     => 800,
                'last_recalibrated' => now(),
            ],
            [
                'code'              => 'WS-005',
                'name'              => 'Provincial Capitol Perimeter Ward',
                'barangay_id'       => $barangayMap['Caimpugan'] ?? null,
                'latitude'          => 8.5710,
                'longitude'         => 125.9120,
                'status'            => 'active',
                'shield_level'      => 88,
                'energy_level'      => 85,
                'frequency'         => '435.2 THz',
                'radius_meters'     => 1600,
                'last_recalibrated' => now()->subDays(3),
            ],
        ];

        foreach ($wardData as $w) {
            WardStation::updateOrCreate(['code' => $w['code']], $w);
        }

        // 5. Seed Spectral Resources
        $resourceData = [
            [
                'name'          => 'Hubang Ectoplasm Well #1',
                'resource_type' => 'Ectoplasmic Reservoir',
                'quantity'      => 280.50,
                'unit'          => 'L',
                'purity'        => '94%',
                'yield_rate'    => '18.5 L/hr',
                'barangay_id'   => $barangayMap['Hubang'] ?? null,
                'latitude'      => 8.5340,
                'longitude'     => 125.9710,
                'status'        => 'available',
            ],
            [
                'name'          => 'Karaus Resonant Crystal Pocket',
                'resource_type' => 'Spirit Residue Node',
                'quantity'      => 42.00,
                'unit'          => 'kg',
                'purity'        => '88%',
                'yield_rate'    => '4.2 kg/day',
                'barangay_id'   => $barangayMap['Karaus'] ?? null,
                'latitude'      => 8.5200,
                'longitude'     => 125.9860,
                'status'        => 'available',
            ],
            [
                'name'          => 'Borbon Heavy Anchor Bed',
                'resource_type' => 'Soul Anchor Deposit',
                'quantity'      => 12.00,
                'unit'          => 'units',
                'purity'        => '99%',
                'yield_rate'    => '12 units',
                'barangay_id'   => $barangayMap['Borbon'] ?? null,
                'latitude'      => 8.5260,
                'longitude'     => 125.9410,
                'status'        => 'available',
            ],
            [
                'name'          => 'Pisa-an Subterranean Stream',
                'resource_type' => 'Ectoplasmic Reservoir',
                'quantity'      => 115.00,
                'unit'          => 'L',
                'purity'        => '91%',
                'yield_rate'    => '11.0 L/hr',
                'barangay_id'   => $barangayMap['Pisa-an'] ?? null,
                'latitude'      => 8.4980,
                'longitude'     => 125.9750,
                'status'        => 'available',
            ],
        ];

        foreach ($resourceData as $r) {
            Resource::updateOrCreate(['name' => $r['name']], $r);
        }

        // 6. Seed Equipment Catalog
        $equipmentData = [
            [
                'name'        => 'Aegis-IV Spirit Ward Generator',
                'description' => 'Industrial-grade harmonic ward generator capable of maintaining a 1.2km containment field at 432 THz.',
                'category'    => 'Ward Devices',
                'price'       => 45000.00,
                'stock'       => 8,
                'status'      => 'in_stock',
                'image'       => null,
            ],
            [
                'name'        => 'Heavy-Grade Soul Anchor Mark III',
                'description' => 'Dense lead-tungsten alloy stabilizer designed to tether planar disturbances and prevent ethereal manifestation.',
                'category'    => 'Containment Units',
                'price'       => 18500.00,
                'stock'       => 15,
                'status'      => 'in_stock',
                'image'       => null,
            ],
            [
                'name'        => 'Ecto-Spectrometric Field Scanner',
                'description' => 'High-precision handheld scanner for measuring localized ectoplasmic density and spirit residue purity.',
                'category'    => 'Spectral Equipment',
                'price'       => 32000.00,
                'stock'       => 12,
                'status'      => 'in_stock',
                'image'       => null,
            ],
            [
                'name'        => 'Portable Ghost Containment Vessel',
                'description' => 'Reinforced vacuum-sealed cylinder with triple spirit ward lining for transport of active entities.',
                'category'    => 'Containment Units',
                'price'       => 24000.00,
                'stock'       => 20,
                'status'      => 'in_stock',
                'image'       => null,
            ],
            [
                'name'        => 'Electromagnetic Resonance Detector (EMF-7)',
                'description' => 'Multi-band spirit frequency detector with audio-visual telemetry feedback and GPS logging.',
                'category'    => 'Ghost-Hunting Gadgets',
                'price'       => 8500.00,
                'stock'       => 35,
                'status'      => 'in_stock',
                'image'       => null,
            ],
        ];

        foreach ($equipmentData as $eq) {
            Equipment::updateOrCreate(['name' => $eq['name']], $eq);
        }
    }
}
