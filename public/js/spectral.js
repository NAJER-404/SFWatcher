/**
 * ECTO//NET — Spectral Resource & Ward Network GIS Engine
 * Scenario 3: Enterprise Geographic Supernatural Defense & Emergency Monitoring
 * Primary Scope: San Francisco, Agusan del Sur, Philippines
 */

'use strict';

// ════════════════════════════════════════════════════════════════════════════
// 1. DATA STORE (Synchronized with Laravel Backend & Initial State)
// ════════════════════════════════════════════════════════════════════════════

const SpectralData = {
    node: {
        name: "San Francisco Node",
        sector: "Agusan del Sur",
        province: "Agusan del Sur",
        country: "Philippines",
        coordinates: [8.5100, 125.9750],
        status: "OPERATIONAL"
    },

    barangays: [
        { id: 1, name: "Alegria", lat: 8.5060, lng: 126.0090 },
        { id: 2, name: "Bayugan 2", lat: 8.4680, lng: 125.9490 },
        { id: 3, name: "Bitan-agan", lat: 8.4410, lng: 125.9680 },
        { id: 4, name: "Borbon", lat: 8.4090, lng: 125.9320 },
        { id: 5, name: "Buenasuerte", lat: 8.4115, lng: 125.8820 },
        { id: 6, name: "Caimpugan", lat: 8.3934, lng: 125.9149 },
        { id: 7, name: "Das-agan", lat: 8.5461, lng: 126.0135 },
        { id: 8, name: "Ebro", lat: 8.4460, lng: 125.9379 },
        { id: 9, name: "Hubang", lat: 8.5285, lng: 126.0150 },
        { id: 10, name: "Karaus", lat: 8.4910, lng: 126.0145 },
        { id: 11, name: "Ladgadan", lat: 8.4896, lng: 125.9332 },
        { id: 12, name: "Lapinigan", lat: 8.4720, lng: 125.9770 },
        { id: 13, name: "Lucac", lat: 8.5614, lng: 125.9620 },
        { id: 14, name: "Mate", lat: 8.4455, lng: 126.0180 },
        { id: 15, name: "New Visayas", lat: 8.4390, lng: 125.8995 },
        { id: 16, name: "Ormaca", lat: 8.4474, lng: 125.9935 },
        { id: 17, name: "Pasta", lat: 8.4089, lng: 125.9841 },
        { id: 18, name: "Pisa-an", lat: 8.4230, lng: 125.9645 },
        { id: 19, name: "Barangay 1", lat: 8.5132, lng: 125.9765 },
        { id: 20, name: "Barangay 2", lat: 8.5086, lng: 125.9811 },
        { id: 21, name: "Barangay 3", lat: 8.5110, lng: 125.9720 },
        { id: 22, name: "Barangay 4", lat: 8.5065, lng: 125.9735 },
        { id: 23, name: "Barangay 5", lat: 8.5029, lng: 125.9781 },
        { id: 24, name: "Rizal", lat: 8.4883, lng: 125.8711 },
        { id: 25, name: "San Isidro", lat: 8.4710, lng: 126.0080 },
        { id: 26, name: "Santa Ana", lat: 8.4620, lng: 125.8940 },
        { id: 27, name: "Tagapua", lat: 8.5630, lng: 126.0010 }
    ],

    incidents: [],
    wardStations: [],
    safeZones: [
        { id: "SAFE-01", name: "San Francisco Municipal Gymnasium Sanctuary", barangay: "Barangay 1", latitude: 8.5098, longitude: 125.9780, radius: 450, status: "active" },
        { id: "SAFE-02", name: "Hubang Transport Safe Haven", barangay: "Hubang", latitude: 8.5310, longitude: 125.9730, radius: 550, status: "active" }
    ],

    checkSafeZoneProtection(lat, lng) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (isNaN(lat) || isNaN(lng)) return null;

        const zones = (this.safeZones && this.safeZones.length > 0)
            ? this.safeZones
            : [
                { id: "SAFE-01", name: "San Francisco Municipal Gymnasium Sanctuary", latitude: 8.5098, longitude: 125.9780, radius: 450, status: "active" },
                { id: "SAFE-02", name: "Hubang Transport Safe Haven", latitude: 8.5310, longitude: 125.9730, radius: 550, status: "active" }
            ];

        for (let i = 0; i < zones.length; i++) {
            const sz = zones[i];
            if (sz.status && sz.status !== 'active') continue;
            const szLat = parseFloat(sz.latitude || sz.lat);
            const szLng = parseFloat(sz.longitude || sz.lng);
            const radius = parseFloat(sz.radius || 450);
            if (isNaN(szLat) || isNaN(szLng) || isNaN(radius)) continue;

            const dLat = (szLat - lat) * Math.PI / 180;
            const dLng = (szLng - lng) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat * Math.PI / 180) * Math.cos(szLat * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const dist = 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            // Condition: distance <= radius (including boundary distance == radius)
            if (dist <= radius) {
                return {
                    protected: true,
                    safeZone: sz,
                    distance: dist,
                    radius: radius,
                    title: 'Safe Zone Protected',
                    message: `You cannot report an incident within ${Math.round(radius)}m of ${sz.name}. This Safe Zone is protected by Investigators and Responders. Please select a location outside the Safe Zone.`
                };
            }
        }
        return null;
    },

    findClosestBarangay(lat, lng) {
        if (!this.barangays || this.barangays.length === 0) return null;
        let closest = null;
        let minDist = Infinity;
        this.barangays.forEach(b => {
            const bLat = parseFloat(b.latitude || b.lat);
            const bLng = parseFloat(b.longitude || b.lng);
            if (isNaN(bLat) || isNaN(bLng)) return;
            const d = (bLat - lat) ** 2 + (bLng - lng) ** 2;
            if (d < minDist) {
                minDist = d;
                closest = b;
            }
        });
        return closest;
    },

    _initialized: false,

    init() {
        if (this._initialized) return;
        this._initialized = true;
        if (window.INITIAL_SPECTRAL_STATE) {
            if (window.INITIAL_SPECTRAL_STATE.barangays && window.INITIAL_SPECTRAL_STATE.barangays.length > 0) {
                this.barangays = window.INITIAL_SPECTRAL_STATE.barangays.map(b => ({
                    id: b.id,
                    name: b.name,
                    lat: parseFloat(b.latitude || b.lat),
                    lng: parseFloat(b.longitude || b.lng)
                }));
            }
            if (window.INITIAL_SPECTRAL_STATE.incidents && window.INITIAL_SPECTRAL_STATE.incidents.length > 0) {
                this.incidents = window.INITIAL_SPECTRAL_STATE.incidents.map(inc => {
                    let reportedAt = inc.incident_date || inc.created_at;
                    try {
                        const d = new Date(reportedAt);
                        reportedAt = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                            + ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    } catch (_) { }

                    const lat = parseFloat(inc.latitude);
                    const lng = parseFloat(inc.longitude);
                    let bName = inc.barangay ? inc.barangay.name : null;
                    if (!bName && !isNaN(lat) && !isNaN(lng)) {
                        const closest = this.findClosestBarangay(lat, lng);
                        if (closest) bName = closest.name;
                    }

                    return {
                        id: inc.incident_code || `SF-INC-${inc.id}`,
                        incident_code: inc.incident_code || `SF-INC-${inc.id}`,
                        db_id: inc.id,
                        type: inc.incident_type,
                        title: inc.title,
                        description: inc.description,
                        barangay: bName || 'Hubang',
                        municipality: inc.barangay ? (inc.barangay.municipality || 'San Francisco') : 'San Francisco',
                        province: inc.barangay ? (inc.barangay.province || 'Agusan del Sur') : 'Agusan del Sur',
                        latitude: lat,
                        longitude: lng,
                        severity: inc.severity,
                        status: inc.status,
                        anomaly_hp: inc.anomaly_hp,
                        anomaly_max_hp: inc.anomaly_max_hp,
                        response_progress: inc.response_progress,
                        response_status: inc.response_status,
                        responder_assignments: inc.responder_assignments || [],
                        reported_at: reportedAt,
                        reported_by: inc.reporter ? inc.reporter.name : 'Civilian Observer',
                        evidence: (inc.evidence && inc.evidence.length > 0 && inc.evidence[0] && inc.evidence[0].file_path)
                            ? (String(inc.evidence[0].file_path).startsWith('http')
                                ? inc.evidence[0].file_path
                                : '/storage/' + inc.evidence[0].file_path)
                            : null,
                        investigations: inc.investigations || [],
                        notes: inc.notes,
                        real_address: null
                    };
                });
            }
            if (window.INITIAL_SPECTRAL_STATE.wards && window.INITIAL_SPECTRAL_STATE.wards.length > 0) {
                this.wardStations = window.INITIAL_SPECTRAL_STATE.wards.map(w => ({
                    id: w.code || `WS-${w.id}`,
                    code: w.code,
                    name: w.name,
                    barangay: w.barangay ? (w.barangay.name || w.barangay) : 'San Francisco',
                    latitude: parseFloat(w.latitude),
                    longitude: parseFloat(w.longitude),
                    power: w.energy_level || 90,
                    shield_level: w.shield_level || 95,
                    radius_meters: w.radius_meters || 1000,
                    status: w.status || 'active',
                    frequency: w.frequency || '432.8 THz'
                }));
            } else {
                this.wardStations = [];
            }
            // 🌐 Safe zones come from the DB (via DashboardController -> safeWardStations)
            if (window.INITIAL_SPECTRAL_STATE.safeWardStations && window.INITIAL_SPECTRAL_STATE.safeWardStations.length > 0) {
                this.safeZones = window.INITIAL_SPECTRAL_STATE.safeWardStations.map(sz => ({
                    id: sz.id ?? sz.name,
                    code: sz.code ?? null,
                    name: sz.name,
                    barangay: sz.barangay || 'San Francisco',
                    latitude: parseFloat(sz.lat ?? sz.latitude),
                    longitude: parseFloat(sz.lng ?? sz.longitude),
                    radius: parseFloat(sz.radius) || 450,
                    status: sz.status || 'active'
                }));
            } else {
                // Legacy fallback (only used if DB returns nothing)
                this.safeZones = [
                    { id: "SAFE-01", name: "San Francisco Municipal Gymnasium Sanctuary", barangay: "Barangay 1", latitude: 8.5098, longitude: 125.9780, radius: 450, status: "active" },
                    { id: "SAFE-02", name: "Hubang Transport Safe Haven", barangay: "Hubang", latitude: 8.5310, longitude: 125.9730, radius: 550, status: "active" }
                ];
            }
            if (window.INITIAL_SPECTRAL_STATE.resources && window.INITIAL_SPECTRAL_STATE.resources.length > 0) {
                this.spectralResources = window.INITIAL_SPECTRAL_STATE.resources.map(r => ({
                    id: `RES-${r.id}`,
                    name: r.name,
                    type: r.resource_type,
                    quantity: r.quantity,
                    unit: r.unit,
                    purity: r.purity,
                    yield_rate: r.yield_rate,
                    barangay: r.barangay ? r.barangay.name : 'San Francisco',
                    latitude: parseFloat(r.latitude),
                    longitude: parseFloat(r.longitude),
                    status: r.status,
                    icon: 'resource'
                }));
            }

            if (window.SpectralMap && window.SpectralMap.map) {
                try {
                    window.SpectralMap.renderAllLayers();
                    window.SpectralMap.updateLayerToggleButtons();
                } catch (_) { }
            }
        }
    }
};


// ════════════════════════════════════════════════════════════════════════════
// 1B. STREET VIEW 360 PANORAMA ENGINE (Interactive Left/Right Road Watcher)
// ════════════════════════════════════════════════════════════════════════════
const StreetView360 = {
    canvas: null,
    ctx: null,
    yaw: 172,
    targetYaw: 172,
    pitch: 0,
    targetPitch: 0,
    isDragging: false,
    lastX: 0,
    lastY: 0,
    roadName: 'Davao-Agusan National Hwy',
    barangay: 'Hubang',
    lat: 8.5100,
    lng: 125.9750,
    animId: null,
    initialized: false,

    init() {
        this.canvas = document.getElementById('streetview-canvas');
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        if (!this.initialized) {
            this.bindEvents();
            this.initialized = true;
        }
        this.startLoop();
    },

    bindEvents() {
        if (!this.canvas) return;

        const onDown = (clientX, clientY) => {
            this.isDragging = true;
            this.lastX = clientX;
            this.lastY = clientY;
            const hint = document.getElementById('sv-drag-hint');
            if (hint) hint.style.opacity = '0';
        };

        const onMove = (clientX, clientY) => {
            if (!this.isDragging) return;
            const dx = clientX - this.lastX;
            const dy = clientY - this.lastY;
            this.lastX = clientX;
            this.lastY = clientY;

            this.targetYaw = (this.targetYaw - dx * 0.45) % 360;
            if (this.targetYaw < 0) this.targetYaw += 360;
            this.targetPitch = Math.max(-25, Math.min(25, this.targetPitch + dy * 0.25));
        };

        const onUp = () => {
            this.isDragging = false;
        };

        this.canvas.addEventListener('mousedown', (e) => onDown(e.clientX, e.clientY));
        window.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY));
        window.addEventListener('mouseup', onUp);

        this.canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) onDown(e.touches[0].clientX, e.touches[0].clientY);
        }, { passive: true });
        window.addEventListener('touchmove', (e) => {
            if (e.touches.length === 1) onMove(e.touches[0].clientX, e.touches[0].clientY);
        }, { passive: true });
        window.addEventListener('touchend', onUp);
    },

    panBy(deg) {
        this.targetYaw = (this.targetYaw + deg) % 360;
        if (this.targetYaw < 0) this.targetYaw += 360;
        const hint = document.getElementById('sv-drag-hint');
        if (hint) hint.style.opacity = '0';
    },

    resetNorth() {
        this.targetYaw = 0;
        this.targetPitch = 0;
        const hint = document.getElementById('sv-drag-hint');
        if (hint) hint.style.opacity = '0';
    },

    setLocation(lat, lng, roadName, barangay) {
        this.lat = lat;
        this.lng = lng;
        this.roadName = roadName || 'Davao-Agusan National Hwy';
        this.barangay = barangay || 'Hubang';
        this.targetYaw = Math.floor(((lat * 1000 + lng * 1000) % 180) + 90);
    },

    startLoop() {
        if (this.animId) return;
        const loop = () => {
            this.update();
            this.render();
            this.animId = requestAnimationFrame(loop);
        };
        this.animId = requestAnimationFrame(loop);
    },

    stopLoop() {
        if (this.animId) {
            cancelAnimationFrame(this.animId);
            this.animId = null;
        }
    },

    update() {
        let diff = (this.targetYaw - this.yaw) % 360;
        if (diff > 180) diff -= 360;
        if (diff < -180) diff += 360;
        this.yaw = (this.yaw + diff * 0.18 + 360) % 360;

        this.pitch += (this.targetPitch - this.pitch) * 0.18;

        const deg = Math.round(this.yaw);
        const dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N'];
        const dir = dirs[Math.round(deg / 45)];
        const headingEl = document.getElementById('sv-heading-display');
        const compassEl = document.getElementById('sv-compass-icon');
        if (headingEl) headingEl.textContent = `${String(deg).padStart(3, '0')}° ${dir}`;
        if (compassEl) compassEl.style.transform = `rotate(${-this.yaw}deg)`;
    },

    render() {
        if (!this.canvas || !this.ctx) return;
        const width = this.canvas.clientWidth;
        const height = this.canvas.clientHeight;
        if (width === 0 || height === 0) return;

        const dpr = window.devicePixelRatio || 1;
        if (this.canvas.width !== width * dpr || this.canvas.height !== height * dpr) {
            this.canvas.width = width * dpr;
            this.canvas.height = height * dpr;
        }

        const ctx = this.ctx;
        ctx.save();
        ctx.scale(dpr, dpr);

        const timeOfDay = 0.82;
        const horizonY = height * 0.42 + (this.pitch * 2.5);

        const skyTop = `hsl(207,${60 + timeOfDay * 20}%,${18 + timeOfDay * 38}%)`;
        const skyMid = `hsl(206,${55 + timeOfDay * 15}%,${28 + timeOfDay * 32}%)`;
        const skyHorizon = `hsl(205,${40 + timeOfDay * 10}%,${48 + timeOfDay * 22}%)`;
        const skyGrad = ctx.createLinearGradient(0, 0, 0, horizonY);
        skyGrad.addColorStop(0, skyTop);
        skyGrad.addColorStop(0.55, skyMid);
        skyGrad.addColorStop(1, skyHorizon);
        ctx.fillStyle = skyGrad;
        ctx.fillRect(0, 0, width, horizonY + 2);

        const sunAngleDeg = (110 - this.yaw + 720) % 360;
        const sunVisAng = sunAngleDeg > 180 ? sunAngleDeg - 360 : sunAngleDeg;
        if (Math.abs(sunVisAng) < 80) {
            const sunFrac = sunVisAng / 80;
            const sunX = (width / 2) + sunFrac * width * 0.7;
            const sunY = horizonY * 0.3;
            const sunGlare = ctx.createRadialGradient(sunX, sunY, 0, sunX, sunY, 150);
            sunGlare.addColorStop(0, 'rgba(255,252,235,0.95)');
            sunGlare.addColorStop(0.06, 'rgba(255,240,180,0.7)');
            sunGlare.addColorStop(0.25, 'rgba(255,230,120,0.25)');
            sunGlare.addColorStop(1, 'rgba(255,230,100,0)');
            ctx.fillStyle = sunGlare;
            ctx.fillRect(0, 0, width, horizonY);
            ctx.fillStyle = 'rgba(255,252,235,0.98)';
            ctx.beginPath();
            ctx.arc(sunX, sunY, 14, 0, Math.PI * 2);
            ctx.fill();
        }

        const cloudSeeds = [0.12, 0.35, 0.62, 0.80];
        cloudSeeds.forEach((seed, ci) => {
            const worldOffset = ((seed * 360 - this.yaw + 720) % 360);
            const cang = worldOffset > 180 ? worldOffset - 360 : worldOffset;
            if (Math.abs(cang) > 90) return;
            const cx2 = (width / 2) + (cang / 90) * width * 0.65;
            const cy2 = horizonY * (0.2 + seed * 0.25);
            ctx.globalAlpha = 0.55;
            ctx.fillStyle = '#DDEEFF';
            [[0, 0, 30], [-22, 8, 22], [22, 8, 22], [-10, 16, 18], [10, 16, 18]].forEach(([ox, oy, r]) => {
                ctx.beginPath();
                ctx.arc(cx2 + ox, cy2 + oy, r, 0, Math.PI * 2);
                ctx.fill();
            });
            ctx.globalAlpha = 1;
        });

        const drawMtnLayer = (fillColor, yOffset, amplitude, freq, phase) => {
            ctx.fillStyle = fillColor;
            ctx.beginPath();
            ctx.moveTo(0, horizonY + 2);
            for (let x = 0; x <= width; x += 6) {
                const w = (this.yaw + (x / width - 0.5) * 80 + 720) % 360;
                const h = amplitude * Math.sin(w * freq + phase)
                    + amplitude * 0.5 * Math.cos(w * freq * 1.7 + phase * 0.6)
                    + amplitude * 0.25 * Math.sin(w * freq * 3.1);
                ctx.lineTo(x, horizonY - Math.max(yOffset, h + yOffset));
            }
            ctx.lineTo(width, horizonY + 2);
            ctx.closePath();
            ctx.fill();
        };
        drawMtnLayer('rgba(60,95,85,0.65)', 28, 22, 0.032, 0);
        drawMtnLayer('rgba(40,75,60,0.75)', 14, 18, 0.048, 1.2);
        drawMtnLayer('rgba(32,58,44,0.85)', 6, 12, 0.065, 2.4);

        const groundGrad = ctx.createLinearGradient(0, horizonY, 0, height);
        groundGrad.addColorStop(0, '#7A8090');
        groundGrad.addColorStop(0.15, '#5C636D');
        groundGrad.addColorStop(0.6, '#3E434B');
        groundGrad.addColorStop(1, '#2A2D32');
        ctx.fillStyle = groundGrad;
        ctx.fillRect(0, horizonY, width, height - horizonY);

        const yawFwd = this.yaw > 180 ? this.yaw - 360 : this.yaw;
        const yawBwd = yawFwd > 0 ? yawFwd - 180 : yawFwd + 180;
        const roadAng = Math.abs(yawFwd) < Math.abs(yawBwd) ? yawFwd : yawBwd;
        const isFacingRoad = Math.abs(roadAng) < 70;

        if (isFacingRoad) {
            const vpX = (width / 2) - (roadAng / 55) * (width * 0.42);
            const rpW = width * 0.80;
            const rleft = (width / 2) - (roadAng / 55) * (width * 0.18) - rpW / 2;
            const rright = rleft + rpW;
            const rVpL = vpX - 8;
            const rVpR = vpX + 8;

            const asphaltGrad = ctx.createLinearGradient(0, horizonY, 0, height);
            asphaltGrad.addColorStop(0, '#979DA8');
            asphaltGrad.addColorStop(0.2, '#8A8F99');
            asphaltGrad.addColorStop(0.6, '#7A7F88');
            asphaltGrad.addColorStop(1, '#666A72');
            ctx.fillStyle = asphaltGrad;
            ctx.beginPath();
            ctx.moveTo(rVpL, horizonY);
            ctx.lineTo(rVpR, horizonY);
            ctx.lineTo(rright, height);
            ctx.lineTo(rleft, height);
            ctx.closePath();
            ctx.fill();

            const grassL = ctx.createLinearGradient(0, horizonY, 0, height);
            grassL.addColorStop(0, '#5C8B4A');
            grassL.addColorStop(1, '#3A5A2E');
            ctx.fillStyle = grassL;
            ctx.beginPath();
            ctx.moveTo(0, horizonY);
            ctx.lineTo(rVpL, horizonY);
            ctx.lineTo(rleft, height);
            ctx.lineTo(0, height);
            ctx.closePath();
            ctx.fill();

            ctx.fillStyle = grassL;
            ctx.beginPath();
            ctx.moveTo(rVpR, horizonY);
            ctx.lineTo(width, horizonY);
            ctx.lineTo(width, height);
            ctx.lineTo(rright, height);
            ctx.closePath();
            ctx.fill();

            ctx.strokeStyle = 'rgba(255,255,255,0.85)';
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(rVpL, horizonY); ctx.lineTo(rleft, height);
            ctx.moveTo(rVpR, horizonY); ctx.lineTo(rright, height);
            ctx.stroke();

            const ctrTop = vpX;
            const ctrBot = (rleft + rright) / 2;
            for (let step = 0; step < 1; step += 0.08) {
                const y1 = horizonY + (height - horizonY) * step;
                const y2 = horizonY + (height - horizonY) * (step + 0.04);
                const x1 = ctrTop + (ctrBot - ctrTop) * step;
                const x2 = ctrTop + (ctrBot - ctrTop) * (step + 0.04);
                ctx.strokeStyle = '#F8D800';
                ctx.lineWidth = 3;
                ctx.beginPath(); ctx.moveTo(x1 - 3, y1); ctx.lineTo(x2 - 3, y2); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(x1 + 3, y1); ctx.lineTo(x2 + 3, y2); ctx.stroke();
            }

            const drawDashes = (offsetFrac) => {
                for (let s = 0; s < 1; s += 0.1) {
                    const ya = horizonY + (height - horizonY) * s;
                    const yb = horizonY + (height - horizonY) * (s + 0.05);
                    const xa = vpX + (ctrBot - vpX + offsetFrac * (rright - rleft) / 2) * s;
                    const xb = vpX + (ctrBot - vpX + offsetFrac * (rright - rleft) / 2) * (s + 0.05);
                    ctx.strokeStyle = 'rgba(255,255,255,0.7)';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([10, 8]);
                    ctx.beginPath(); ctx.moveTo(xa, ya); ctx.lineTo(xb, yb); ctx.stroke();
                    ctx.setLineDash([]);
                }
            };
            drawDashes(0.45);
            drawDashes(-0.45);

            ctx.save();
            ctx.translate((rleft + rright) / 2, height * 0.76);
            ctx.transform(1, 0, (roadAng / 55) * 0.3, 0.42, 0, 0);
            ctx.font = 'bold 13px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillStyle = 'rgba(255,255,255,0.45)';
            ctx.fillText(this.roadName.toUpperCase(), 0, 0);
            ctx.restore();

            const polePositions = [
                { side: 'left', frac: 0.3 },
                { side: 'right', frac: 0.55 },
                { side: 'left', frac: 0.72 },
            ];
            polePositions.forEach(pp => {
                const frac = pp.frac;
                const py = horizonY + (height - horizonY) * frac;
                const poleH = (height - horizonY) * (1 - frac) * 0.85;
                const isLeft = pp.side === 'left';
                const px = isLeft
                    ? rleft + (rVpL - rleft) * (1 - frac) - 18 * (1 - frac)
                    : rright + (rVpR - rright) * (1 - frac) + 18 * (1 - frac);

                ctx.strokeStyle = '#4A4A4A';
                ctx.lineWidth = Math.max(2, 4 * frac);
                ctx.beginPath();
                ctx.moveTo(px, py);
                ctx.lineTo(px + (isLeft ? -2 : 2) * frac, py - poleH);
                ctx.stroke();

                const armW = 16 * frac;
                const armY = py - poleH * 0.95;
                const armX = px + (isLeft ? -2 : 2) * frac;
                ctx.strokeStyle = '#555';
                ctx.lineWidth = Math.max(1, 2 * frac);
                ctx.beginPath();
                ctx.moveTo(armX - armW, armY);
                ctx.lineTo(armX + armW, armY);
                ctx.stroke();

                ['rgba(80,80,80,0.9)', 'rgba(80,80,80,0.9)'].forEach((c, ii) => {
                    ctx.fillStyle = c;
                    ctx.beginPath();
                    ctx.arc(armX + (ii === 0 ? -armW : armW), armY, Math.max(1, 2 * frac), 0, Math.PI * 2);
                    ctx.fill();
                });

                if (frac < 0.65) {
                    const nextFrac = frac * 0.5;
                    const ny = horizonY + (height - horizonY) * nextFrac;
                    const nx = isLeft
                        ? rleft + (rVpL - rleft) * (1 - nextFrac) - 18 * (1 - nextFrac)
                        : rright + (rVpR - rright) * (1 - nextFrac) + 18 * (1 - nextFrac);
                    ctx.strokeStyle = 'rgba(60,60,60,0.6)';
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(armX - armW, armY);
                    ctx.quadraticCurveTo((armX + nx) / 2, (armY + ny - poleH * 0.9) / 2 + 4, nx - 16 * nextFrac, ny - poleH * 0.5);
                    ctx.stroke();
                    ctx.beginPath();
                    ctx.moveTo(armX + armW, armY);
                    ctx.quadraticCurveTo((armX + nx) / 2, (armY + ny - poleH * 0.9) / 2 + 4, nx + 16 * nextFrac, ny - poleH * 0.5);
                    ctx.stroke();
                }
            });

            {
                const vFrac = 0.55;
                const vy = horizonY + (height - horizonY) * vFrac;
                const vH = 22 * vFrac;
                const vW = 50 * vFrac;
                const vx = rleft + (rVpL - rleft) * (1 - vFrac) + 12;

                ctx.fillStyle = 'rgba(0,0,0,0.25)';
                ctx.beginPath();
                ctx.ellipse(vx + vW * 0.5, vy + 3, vW * 0.6, 6 * vFrac, 0, 0, Math.PI * 2);
                ctx.fill();

                const bodyGrad = ctx.createLinearGradient(vx, vy - vH, vx, vy);
                bodyGrad.addColorStop(0, '#E8EAF0');
                bodyGrad.addColorStop(0.4, '#D0D4DC');
                bodyGrad.addColorStop(1, '#A8ABB4');
                ctx.fillStyle = bodyGrad;
                ctx.beginPath();
                ctx.roundRect(vx, vy - vH, vW, vH, [4, 4, 0, 0]);
                ctx.fill();

                ctx.fillStyle = '#C8CACE';
                ctx.beginPath();
                ctx.roundRect(vx + vW * 0.15, vy - vH * 1.45, vW * 0.68, vH * 0.55, 4);
                ctx.fill();

                ctx.fillStyle = 'rgba(140,200,220,0.55)';
                ctx.beginPath();
                ctx.roundRect(vx + vW * 0.17, vy - vH * 1.38, vW * 0.28, vH * 0.45, 2);
                ctx.fill();

                ctx.fillStyle = 'rgba(140,200,220,0.45)';
                ctx.beginPath();
                ctx.roundRect(vx + vW * 0.54, vy - vH * 1.38, vW * 0.28, vH * 0.45, 2);
                ctx.fill();

                [0.15, 0.78].forEach(wx => {
                    ctx.fillStyle = '#222';
                    ctx.beginPath();
                    ctx.ellipse(vx + vW * wx, vy, 5 * vFrac, 5 * vFrac, 0, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = '#888';
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.arc(vx + vW * wx, vy, 3 * vFrac, 0, Math.PI * 2);
                    ctx.stroke();
                });
            }

            {
                const tvFrac = 0.25;
                const tvy = horizonY + (height - horizonY) * tvFrac;
                const tvH = 10 * tvFrac;
                const tvW = 14 * tvFrac;
                const tvx = vpX + (roadAng / 55) * 30;

                ctx.fillStyle = '#8B2020';
                ctx.beginPath();
                ctx.roundRect(tvx - tvW / 2, tvy - tvH, tvW, tvH, 2);
                ctx.fill();
                ctx.fillStyle = 'rgba(200,235,240,0.6)';
                ctx.beginPath();
                ctx.roundRect(tvx - tvW * 0.3, tvy - tvH * 0.85, tvW * 0.6, tvH * 0.4, 1);
                ctx.fill();
                ctx.fillStyle = '#FFEE88';
                ctx.beginPath(); ctx.arc(tvx - tvW * 0.28, tvy - tvH * 0.1, 1.5, 0, Math.PI * 2); ctx.fill();
                ctx.beginPath(); ctx.arc(tvx + tvW * 0.28, tvy - tvH * 0.1, 1.5, 0, Math.PI * 2); ctx.fill();
            }

            {
                const pFrac = 0.42;
                const py2 = horizonY + (height - horizonY) * pFrac;
                const pH = 14 * pFrac;
                const px2 = vpX + 18 * pFrac;

                ctx.strokeStyle = '#555';
                ctx.lineWidth = 1.5 * pFrac;
                [-8, 8].forEach(ow => {
                    ctx.beginPath();
                    ctx.arc(px2 + ow * pFrac, py2, 5 * pFrac, 0, Math.PI * 2);
                    ctx.stroke();
                });
                ctx.beginPath();
                ctx.moveTo(px2 - 8 * pFrac, py2);
                ctx.lineTo(px2 + 4 * pFrac, py2 - 7 * pFrac);
                ctx.lineTo(px2 + 8 * pFrac, py2);
                ctx.stroke();
                ctx.fillStyle = '#8BC34A';
                ctx.beginPath();
                ctx.ellipse(px2 + 2 * pFrac, py2 - 9 * pFrac, 3 * pFrac, 5 * pFrac, -0.2, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#C8946A';
                ctx.beginPath();
                ctx.arc(px2 + 3 * pFrac, py2 - 14 * pFrac, 3 * pFrac, 0, Math.PI * 2);
                ctx.fill();
            }

            const treeXPositions = [rright + 20 * 0.75, rright + 45 * 0.75, rright + 70 * 0.75].filter(tx => tx < width + 20);
            const treeFracs = [0.7, 0.5, 0.38];
            treeXPositions.forEach((tx, ti) => {
                const tf = treeFracs[ti] || 0.4;
                const ty2 = horizonY + (height - horizonY) * tf;
                const tH = (height - horizonY) * tf * 0.8;

                ctx.strokeStyle = '#5C3D1A';
                ctx.lineWidth = Math.max(2, 3.5 * tf);
                ctx.beginPath();
                ctx.moveTo(tx, ty2);
                ctx.quadraticCurveTo(tx + 4 * tf, ty2 - tH * 0.5, tx + 3 * tf, ty2 - tH);
                ctx.stroke();

                const fX = tx + 3 * tf;
                const fY = ty2 - tH;
                for (let fa = 0; fa < 7; fa++) {
                    const ang2 = (fa / 7) * Math.PI * 2 - Math.PI * 0.3;
                    const fLen = 18 * tf;
                    ctx.strokeStyle = `rgba(42,90,30,${0.6 + fa % 2 * 0.25})`;
                    ctx.lineWidth = Math.max(1, 1.5 * tf);
                    ctx.beginPath();
                    ctx.moveTo(fX, fY);
                    ctx.quadraticCurveTo(
                        fX + Math.cos(ang2) * fLen * 0.6, fY + Math.sin(ang2) * fLen * 0.6 + 4 * tf,
                        fX + Math.cos(ang2) * fLen, fY + Math.sin(ang2) * fLen + 8 * tf
                    );
                    ctx.stroke();
                }
            });

            const shrubY = horizonY + (height - horizonY) * 0.68;
            const shrubGrad = ctx.createLinearGradient(0, shrubY - 14, 0, shrubY + 5);
            shrubGrad.addColorStop(0, '#3D7A2E');
            shrubGrad.addColorStop(1, '#2A5220');
            ctx.fillStyle = shrubGrad;
            for (let sx = 0; sx < rleft + 10; sx += 14) {
                const sw = 10 + Math.sin(sx * 0.4 + this.yaw * 0.01) * 5;
                const sh = 10 + Math.sin(sx * 0.7) * 4;
                ctx.beginPath();
                ctx.arc(sx, shrubY - sh / 2, sw / 2, 0, Math.PI * 2);
                ctx.fill();
            }

        } else {
            ctx.fillStyle = '#606268';
            ctx.fillRect(0, horizonY, width, height - horizonY);

            ctx.fillStyle = '#888B94';
            ctx.fillRect(0, horizonY + (height - horizonY) * 0.55, width, height - horizonY);

            const bldColors = ['#B0824A', '#CCAA72', '#A09060', '#D4B080'];
            let bx = 0;
            while (bx < width * 0.55) {
                const bw = 55 + Math.sin(bx * 0.3) * 20;
                const bh = (height - horizonY) * (0.55 + Math.sin(bx) * 0.2);
                ctx.fillStyle = bldColors[Math.floor(bx / 60) % bldColors.length];
                ctx.fillRect(bx, horizonY, bw - 2, bh);
                ctx.fillStyle = 'rgba(160,220,240,0.45)';
                ctx.fillRect(bx + 6, horizonY + 8, bw - 18, (bh - 20) * 0.4);
                ctx.fillStyle = '#E8D060';
                ctx.fillRect(bx + 4, horizonY + 4, bw - 12, 10);
                bx += bw + 2;
            }

            ctx.font = `bold ${Math.round(height * 0.042)}px sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillStyle = 'rgba(255,255,255,0.55)';
            ctx.fillText(`${this.roadName.toUpperCase()} — ROADSIDE VIEW`, width / 2, horizonY + (height - horizonY) * 0.88);
        }

        ctx.restore();
    }
};

// ════════════════════════════════════════════════════════════════════════════
// 2. GIS MAP ENGINE (Leaflet + Google Satellite + Haversine + Geocode)
// ════════════════════════════════════════════════════════════════════════════
const SpectralMap = {
    map: null,
    baseLayers: {},
    activeBaseMap: 'satellite',
    isStreetViewMode: false,
    layerGroups: {
        incidents: null,
        wards: null,
        resources: null,
        safeZones: null,
    },
    activeFilters: {
        incidents: true,
        wards: true,
        resources: true,
        safeZones: true,
        severity: 'ALL'
    },
    pickerMarker: null,
    anomalyMarker: null,
    streetViewMarker: null,
    googlePanorama: null,
    googleMapsLoaded: false,
    isPickingLocation: false,
    tileErrorTimer: null,

    haversineMeters(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const toRad = d => d * Math.PI / 180;
        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    },

    resolveNearestBarangay(lat, lng) {
        if (lat >= 8.5015 && lat <= 8.5150 && lng >= 125.9670 && lng <= 125.9845) {
            const poblacionList = SpectralData.barangays.filter(b => b.name.startsWith('Barangay '));
            let nearestPob = poblacionList[0];
            let minDistPob = Infinity;
            poblacionList.forEach(b => {
                const dist = this.haversineMeters(lat, lng, b.lat, b.lng);
                if (dist < minDistPob) { minDistPob = dist; nearestPob = b; }
            });
            return { barangay: nearestPob, distanceMeters: Math.round(minDistPob) };
        }

        let nearest = SpectralData.barangays[0];
        let minDist = Infinity;
        SpectralData.barangays.forEach(b => {
            const dist = this.haversineMeters(lat, lng, b.lat, b.lng);
            if (dist < minDist) { minDist = dist; nearest = b; }
        });
        return { barangay: nearest, distanceMeters: Math.round(minDist) };
    },

    selectBarangayOption(selectElement, identifier) {
        if (!selectElement || !identifier) return false;
        const strId = String(identifier).trim();
        const cleanName = strId.replace(/^Brgy\.?\s*/i, '').toLowerCase();

        for (let i = 0; i < selectElement.options.length; i++) {
            const opt = selectElement.options[i];
            const optVal = String(opt.value).trim();
            const optId = String(opt.dataset?.id || '').trim();
            const optName = String(opt.dataset?.name || opt.textContent || '').replace(/^Brgy\.?\s*/i, '').trim().toLowerCase();

            if (optVal === strId || optId === strId || optName === cleanName || opt.text.toLowerCase().includes(cleanName)) {
                selectElement.selectedIndex = i;
                return true;
            }
        }
        return false;
    },

    async reverseGeocode(lat, lng) {
        try {
            const res = await fetch(`/api/spectral/reverse-geocode?lat=${lat}&lng=${lng}`, {
                headers: { 'Accept': 'application/json' },
                signal: AbortSignal.timeout(6000)
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success && data.address) {
                    return data;
                }
            }
        } catch (e) {
            console.warn('[Spectra GIS] Internal reverse geocode failed, trying direct OSM fallback:', e.message);
        }

        const inPoblacion = (lat >= 8.5015 && lat <= 8.5150 && lng >= 125.9670 && lng <= 125.9845);
        let { barangay } = this.resolveNearestBarangay(lat, lng);

        let road = '';
        try {
            const osmUrl = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=jsonv2&addressdetails=1`;
            const res = await fetch(osmUrl, { signal: AbortSignal.timeout(4000) });
            if (res.ok) {
                const data = await res.json();
                const addr = data.address || {};
                if (addr.road) road = `${addr.road}, `;

                const rawHint = addr.quarter || addr.village || addr.suburb || addr.neighbourhood || addr.city_district;
                if (rawHint) {
                    const cleanHint = rawHint.trim().toLowerCase();
                    const isPoblacionHint = cleanHint.includes('barangay') || /^(1|2|3|4|5)$/.test(cleanHint);

                    if (!inPoblacion || isPoblacionHint) {
                        const matched = SpectralData.barangays.find(b =>
                            b.name.toLowerCase() === cleanHint ||
                            cleanHint.includes(b.name.toLowerCase()) ||
                            b.name.toLowerCase().includes(cleanHint.replace(/^brgy\.?\s*/i, ''))
                        );
                        if (matched) {
                            barangay = matched;
                        }
                    }
                }
            }
        } catch (_) { }

        return {
            success: true,
            address: `${road}Brgy. ${barangay.name}, San Francisco, Agusan del Sur, Philippines`,
            barangay: barangay.name,
            barangay_name: barangay.name,
            barangay_id: barangay.id,
            municipality: 'San Francisco',
            province: 'Agusan del Sur',
            country: 'Philippines',
            display: `${road}Brgy. ${barangay.name}, San Francisco, Agusan del Sur, Philippines`
        };
    },

    init() {
        if (window.SPECTRAL_REPORTER_MAP) return;

        const mapElement = document.getElementById('spectral-map');
        if (!mapElement) return;

        try {
            const savedInc = localStorage.getItem('spectral_layer_incidents');
            if (savedInc !== null) this.activeFilters.incidents = (savedInc !== '0');
            const savedSafe = localStorage.getItem('spectral_layer_safeZones');
            if (savedSafe !== null) this.activeFilters.safeZones = (savedSafe !== '0');
        } catch (_) { }

        if (this.map || (window.SpectralMap && window.SpectralMap.map) || (mapElement && mapElement._leaflet_id)) {
            this.map = this.map || (window.SpectralMap ? window.SpectralMap.map : null);
            try {
                if (this.map) {
                    this.map.invalidateSize();
                    this.updateLayerToggleButtons();
                }
            } catch (_) { }
            return;
        }

        try {
            const initialCenter = (SpectralData.node && SpectralData.node.coordinates) ? SpectralData.node.coordinates : [8.5100, 125.9750];
            const initialZoom = 14;

            this.map = L.map('spectral-map', {
                center: initialCenter,
                zoom: initialZoom,
                zoomControl: false,
                attributionControl: true
            });

            this.baseLayers.satellite = L.tileLayer(
                'https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                attribution: 'OpenStreetMap',
                subdomains: ['0', '1', '2', '3'],
                maxZoom: 20
            }).addTo(this.map);

            this.activeBaseMap = 'satellite';

            L.control.zoom({ position: 'topright' }).addTo(this.map);
            L.control.scale({ imperial: true, metric: true, position: 'bottomleft' }).addTo(this.map);

            this.layerGroups.incidents = L.layerGroup().addTo(this.map);
            this.layerGroups.wards = L.layerGroup().addTo(this.map);
            this.layerGroups.resources = L.layerGroup().addTo(this.map);
            this.layerGroups.safeZones = L.layerGroup().addTo(this.map);

            this.renderAllLayers();
            this.updateLayerToggleButtons();

            this.map.on('click', (e) => this.handleMapClick(e));

            setTimeout(() => {
                if (this.map) {
                    this.map.invalidateSize();
                    this.renderAllLayers();
                    this.updateLayerToggleButtons();
                }
            }, 250);
        } catch (err) {
            console.error('[SpectralMap] Init error:', err);
        }
    },

    renderAllLayers() {
        this.renderIncidents();
        this.renderSafeZones();
        this.updateStatsCounters();
    },

    renderIncidents() {
        if (!this.layerGroups.incidents) return;
        this.layerGroups.incidents.clearLayers();
        this.incidentMarkers = {};
        if (!this.activeFilters.incidents) return;

        SpectralData.incidents.forEach(inc => {
            if (!inc.latitude || !inc.longitude) return;
            const lat = parseFloat(inc.latitude);
            const lng = parseFloat(inc.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            if (this.activeFilters.severity !== 'ALL' && inc.severity !== this.activeFilters.severity) return;

            const sevColors = {
                CRITICAL: '#000000',
                HIGH: '#EF4444',
                MEDIUM: '#EAB308',
                LOW: '#22C55E'
            };
            const color = sevColors[inc.severity] || '#22C55E';
            const isHigh = inc.severity === 'HIGH' || inc.severity === 'CRITICAL';
            const isCritical = inc.severity === 'CRITICAL';
            const pulseBg = isCritical ? 'rgba(0,0,0,0.6)' : `${color}44`;
            const markerBorder = isCritical ? '#9CA3AF' : '#FFFFFF';
            const markerShadow = isCritical ? '0 0 12px rgba(0,0,0,0.95)' : `0 0 10px ${color}`;

            const markerHtml = `
                <div style="position:relative; width:26px; height:26px; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="${inc.id}: ${inc.title}">
                    ${isHigh ? `<div style="position:absolute; inset:-4px; border-radius:50%; background:${pulseBg}; animation:ping 2s cubic-bezier(0,0,0.2,1) infinite;"></div>` : ''}
                    <div style="position:relative; width:20px; height:20px; border-radius:50%; background:${color}; border:2px solid ${markerBorder}; box-shadow:${markerShadow}; display:flex; align-items:center; justify-content:center;">
                        <div style="width:6px; height:6px; border-radius:50%; background:#FFFFFF;"></div>
                    </div>
                </div>`;

            const icon = L.divIcon({
                className: '',
                html: markerHtml,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            const marker = L.marker([lat, lng], { icon, zIndexOffset: 800 });

            const defaultHpMap = { CRITICAL: 150, HIGH: 100, MEDIUM: 60, LOW: 30 };
            const defaultHp = defaultHpMap[inc.severity] || 100;
            const assignment = (inc.responder_assignments && inc.responder_assignments.length > 0)
                ? inc.responder_assignments[inc.responder_assignments.length - 1]
                : null;
            const maxHp = assignment && assignment.anomaly_max_hp ? assignment.anomaly_max_hp : (inc.anomaly_max_hp || defaultHp);
            const hp = assignment && assignment.anomaly_hp !== null && assignment.anomaly_hp !== undefined
                ? assignment.anomaly_hp
                : (inc.anomaly_hp !== null && inc.anomaly_hp !== undefined ? inc.anomaly_hp : maxHp);
            const progress = assignment && assignment.response_progress !== undefined && assignment.response_progress !== null
                ? assignment.response_progress
                : (inc.response_progress || 0);
            const pct = maxHp > 0 ? Math.max(0, Math.min(100, Math.round((hp / maxHp) * 100))) : 100;
            const barColor = pct <= 25 ? '#22C55E' : (pct <= 60 ? '#EAB308' : '#EF4444');

            const isResponderAssigned = !!(assignment && assignment.responder_id);
            const isVerified = ['VERIFIED', 'RESOLVED'].includes(inc.status) || isResponderAssigned;
            const isInvestigating = isVerified || ['UNDER INVESTIGATION'].includes(inc.status) || (inc.investigations && inc.investigations.length > 0);

            let statusLabel = 'Pending';
            if (inc.status === 'RESOLVED') {
                statusLabel = 'Resolved';
            } else if (isResponderAssigned) {
                statusLabel = 'Responder Assigned';
            } else if (isVerified) {
                statusLabel = 'Verified';
            } else if (isInvestigating) {
                statusLabel = 'Under Investigation';
            } else {
                statusLabel = 'Pending';
            }

            const conditionHtml = isVerified ? `
                <div style="margin-top:8px; padding-top:8px; border-top:1px solid #2A3440; font-family:'JetBrains Mono',monospace;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <span style="font-size:10px; font-weight:700; color:#CBD5E1; text-transform:uppercase;">ANOMALY CONDITION</span>
                        <span style="font-size:11px; font-weight:800; color:${barColor};">${hp} / ${maxHp} HP</span>
                    </div>
                    <div style="width:100%; height:6px; background:#11161D; border-radius:3px; overflow:hidden; border:1px solid #2A3440; margin-bottom:6px;">
                        <div style="width:${pct}%; height:100%; background:${barColor}; transition:width 0.5s;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <span style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">RESPONSE PROGRESS</span>
                        <span style="font-size:11px; font-weight:700; color:#10B981;">${progress}%</span>
                    </div>
                    <div style="width:100%; height:5px; background:#11161D; border-radius:3px; overflow:hidden; border:1px solid #2A3440; margin-bottom:6px;">
                        <div style="width:${progress}%; height:100%; background:#10B981; transition:width 0.5s;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">STATUS</span>
                        <span style="font-size:11px; font-weight:700; color:#A78BFA;">${statusLabel}</span>
                    </div>
                </div>` : `
                <div style="margin-top:8px; padding-top:8px; border-top:1px solid #2A3440; font-family:'JetBrains Mono',monospace; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">STATUS</span>
                    <span style="font-size:11px; font-weight:700; color:#EAB308;">${statusLabel}</span>
                </div>`;

            const sevLower = (inc.severity || 'low').toLowerCase();

            marker.bindPopup(`
                <div style="padding: 12px 14px; min-width: 270px; font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px; gap: 8px; padding-right: 24px;">
                        <span style="font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; color:#8B5CF6;">${inc.id}</span>
                        <span class="badge-${sevLower}" style="font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px; flex-shrink:0;">${inc.severity}</span>
                    </div>
                    <h4 style="font-size:12px; font-weight:800; color:#FFFFFF; line-height:1.3; margin-bottom:4px; padding-right: 12px;">${inc.title}</h4>
                    <p id="popup-addr-${inc.db_id || inc.id}" style="font-size:11px; color:#9CA3AF; margin-bottom:8px;">${inc.real_address || ('Brgy. ' + inc.barangay + ', ' + inc.municipality + ', ' + inc.province)}</p>
                    ${conditionHtml}
                    <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px; padding-top:8px; margin-top:8px; border-top:1px solid #2A3440;">
                        <a href="/incidents/${inc.db_id || inc.id}" style="background:#8B5CF6; color:#FFFFFF; border:none; padding:6px 12px; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0; display:inline-flex; align-items:center; gap:5px; text-decoration:none;" onmouseover="this.style.background='#7C3AED'" onmouseout="this.style.background='#8B5CF6'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>INSPECT</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </div>
            `);

            marker.on('click', () => {
                if (SpectralMap.map && inc.latitude && inc.longitude) {
                    const currentZoom = SpectralMap.map.getZoom();
                    const targetZoom = Math.max(currentZoom, 17);
                    SpectralMap.map.flyTo([inc.latitude, inc.longitude], targetZoom, { duration: 0.7 });
                }
                SpectralUI.inspectIncident(inc.id);

                if (!inc.real_address && SpectralMap.reverseGeocode && inc.latitude && inc.longitude) {
                    SpectralMap.reverseGeocode(inc.latitude, inc.longitude).then(geo => {
                        if (geo && geo.address) {
                            inc.real_address = geo.address;
                            if (geo.barangay_name) inc.barangay = geo.barangay_name;
                            const popupEl = document.getElementById(`popup-addr-${inc.db_id || inc.id}`);
                            if (popupEl) popupEl.textContent = geo.address;
                            const locEl = document.getElementById('insp-location');
                            if (locEl && SpectralUI.selectedIncidentId === inc.id) {
                                locEl.textContent = geo.address;
                            }
                        }
                    }).catch(() => { });
                }
            });
            if (inc.db_id) this.incidentMarkers[inc.db_id] = marker;
            if (inc.id) this.incidentMarkers[inc.id] = marker;
            this.layerGroups.incidents.addLayer(marker);
        });
    },

    renderWards() {
        if (this.layerGroups.wards) this.layerGroups.wards.clearLayers();
    },

    renderResources() {
        if (this.layerGroups.resources) this.layerGroups.resources.clearLayers();
    },

    // 🌐 Safe zone circles now use the ACTUAL protection radius (no more ×0.30 scaling)
    renderSafeZones() {
        if (!this.layerGroups.safeZones) return;
        this.layerGroups.safeZones.clearLayers();
        if (!this.activeFilters.safeZones) return;

        SpectralData.safeZones.forEach(sz => {
            const safeRadius = Math.round(parseFloat(sz.radius) || 450);
            const circle = L.circle([sz.latitude, sz.longitude], {
                radius: safeRadius,
                color: '#22C55E',
                weight: 1.5,
                fillColor: '#22C55E',
                fillOpacity: 0.08
            });

            const szHtml = `
                <div style="width:28px; height:28px; border-radius:50%; background:#10B981; border:2px solid #34D399; box-shadow:0 0 10px rgba(16,185,129,0.8); display:flex; align-items:center; justify-content:center; cursor:pointer;" title="${sz.name}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#FFFFFF" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>`;
            const szIcon = L.divIcon({ className: '', html: szHtml, iconSize: [28, 28], iconAnchor: [14, 14], popupAnchor: [0, -14] });
            const marker = L.marker([sz.latitude, sz.longitude], { icon: szIcon, zIndexOffset: 700 });

            const popupHtml = `
                <div class="gis-custom-popup" style="padding: 10px 12px; min-width: 230px; box-sizing: border-box; font-family:'Plus Jakarta Sans',sans-serif;">
                    <div style="margin-bottom: 2px;">
                        <span class="popup-code" style="color:#22C55E; font-size:9px; font-weight:700; font-family:'JetBrains Mono',monospace;">SAFE WARD STATION</span>
                    </div>
                    <h4 class="popup-title" style="font-size:12px; font-weight:800; color:#FFFFFF; margin-bottom:2px;">${sz.name}</h4>
                    <p class="popup-meta" style="font-size:11px; color:#9CA3AF;">Active Safe Ward Station Perimeter &bull; ${safeRadius}m radius</p>
                </div>`;

            marker.bindPopup(popupHtml, { className: 'gis-leaflet-popup' });
            marker.bindTooltip(sz.name, { direction: 'top', offset: [0, -14] });

            circle.bindPopup(popupHtml, { className: 'gis-leaflet-popup' });
            circle.on('click', () => {
                marker.openPopup();
                if (SpectralMap.map && sz.latitude && sz.longitude) {
                    const currentZoom = SpectralMap.map.getZoom();
                    const targetZoom = Math.max(currentZoom, 17);
                    SpectralMap.map.flyTo([sz.latitude, sz.longitude], targetZoom, { duration: 0.7 });
                }
            });

            marker.on('click', () => {
                if (SpectralMap.map && sz.latitude && sz.longitude) {
                    const currentZoom = SpectralMap.map.getZoom();
                    const targetZoom = Math.max(currentZoom, 17);
                    SpectralMap.map.flyTo([sz.latitude, sz.longitude], targetZoom, { duration: 0.7 });
                }
            });

            this.layerGroups.safeZones.addLayer(circle);
            this.layerGroups.safeZones.addLayer(marker);
        });
    },

    startLocationPicking() {
        const mapEl = document.getElementById('spectral-map');
        if (!mapEl || (window.location.pathname !== '/map' && !mapEl.offsetParent)) {
            window.location.href = '/map?pick=1';
            return;
        }

        this.isPickingLocation = true;
        if (this.anomalyMarker && this.map) {
            this.map.removeLayer(this.anomalyMarker);
            this.anomalyMarker = null;
        }
        const hudEl = document.getElementById('location-picker-hud');
        if (mapEl) mapEl.classList.add('picking-location');
        if (hudEl) {
            hudEl.style.display = 'flex';
            hudEl.classList.add('visible');
        }

        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.add('hidden');
    },

    cancelLocationPicking() {
        this.isPickingLocation = false;
        const mapEl = document.getElementById('spectral-map');
        const hudEl = document.getElementById('location-picker-hud');
        if (mapEl) mapEl.classList.remove('picking-location');
        if (hudEl) {
            hudEl.style.display = 'none';
            hudEl.classList.remove('visible');
        }

        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.remove('hidden');
    },

    focusOnInspected() {
        const lat = SpectralUI._inspectedLat;
        const lng = SpectralUI._inspectedLng;
        if (lat && lng && this.map) {
            const currentZoom = this.map.getZoom();
            const targetZoom = Math.max(currentZoom, 17);
            this.map.flyTo([lat, lng], targetZoom, { duration: 0.7 });
        }
    },

    useMyLocation() {
        if (!navigator.geolocation) {
            SpectralUI.showToast('Geolocation not supported by this browser.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (this.map) {
                    this.map.setView([lat, lng], 15, { animate: true });
                }
                SpectralUI.showToast(`Located: ${lat.toFixed(5)}, ${lng.toFixed(5)}`);
            },
            () => {
                SpectralUI.showToast('Unable to retrieve your location.');
            }
        );
    },

    async handleMapClick(e) {
        if (!this.isPickingLocation) {
            return;
        }

        const { lat, lng } = e.latlng;

        if (this.pickerMarker) this.map.removeLayer(this.pickerMarker);

        const pinHtml = `
            <div class="marker-picker-pin animate-bounce">
                <div style="width:28px;height:28px;background:#8B5CF6;border-radius:50%;border:2px solid #FFFFFF;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(139,92,246,0.6);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#fff"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                </div>
                <div style="width:2px;height:12px;background:#8B5CF6;margin:0 auto;"></div>
            </div>`;

        this.pickerMarker = L.marker([lat, lng], {
            icon: L.divIcon({ className: '', html: pinHtml, iconSize: [36, 44], iconAnchor: [18, 40] })
        }).addTo(this.map);

        const { barangay } = this.resolveNearestBarangay(lat, lng);

        const latInput = document.getElementById('report-lat');
        const lngInput = document.getElementById('report-lng');
        const barangaySelect = document.getElementById('report-barangay');
        const resolvedInput = document.getElementById('report-resolved-location');
        const geocodeStatus = document.getElementById('report-geocode-status');

        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
        if (barangaySelect) this.selectBarangayOption(barangaySelect, barangay.id || barangay.name);

        if (geocodeStatus) {
            geocodeStatus.textContent = 'Resolving address...';
            geocodeStatus.className = 'text-[9px] font-mono text-amber-400 animate-pulse';
        }
        if (resolvedInput) {
            resolvedInput.value = `Resolving address for ${lat.toFixed(5)}, ${lng.toFixed(5)}...`;
        }

        this.isPickingLocation = false;
        const mapEl = document.getElementById('spectral-map');
        const hudEl = document.getElementById('location-picker-hud');
        if (mapEl) mapEl.classList.remove('picking-location');
        if (hudEl) hudEl.classList.remove('visible');

        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.remove('hidden');

        if (typeof SpectralUI !== 'undefined' && typeof SpectralUI.lookupAddress === 'function') {
            SpectralUI.lookupAddress(lat, lng);
        }
    },

    toggleStreetView() {
        const leftPanel = document.getElementById('streetview-left-panel');
        const isOpen = leftPanel && leftPanel.classList.contains('translate-x-0');
        if (isOpen) {
            this.closeStreetView();
        } else {
            const center = this.map ? this.map.getCenter() : { lat: 8.5100, lng: 125.9750 };
            this.openStreetView(center.lat, center.lng);
        }
    },

    openStreetView(lat, lng) {
        this.isStreetViewMode = true;
        const leftPanel = document.getElementById('streetview-left-panel');
        const btn = document.getElementById('streetview-toggle-btn');
        const label = document.getElementById('streetview-btn-label');

        if (leftPanel) {
            leftPanel.classList.remove('-translate-x-full');
            leftPanel.classList.add('translate-x-0');
        }
        if (btn) {
            btn.classList.add('border-[#FACC15]', 'text-[#FACC15]');
        }
        if (label) {
            label.textContent = 'Street Active';
        }

        const { barangay, distanceMeters } = this.resolveNearestBarangay(lat, lng);

        const coordText = document.getElementById('sv-coord-text');
        const barangayText = document.getElementById('sv-barangay-text');
        const subtitle = document.getElementById('sv-road-subtitle');
        const nearestWard = document.getElementById('sv-nearest-ward');
        const roadTitle = document.getElementById('sv-road-title');
        const roadMeta = document.getElementById('sv-road-meta');
        const deepLink = document.getElementById('sv-google-deep-link');

        if (coordText) coordText.textContent = `${lat.toFixed(5)}° N, ${lng.toFixed(5)}° E`;
        if (barangayText) barangayText.textContent = `Brgy. ${barangay.name}`;
        if (subtitle) subtitle.textContent = `Brgy. ${barangay.name}, San Francisco, Agusan del Sur`;
        if (nearestWard) nearestWard.textContent = `Brgy. ${barangay.name} (~${distanceMeters}m)`;

        if (deepLink) {
            deepLink.href = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${lat},${lng}`;
        }

        let guessedRoad = barangay.name === 'Hubang' ? 'Davao-Agusan National Hwy' : `San Francisco - ${barangay.name} Rd`;
        if (roadTitle) roadTitle.textContent = guessedRoad;
        if (roadMeta) roadMeta.textContent = `${barangay.name}, San Francisco · Updated 2026`;

        if (this.streetViewMarker) {
            this.map.removeLayer(this.streetViewMarker);
        }

        const pegmanHtml = `
            <div class="marker-pegman" title="Viewing: ${guessedRoad}">
                <div class="marker-pegman-pulse"></div>
                <div class="marker-pegman-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#11161D">
                        <circle cx="12" cy="4" r="2.5"/>
                        <path d="M12 7.5c-2.5 0-4.5 1.5-4.5 3.5v2h2v5h5v-5h2v-2c0-2-2-3.5-4.5-3.5z"/>
                        <path d="M9.5 18h5v2.5h-5z"/>
                    </svg>
                </div>
                <div class="marker-pegman-stem"></div>
            </div>`;

        this.streetViewMarker = L.marker([lat, lng], {
            icon: L.divIcon({ className: '', html: pegmanHtml, iconSize: [32, 42], iconAnchor: [16, 42] }),
            zIndexOffset: 1600
        }).addTo(this.map);

        StreetView360.init();
        StreetView360.setLocation(lat, lng, guessedRoad, barangay.name);

        this.loadRealGoogleStreetView(lat, lng);

        this.reverseGeocode(lat, lng).then(geo => {
            if (geo.ok && (geo.village || geo.city)) {
                const actualRoad = geo.village || geo.city || guessedRoad;
                if (roadTitle) roadTitle.textContent = actualRoad;
                StreetView360.roadName = actualRoad;
            }
        });

        setTimeout(() => {
            if (this.map) this.map.invalidateSize();
        }, 320);
    },

    getGoogleMapsKey() {
        return (typeof window.GOOGLE_MAPS_API_KEY === 'string' && window.GOOGLE_MAPS_API_KEY.trim())
            || localStorage.getItem('google_maps_api_key')
            || '';
    },

    saveGoogleMapsKey() {
        const input = document.getElementById('sv-api-key-input');
        if (!input || !input.value.trim()) {
            SpectralUI.showToast('Please enter a valid Google Maps API Key');
            return;
        }
        const key = input.value.trim();
        localStorage.setItem('google_maps_api_key', key);
        SpectralUI.showToast('Google Maps API Key saved! Loading real photos...');
        this.updateKeyStatusUI(true);
        if (StreetView360.lat && StreetView360.lng) {
            this.loadRealGoogleStreetView(StreetView360.lat, StreetView360.lng);
        }
    },

    updateKeyStatusUI(hasKey) {
        const badge = document.getElementById('sv-key-status-badge');
        const desc = document.getElementById('sv-key-desc');
        const input = document.getElementById('sv-api-key-input');
        if (hasKey) {
            if (badge) {
                badge.className = 'px-1.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-[9px] font-mono text-emerald-400';
                badge.textContent = 'GOOGLE API CONNECTED';
            }
            if (desc) {
                desc.textContent = 'Live camera photos are actively streaming directly from Google Street View servers.';
            }
            if (input) input.placeholder = 'API Key is active (Connected)';
        } else {
            if (badge) {
                badge.className = 'px-1.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-[9px] font-mono text-emerald-400';
                badge.textContent = 'ACTIVE 360° ENGINE';
            }
            if (desc) {
                desc.textContent = 'Synchronized live 360° road perspective with Philippine highway layout, vehicle recognition, and terrain mapping.';
            }
        }
    },

    loadRealGoogleStreetView(lat, lng) {
        const key = this.getGoogleMapsKey();
        if (!key) {
            this.updateKeyStatusUI(false);
            return;
        }

        this.updateKeyStatusUI(true);

        const realContainer = document.getElementById('streetview-real-container');
        const canvas = document.getElementById('streetview-canvas');
        const compass = document.getElementById('sv-compass-container');
        const controls = document.getElementById('sv-controls-overlay');
        const hint = document.getElementById('sv-drag-hint');
        const modeBadge = document.getElementById('sv-mode-badge');

        const initPano = () => {
            if (!realContainer || typeof google === 'undefined' || !google.maps) return;

            const svService = new google.maps.StreetViewService();
            svService.getPanorama({ location: { lat, lng }, radius: 150 }, (data, status) => {
                if (status === google.maps.StreetViewStatus.OK && data && data.location) {
                    realContainer.classList.remove('hidden');
                    if (canvas) canvas.classList.add('hidden');
                    if (compass) compass.classList.add('hidden');
                    if (controls) controls.classList.add('hidden');
                    if (hint) hint.classList.add('hidden');
                    if (modeBadge) {
                        modeBadge.textContent = 'LIVE STREET VIEW';
                        modeBadge.className = 'bg-[#0B0F14]/90 backdrop-blur border border-emerald-500/40 rounded-md px-2 py-1 shadow-lg text-[9px] font-mono text-emerald-400 font-bold';
                    }

                    if (!this.googlePanorama) {
                        this.googlePanorama = new google.maps.StreetViewPanorama(realContainer, {
                            position: data.location.latLng,
                            pov: { heading: 165, pitch: 0 },
                            zoom: 1,
                            addressControl: false,
                            fullscreenControl: false,
                            linksControl: true,
                            panControl: true,
                            enableCloseButton: false
                        });
                    } else {
                        this.googlePanorama.setPosition(data.location.latLng);
                    }
                } else {
                    realContainer.classList.add('hidden');
                    if (canvas) canvas.classList.remove('hidden');
                    if (compass) compass.classList.remove('hidden');
                    if (controls) controls.classList.remove('hidden');
                    if (modeBadge) {
                        modeBadge.textContent = '360° RECON';
                        modeBadge.className = 'bg-[#0B0F14]/90 backdrop-blur border border-[#2A3440] rounded-md px-2 py-1 shadow-lg text-[9px] font-mono text-[#FACC15] font-bold';
                    }
                }
            });
        };

        if (this.googleMapsLoaded && typeof google !== 'undefined' && google.maps) {
            initPano();
        } else {
            const scriptId = 'google-maps-js-sdk';
            if (!document.getElementById(scriptId)) {
                const script = document.createElement('script');
                script.id = scriptId;
                script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&callback=onGoogleMapsSdkReady`;
                script.async = true;
                script.defer = true;
                window.onGoogleMapsSdkReady = () => {
                    this.googleMapsLoaded = true;
                    initPano();
                };
                script.onerror = () => {
                    console.warn('[Spectra GIS] Google Maps SDK failed to load. Falling back to built-in 360 viewer.');
                };
                document.head.appendChild(script);
            }
        }
    },

    closeStreetView() {
        const leftPanel = document.getElementById('streetview-left-panel');
        const btn = document.getElementById('streetview-toggle-btn');
        const label = document.getElementById('streetview-btn-label');

        if (leftPanel) {
            leftPanel.classList.remove('translate-x-0');
            leftPanel.classList.add('-translate-x-full');
        }
        if (btn) {
            btn.classList.remove('border-[#FACC15]', 'text-[#FACC15]');
        }
        if (label) {
            label.textContent = 'Street View';
        }

        if (this.streetViewMarker) {
            this.map.removeLayer(this.streetViewMarker);
            this.streetViewMarker = null;
        }

        this.isStreetViewMode = false;

        setTimeout(() => {
            if (this.map) this.map.invalidateSize();
        }, 320);
    },

    panStreetView(deg) {
        StreetView360.panBy(deg);
    },

    resetStreetViewNorth() {
        StreetView360.resetNorth();
    },

    jumpToStreetViewMarker() {
        if (this.map && StreetView360.lat && StreetView360.lng) {
            this.map.flyTo([StreetView360.lat, StreetView360.lng], 17, { duration: 1.0 });
        }
    },

    reportAtCurrentStreetView() {
        SpectralUI.openReportModalAtPoint(StreetView360.lat, StreetView360.lng, StreetView360.barangay);
    },

    copyCoordinates() {
        const text = `${StreetView360.lat.toFixed(5)}, ${StreetView360.lng.toFixed(5)}`;
        navigator.clipboard.writeText(text).then(() => {
            SpectralUI.showToast(`Coordinates copied: ${text}`);
        }).catch(() => {
            SpectralUI.showToast(`Coordinates: ${text}`);
        });
    },

    clearAnomalyMarker() {
        if (this.anomalyMarker) {
            this.map.removeLayer(this.anomalyMarker);
            this.anomalyMarker = null;
        }
    },

    jumpTo(level) {
        if (!this.map) return;
        const presets = {
            world: { center: [13.0, 122.0], zoom: 3 },
            philippines: { center: [12.8797, 121.7740], zoom: 6 },
            mindanao: { center: [7.8500, 124.9000], zoom: 8 },
            agusan: { center: [8.5000, 125.8500], zoom: 10 },
            san_francisco: { center: [8.5100, 125.9750], zoom: 16 }
        };
        const target = presets[level] || presets.san_francisco;
        this.map.flyTo(target.center, target.zoom, { duration: 1.4 });
    },

    setBaseMap(theme) {
        if (!this.map) return;

        const allLayers = [...Object.values(this.baseLayers)];
        allLayers.forEach(layer => {
            if (this.map.hasLayer(layer)) this.map.removeLayer(layer);
        });

        const layer = this.baseLayers[theme] || this.baseLayers.dark;
        layer.addTo(this.map);
        this.activeBaseMap = theme;

        ['dark', 'standard', 'voyager', 'satellite'].forEach(t => {
            const btn = document.getElementById(`mapbtn-${t}`);
            if (!btn) return;
            if (t === theme) {
                btn.classList.add('active');
                btn.style.color = '';
            } else {
                btn.classList.remove('active');
                btn.style.color = '#9CA3AF';
            }
        });
    },

    toggleLayer(layerName) {
        this.activeFilters[layerName] = !this.activeFilters[layerName];
        try {
            localStorage.setItem('spectral_layer_' + layerName, this.activeFilters[layerName] ? '1' : '0');
        } catch (_) { }
        this.renderAllLayers();
        this.updateLayerToggleButtons();
    },

    updateLayerToggleButtons() {
        ['incidents', 'wards', 'resources', 'safeZones'].forEach(key => {
            const btn = document.getElementById(`layer-btn-${key}`);
            const statusEl = document.getElementById(`layer-status-${key}`);
            const isActive = !!this.activeFilters[key];

            if (btn) btn.classList.toggle('active', isActive);

            if (statusEl) {
                statusEl.textContent = isActive ? 'ON' : 'OFF';
                if (key === 'incidents') {
                    statusEl.className = isActive
                        ? 'sb-label text-[9px] font-mono px-1.5 py-0.5 rounded bg-red-500/15 text-red-400 border border-red-500/30 font-bold'
                        : 'sb-label text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-800/80 text-slate-500 border border-slate-700/50 font-bold';
                } else if (key === 'safeZones') {
                    statusEl.className = isActive
                        ? 'sb-label text-[9px] font-mono px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-bold'
                        : 'sb-label text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-800/80 text-slate-500 border border-slate-700/50 font-bold';
                }
            }
        });
    },

    updateStatsCounters() {
        const incidents = SpectralData.incidents || [];
        const activeIncidents = incidents.filter(i => ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'].includes(i.status)).length;
        const underReview = incidents.filter(i => i.status === 'UNDER INVESTIGATION').length;
        const totalWards = (SpectralData.wardStations && SpectralData.wardStations.length > 0)
            ? SpectralData.wardStations.length
            : ((SpectralData.safeZones && SpectralData.safeZones.length > 0) ? SpectralData.safeZones.length : 0);
        const resolved = incidents.filter(i => i.status === 'RESOLVED').length;

        const elInc = document.getElementById('stat-active-incidents');
        const elInv = document.getElementById('stat-investigating');
        const elWard = document.getElementById('stat-ward-stations');
        const elRes = document.getElementById('stat-resolved');

        if (elInc) elInc.textContent = String(activeIncidents).padStart(2, '0');
        if (elInv) elInv.textContent = String(underReview).padStart(2, '0');
        if (elWard) elWard.textContent = String(totalWards).padStart(2, '0');
        if (elRes) elRes.textContent = String(resolved).padStart(2, '0');

        const elMapInc = document.getElementById('map-hud-incidents');
        const elMapWards = document.getElementById('map-hud-wards');
        const elMapRes = document.getElementById('map-hud-resources');

        if (elMapInc) elMapInc.textContent = activeIncidents;
        if (elMapWards) elMapWards.textContent = totalWards;
        if (elMapRes) elMapRes.textContent = (SpectralData.spectralResources || []).length;
    }
};


// ════════════════════════════════════════════════════════════════════════════
// 3. UI CONTROLLER & WORKFLOW (Vanilla JS)
// ════════════════════════════════════════════════════════════════════════════
const SpectralUI = {
    currentRole: 'reporter',
    selectedIncidentId: null,

    init() {
        SpectralData.init();
        this.populateBarangayOptions();
        this.bindEvents();
    },

    setRole(role) {
        this.currentRole = role;

        const btnReporter = document.getElementById('role-reporter-btn');
        const btnInvestigator = document.getElementById('role-investigator-btn');

        if (btnReporter && btnInvestigator) {
            if (role === 'reporter') {
                btnReporter.className = "px-2.5 py-1 rounded-md text-[11px] font-semibold transition-all active-role-btn";
                btnInvestigator.className = "px-2.5 py-1 rounded-md text-[11px] font-semibold transition-all text-[#9CA3AF] hover:text-white";
            } else {
                btnReporter.className = "px-2.5 py-1 rounded-md text-[11px] font-semibold transition-all text-[#9CA3AF] hover:text-white";
                btnInvestigator.className = "px-2.5 py-1 rounded-md text-[11px] font-semibold transition-all active-role-btn";
            }
        }

        if (this.selectedIncidentId) {
            this.inspectIncident(this.selectedIncidentId);
        }
    },

    populateBarangayOptions() {
        const select = document.getElementById('report-barangay');
        if (!select || select.children.length > 1) return;

        select.innerHTML = '';
        SpectralData.barangays.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.dataset.id = b.id;
            opt.dataset.name = b.name;
            opt.dataset.lat = b.latitude || b.lat || '';
            opt.dataset.lng = b.longitude || b.lng || '';
            opt.textContent = `Brgy. ${b.name}`;
            if (b.name === 'Hubang' || b.id === 9) opt.selected = true;
            select.appendChild(opt);
        });
    },

    inspectIncident(incidentId) {
        this.selectedIncidentId = incidentId;
        const inc = SpectralData.incidents.find(i =>
            i.id === incidentId || i.incident_code === incidentId || i.db_id === incidentId
        );
        if (!inc) return;

        const defaultOverview = document.getElementById('panel-default-overview');
        const inspectorView = document.getElementById('panel-incident-inspector');

        if (inspectorView) {
            if (defaultOverview) defaultOverview.classList.add('hidden');
            inspectorView.classList.remove('hidden');

            if (window.innerWidth < 768) {
                this.expandMobileInfoPanel();
                const mobTitle = document.getElementById('mobile-infopanel-title');
                if (mobTitle) mobTitle.textContent = 'Incident: ' + (inc.incident_code || inc.id);
            }
        }

        this._inspectedLat = inc.latitude;
        this._inspectedLng = inc.longitude;

        if (SpectralMap.map && inc.latitude && inc.longitude) {
            const currentZoom = SpectralMap.map.getZoom();
            const targetZoom = Math.max(currentZoom, 17);
            SpectralMap.map.flyTo([inc.latitude, inc.longitude], targetZoom, { duration: 0.7 });
        }

        const idEl = document.getElementById('insp-id');
        if (idEl) idEl.textContent = inc.incident_code || inc.id;

        const typeEl = document.getElementById('insp-type');
        if (typeEl) typeEl.textContent = inc.type || 'Incident';

        const titleEl = document.getElementById('insp-title');
        if (titleEl) titleEl.textContent = inc.title;

        const assignments = inc.responder_assignments || [];
        const latestAssignment = assignments.length > 0 ? assignments[assignments.length - 1] : null;

        const isResponderAssigned = !!(latestAssignment && latestAssignment.responder_id);
        const isVerified = ['VERIFIED', 'RESOLVED'].includes(inc.status) || inc.investigation_result === 'CONFIRMED' || assignments.length > 0;
        const isInvestigating = isVerified || ['UNDER INVESTIGATION'].includes(inc.status) || (inc.investigations && inc.investigations.length > 0);

        let currentStatusText = 'Pending';
        let badgeClass = 'pending';
        let activeStageIndex = 0;

        if (inc.status === 'RESOLVED') {
            currentStatusText = 'Resolved';
            badgeClass = 'resolved';
            activeStageIndex = 3;
        } else if (isResponderAssigned) {
            currentStatusText = 'Responder Assigned';
            badgeClass = 'verified';
            activeStageIndex = 3;
        } else if (isVerified) {
            currentStatusText = 'Verified';
            badgeClass = 'verified';
            activeStageIndex = 2;
        } else if (isInvestigating) {
            currentStatusText = 'Under Investigation';
            badgeClass = 'investigating';
            activeStageIndex = 1;
        } else {
            currentStatusText = 'Pending';
            badgeClass = 'pending';
            activeStageIndex = 0;
        }

        const statBadge = document.getElementById('insp-status-badge');
        if (statBadge) {
            statBadge.className = `badge-${badgeClass} text-[10px] font-bold px-2 py-0.5 rounded-full font-mono`;
            statBadge.textContent = currentStatusText;
        }

        let progress = 0;
        if (latestAssignment && latestAssignment.response_progress !== null && latestAssignment.response_progress !== undefined) {
            progress = parseInt(latestAssignment.response_progress, 10) || 0;
        } else if (inc.response_progress !== null && inc.response_progress !== undefined) {
            progress = parseInt(inc.response_progress, 10) || 0;
        }
        if (inc.status === 'RESOLVED') {
            progress = 100;
        }
        progress = Math.max(0, Math.min(100, progress));

        const progressPctEl = document.getElementById('insp-progress-pct');
        if (progressPctEl) {
            progressPctEl.textContent = `${progress}%`;
        }
        const progressBarEl = document.getElementById('insp-progress-bar');
        if (progressBarEl) {
            progressBarEl.style.width = `${progress}%`;
        }

        const stageCounterEl = document.getElementById('insp-stage-counter');
        if (stageCounterEl) {
            stageCounterEl.textContent = `STAGE ${activeStageIndex + 1} OF 4`;
        }

        this._buildTimeline(inc, activeStageIndex);

        const investigatorSection = document.getElementById('insp-investigator-actions');
        if (investigatorSection) {
            if (this.currentRole === 'investigator') {
                investigatorSection.classList.remove('hidden');
                const statSel = document.getElementById('investigator-status-select');
                const sevSel = document.getElementById('investigator-severity-select');
                const notesInp = document.getElementById('investigator-notes-input');
                if (statSel) statSel.value = inc.status;
                if (sevSel) sevSel.value = inc.severity;
                if (notesInp) notesInp.value = inc.notes || '';
            } else {
                investigatorSection.classList.add('hidden');
            }
        }
    },

    _buildTimeline(inc, activeStageIndex) {
        const container = document.getElementById('insp-timeline');
        if (!container) return;

        if (activeStageIndex === undefined) {
            const assignments = inc.responder_assignments || [];
            const latestAssignment = assignments.length > 0 ? assignments[assignments.length - 1] : null;
            const isResponderAssigned = !!(latestAssignment && latestAssignment.responder_id);
            const isVerified = ['VERIFIED', 'RESOLVED'].includes(inc.status) || inc.investigation_result === 'CONFIRMED' || assignments.length > 0;
            const isInvestigating = isVerified || ['UNDER INVESTIGATION'].includes(inc.status) || (inc.investigations && inc.investigations.length > 0);

            if (isResponderAssigned) {
                activeStageIndex = 3;
            } else if (isVerified) {
                activeStageIndex = 2;
            } else if (isInvestigating) {
                activeStageIndex = 1;
            } else {
                activeStageIndex = 0;
            }
        }

        const stages = [
            { label: 'Pending', order: 0 },
            { label: 'Under Investigation', order: 1 },
            { label: 'Verified', order: 2 },
            { label: 'Responder Assigned', order: 3 }
        ];

        let html = '<div class="space-y-2 text-xs pt-1 font-mono">';
        stages.forEach((stage, idx) => {
            const isCompleted = activeStageIndex > idx;
            const isCurrent = activeStageIndex === idx;

            let dotClass = 'bg-slate-600';
            let textClass = 'text-slate-500';
            let tagHtml = '';

            if (isCompleted) {
                dotClass = 'bg-emerald-400 ring-2 ring-emerald-500/20';
                textClass = 'text-white font-bold';
                tagHtml = '<span class="text-[9px] text-emerald-400 font-bold ml-auto">✓</span>';
            } else if (isCurrent) {
                dotClass = 'bg-[#8B5CF6] ring-2 ring-[#8B5CF6]/40 animate-pulse';
                textClass = 'text-[#A78BFA] font-bold';
                tagHtml = '<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-[#8B5CF6]/20 text-[#A78BFA] ml-auto">CURRENT</span>';
            }

            html += `
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full ${dotClass}"></span>
                    <span class="${textClass}">${stage.label}</span>
                    ${tagHtml}
                </div>
            `;

            if (idx < stages.length - 1) {
                html += '<div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>';
            }
        });
        html += '</div>';

        container.innerHTML = html;
    },

    closeInspector() {
        this.selectedIncidentId = null;
        const defaultOverview = document.getElementById('panel-default-overview');
        const inspectorView = document.getElementById('panel-incident-inspector');

        if (defaultOverview) defaultOverview.classList.remove('hidden');
        if (inspectorView) inspectorView.classList.add('hidden');

        const mobTitle = document.getElementById('mobile-infopanel-title');
        if (mobTitle) mobTitle.textContent = 'System Summary';
    },

    toggleMobileInfoPanel() {
        const panel = document.getElementById('spectral-infopanel');
        if (!panel) return;
        const isCollapsed = panel.classList.contains('h-10');
        if (isCollapsed) {
            this.expandMobileInfoPanel();
        } else {
            this.collapseMobileInfoPanel();
        }
    },

    expandMobileInfoPanel() {
        const panel = document.getElementById('spectral-infopanel');
        if (!panel) return;
        panel.classList.remove('h-10');
        panel.classList.add('h-[50vh]');
        const hint = document.getElementById('mobile-infopanel-hint');
        const icon = document.getElementById('mobile-infopanel-icon');
        if (hint) hint.textContent = 'Collapse';
        if (icon) icon.style.transform = 'rotate(180deg)';
    },

    collapseMobileInfoPanel() {
        const panel = document.getElementById('spectral-infopanel');
        if (!panel) return;
        panel.classList.remove('h-[50vh]');
        panel.classList.add('h-10');
        const hint = document.getElementById('mobile-infopanel-hint');
        const icon = document.getElementById('mobile-infopanel-icon');
        if (hint) hint.textContent = 'Expand';
        if (icon) icon.style.transform = 'rotate(0deg)';
    },

    getStatusBadgeClass(status) {
        return status === 'PENDING' ? 'pending'
            : status === 'UNDER INVESTIGATION' ? 'investigating'
                : status === 'VERIFIED' ? 'verified'
                    : status === 'RESOLVED' ? 'resolved'
                        : 'pending';
    },

    async saveInvestigatorChanges() {
        if (!this.selectedIncidentId) return;
        const inc = SpectralData.incidents.find(i =>
            i.id === this.selectedIncidentId || i.incident_code === this.selectedIncidentId || i.db_id === this.selectedIncidentId
        );
        if (!inc) return;

        const newStatus = document.getElementById('investigator-status-select').value;
        const newSeverity = document.getElementById('investigator-severity-select').value;
        const newNotes = document.getElementById('investigator-notes-input').value;

        inc.status = newStatus;
        inc.severity = newSeverity;
        inc.notes = newNotes;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (inc.db_id) {
                await fetch(`/api/spectral/incidents/${inc.db_id}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        severity: newSeverity,
                        notes: newNotes
                    })
                });
            }
        } catch (err) {
            console.warn('API sync fallback to local state:', err);
        }

        SpectralMap.renderIncidents();
        this.inspectIncident(this.selectedIncidentId);
        this.showToast(`Incident ${inc.id} updated to ${newStatus}`);
    },

    async lookupAddress(lat, lng) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (isNaN(lat) || isNaN(lng)) return;

        const loadingEl = document.getElementById('report-geocode-loading');
        const errorEl = document.getElementById('report-geocode-error');
        const errTextEl = document.getElementById('report-geocode-error-text');
        const statusEl = document.getElementById('report-geocode-status');
        const badgeEl = document.getElementById('report-geocode-badge');
        const addrInput = document.getElementById('report-address') || document.getElementById('report-resolved-location');
        const bReview = document.getElementById('report-review-barangay');
        const mReview = document.getElementById('report-review-municipality');
        const cReview = document.getElementById('report-review-coords');
        const bSelect = document.getElementById('report-barangay');
        const confirmCb = document.getElementById('report-location-confirmed');

        if (loadingEl) loadingEl.classList.remove('hidden');
        if (errorEl) errorEl.classList.add('hidden');
        if (statusEl) {
            statusEl.textContent = 'Resolving address...';
            statusEl.style.color = '#A78BFA';
        }
        if (badgeEl) {
            badgeEl.textContent = 'Looking up...';
            badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono bg-purple-500/10 text-purple-400 border border-purple-500/20';
        }
        if (addrInput) addrInput.value = `Looking up address for (${lat.toFixed(5)}, ${lng.toFixed(5)})...`;
        if (cReview) cReview.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

        try {
            const res = await fetch(`/api/spectral/reverse-geocode?lat=${lat}&lng=${lng}`, {
                headers: { 'Accept': 'application/json' },
                signal: AbortSignal.timeout(6500)
            });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    if (loadingEl) loadingEl.classList.add('hidden');
                    if (addrInput) addrInput.value = data.address || '';
                    const bName = data.barangay_name || data.barangay || 'San Francisco';
                    const bDisplay = bName.startsWith('Barangay ') ? bName : `Brgy. ${bName}`;
                    if (bReview) bReview.textContent = bDisplay;
                    if (mReview) mReview.textContent = `${data.municipality || 'San Francisco'}, ${data.province || 'Agusan del Sur'}`;
                    if (cReview) cReview.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    if (statusEl) {
                        statusEl.textContent = `✓ ${bDisplay}`;
                        statusEl.style.color = '#34D399';
                    }
                    if (badgeEl) {
                        badgeEl.textContent = 'Verified';
                        badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                    }
                    if (confirmCb) confirmCb.checked = true;

                    if (bSelect && data.barangay_id) {
                        for (let opt of bSelect.options) {
                            if (opt.value == data.barangay_id || (opt.dataset && opt.dataset.id == data.barangay_id)) {
                                bSelect.value = opt.value;
                                break;
                            }
                        }
                    } else if (bSelect && bName) {
                        for (let opt of bSelect.options) {
                            if (opt.text && opt.text.toLowerCase().includes(bName.toLowerCase())) {
                                bSelect.value = opt.value;
                                break;
                            }
                        }
                    }
                    return data;
                }
            }
            throw new Error('Reverse geocode returned unsuccessful');
        } catch (err) {
            console.warn('[SpectralUI] Reverse geocode lookup fallback:', err.message);
            if (loadingEl) loadingEl.classList.add('hidden');
            if (errorEl) {
                if (errTextEl) errTextEl.textContent = `Address lookup unavailable. Kept exact coordinates (${lat.toFixed(4)}, ${lng.toFixed(4)}).`;
                errorEl.classList.remove('hidden');
            }
            if (badgeEl) {
                badgeEl.textContent = 'Coordinates Kept';
                badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono bg-amber-500/10 text-amber-400 border border-amber-500/20';
            }
            if (statusEl) {
                statusEl.textContent = '⚠ Offline fallback';
                statusEl.style.color = '#FBBF24';
            }

            let fallbackName = 'San Francisco';
            if (window.SpectralData && window.SpectralData.barangays && window.SpectralData.barangays.length > 0) {
                let closest = null;
                let minDist = Infinity;
                window.SpectralData.barangays.forEach(b => {
                    const bLat = parseFloat(b.latitude || b.lat);
                    const bLng = parseFloat(b.longitude || b.lng);
                    if (!isNaN(bLat) && !isNaN(bLng)) {
                        const d = Math.pow(bLat - lat, 2) + Math.pow(bLng - lng, 2);
                        if (d < minDist) { minDist = d; closest = b; }
                    }
                });
                if (closest) {
                    fallbackName = closest.name;
                    if (bSelect) {
                        for (let opt of bSelect.options) {
                            if (opt.value == closest.id || (opt.text && opt.text.includes(closest.name))) {
                                bSelect.value = opt.value;
                                break;
                            }
                        }
                    }
                }
            } else if (bSelect && bSelect.selectedIndex >= 0) {
                fallbackName = bSelect.options[bSelect.selectedIndex].text.replace(/^Brgy\.?\s*/i, '');
            }

            const fallbackDisplay = fallbackName.startsWith('Barangay ') ? fallbackName : `Brgy. ${fallbackName}`;
            const fallbackAddress = `${fallbackDisplay}, San Francisco, Agusan del Sur, Philippines`;
            if (addrInput) addrInput.value = fallbackAddress;
            if (bReview) bReview.textContent = fallbackDisplay;
            if (mReview) mReview.textContent = 'San Francisco, Agusan del Sur';
            if (cReview) cReview.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            if (confirmCb) confirmCb.checked = true;
        }
    },

    openReportModal() {
        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.remove('hidden');

        const bSelect = document.getElementById('report-barangay');
        const latInp = document.getElementById('report-lat');
        const lngInp = document.getElementById('report-lng');

        const updateSafeZoneProtectionState = (lat, lng) => {
            const protection = SpectralData.checkSafeZoneProtection(lat, lng);
            const alertEl = document.getElementById('report-safezone-alert');
            const confirmCb = document.getElementById('report-location-confirmed');
            const statusEl = document.getElementById('report-geocode-status');

            if (protection) {
                if (alertEl) alertEl.classList.remove('hidden');
                if (confirmCb) confirmCb.checked = false;
                if (statusEl) {
                    statusEl.textContent = '⚠️ Safe Zone Protected';
                    statusEl.style.color = '#EF4444';
                }
                return true;
            } else {
                if (alertEl) alertEl.classList.add('hidden');
                return false;
            }
        };
        this.updateSafeZoneProtectionState = updateSafeZoneProtectionState;

        if (bSelect && !bSelect._spectralCoordListenerAttached) {
            bSelect._spectralCoordListenerAttached = true;
            bSelect.addEventListener('change', () => {
                const opt = bSelect.options[bSelect.selectedIndex];
                if (opt && opt.dataset.lat && opt.dataset.lng) {
                    const lat = parseFloat(opt.dataset.lat);
                    const lng = parseFloat(opt.dataset.lng);
                    if (latInp) latInp.value = lat.toFixed(6);
                    if (lngInp) lngInp.value = lng.toFixed(6);
                    updateSafeZoneProtectionState(lat, lng);
                    this.lookupAddress(lat, lng);
                }
            });
        }

        const _onCoordChange = (() => {
            let timer = null;
            return () => {
                const lat = parseFloat(document.getElementById('report-lat')?.value);
                const lng = parseFloat(document.getElementById('report-lng')?.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    updateSafeZoneProtectionState(lat, lng);
                }
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const l = parseFloat(document.getElementById('report-lat')?.value);
                    const g = parseFloat(document.getElementById('report-lng')?.value);
                    if (!isNaN(l) && !isNaN(g)) {
                        this.lookupAddress(l, g);
                    }
                }, 500);
            };
        })();

        if (latInp && !latInp._spectralCoordListenerAttached) {
            latInp._spectralCoordListenerAttached = true;
            latInp.addEventListener('input', _onCoordChange);
            latInp.addEventListener('change', _onCoordChange);
        }
        if (lngInp && !lngInp._spectralCoordListenerAttached) {
            lngInp._spectralCoordListenerAttached = true;
            lngInp.addEventListener('input', _onCoordChange);
            lngInp.addEventListener('change', _onCoordChange);
        }

        const curLat = parseFloat(latInp?.value);
        const curLng = parseFloat(lngInp?.value);

        if (isNaN(curLat) || isNaN(curLng)) {
            const alertEl = document.getElementById('report-safezone-alert');
            if (alertEl) alertEl.classList.add('hidden');
        } else {
            updateSafeZoneProtectionState(curLat, curLng);
        }

        const resolvedInput = document.getElementById('report-address') || document.getElementById('report-resolved-location');
        if (!resolvedInput || !resolvedInput.value || resolvedInput.value.startsWith('Looking up')) {
            if (!isNaN(curLat) && !isNaN(curLng)) {
                this.lookupAddress(curLat, curLng);
            }
        }
    },

    closeReportModal() {
        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.add('hidden');
        const alertEl = document.getElementById('report-safezone-alert');
        if (alertEl) alertEl.classList.add('hidden');
    },

    handleEvidenceUpload(input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const previewContainer = document.getElementById('report-evidence-preview-container');
            const previewImg = document.getElementById('report-evidence-preview');
            if (previewImg) previewImg.src = e.target.result;
            if (previewContainer) previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    },

    removeEvidencePreview() {
        const fileInput = document.getElementById('report-evidence-file');
        const previewImg = document.getElementById('report-evidence-preview');
        const previewContainer = document.getElementById('report-evidence-preview-container');

        if (fileInput) fileInput.value = '';
        if (previewImg) previewImg.src = '';
        if (previewContainer) previewContainer.classList.add('hidden');
    },

    async submitReport(e) {
        e.preventDefault();

        const form = document.getElementById('report-form');
        const submitBtn = document.getElementById('report-submit-btn');
        const spinner = document.getElementById('report-submit-spinner');
        const label = document.getElementById('report-submit-label');
        const overlay = document.getElementById('report-processing-overlay');

        if (submitBtn && submitBtn.disabled) return false;

        const locationConfirmed = document.getElementById('report-location-confirmed');
        if (locationConfirmed && !locationConfirmed.checked) {
            if (typeof this.showToast === 'function') {
                this.showToast('Please review and confirm the incident location before submitting.');
            } else {
                alert('Please review and confirm the incident location before submitting.');
            }
            locationConfirmed.focus();
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (label) label.textContent = 'Submitting...';
        if (form) {
            form.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = true;
            });
        }

        if (overlay) overlay.classList.remove('hidden');

        const unlockUI = () => {
            if (overlay) overlay.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
            if (spinner) spinner.classList.add('hidden');
            if (label) label.textContent = 'Submit Report';
            if (form) {
                form.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = false;
                });
            }
        };

        const type = document.getElementById('report-type').value;
        const title = document.getElementById('report-title').value;
        const barangaySelect = document.getElementById('report-barangay');
        const selectedOpt = barangaySelect?.options[barangaySelect.selectedIndex];
        let barangayId = selectedOpt?.dataset?.id || (barangaySelect?.value && !isNaN(barangaySelect.value) ? parseInt(barangaySelect.value) : null);
        let barangayName = selectedOpt?.dataset?.name || selectedOpt?.textContent?.replace(/^Brgy\.?\s*/i, '').trim();

        if (!barangayName || !isNaN(barangayName)) {
            const foundB = SpectralData.barangays.find(b => String(b.id) === String(barangayId));
            barangayName = foundB ? foundB.name : 'Hubang';
        }
        if (!barangayId) {
            const foundB = SpectralData.barangays.find(b => b.name.toLowerCase() === (barangayName || '').toLowerCase());
            if (foundB) barangayId = foundB.id;
        }

        const lat = parseFloat(document.getElementById('report-lat').value);
        const lng = parseFloat(document.getElementById('report-lng').value);

        if (!isNaN(lat) && !isNaN(lng)) {
            const preCheck = SpectralData.checkSafeZoneProtection(lat, lng);
            if (preCheck) {
                const alertEl = document.getElementById('report-safezone-alert');
                if (alertEl) {
                    alertEl.classList.remove('hidden');
                    alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                this.showToast(preCheck.message);
                unlockUI();
                return;
            }
        }

        if (isNaN(lat) || isNaN(lng)) {
            this.showToast('Please select a location on the map before submitting.');
            unlockUI();
            return;
        }
        const severity = document.getElementById('report-severity').value;
        const description = document.getElementById('report-desc').value;
        const nowIso = new Date().toISOString();
        const authUserName = window.INITIAL_SPECTRAL_STATE?.auth_user || 'Citizen Field Reporter';
        const fileInput = document.getElementById('report-evidence-file');
        const previewImg = document.getElementById('report-evidence-preview');

        let maxCodeNum = 0;
        (SpectralData.incidents || []).forEach(i => {
            const code = i.incident_code || i.id || '';
            const m = String(code).match(/SF-INC-(\d+)/i);
            if (m) {
                const n = parseInt(m[1], 10);
                if (n > maxCodeNum) maxCodeNum = n;
            }
        });
        const nextNum = Math.max(maxCodeNum + 1, (SpectralData.incidents || []).length + 1);
        const newCode = `SF-INC-${String(nextNum).padStart(3, '0')}`;

        let formattedDate = nowIso;
        try {
            const d = new Date(nowIso);
            formattedDate = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                + ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        } catch (_) { }

        const newIncident = {
            id: newCode,
            db_id: null,
            type: type,
            incident_code: newCode,
            title: title,
            description: description,
            barangay: barangayName,
            barangay_id: barangayId,
            municipality: 'San Francisco',
            province: 'Agusan del Sur',
            latitude: lat,
            longitude: lng,
            severity: severity,
            status: 'PENDING',
            reported_at: formattedDate,
            reported_by: authUserName,
            evidence: previewImg && previewImg.src && !previewImg.src.includes('data:,') ? previewImg.src : null,
            investigations: [],
            notes: null
        };

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('#report-form input[name="_token"]')?.value;
            const formData = new FormData();
            if (token) formData.append('_token', token);
            formData.append('incident_type', type);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('latitude', lat);
            formData.append('longitude', lng);
            formData.append('incident_date', nowIso);
            formData.append('severity', severity);
            if (barangayId) formData.append('barangay_id', barangayId);
            if (barangayName) formData.append('barangay', barangayName);
            if (fileInput && fileInput.files[0]) {
                formData.append('evidence', fileInput.files[0]);
            }

            const res = await fetch('/incidents', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (res.ok) {
                const json = await res.json();
                if (json.data) {
                    newIncident.db_id = json.data.id;
                    newIncident.id = json.data.incident_code || newCode;
                }
            } else {
                const errorData = await res.json().catch(() => ({}));
                console.error('Failed to submit incident:', res.status, errorData);

                if (res.status === 422 && errorData.title === 'Safe Zone Protected') {
                    const alertEl = document.getElementById('report-safezone-alert');
                    if (alertEl) {
                        alertEl.classList.remove('hidden');
                        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    this.showToast('Safe Zone Protected: ' + (errorData.message || 'You cannot report an incident within this protected radius.'));
                    unlockUI();
                    return;
                }

                let reason = '';
                if (errorData.errors) {
                    reason = ': ' + Object.values(errorData.errors).flat().join(' ');
                } else if (res.status === 419) {
                    reason = ': Session expired or CSRF token missing. Refresh the page.';
                } else if (res.status === 401) {
                    reason = ': ' + (errorData.message || 'Please log in again.');
                } else if (res.status === 404) {
                    reason = ': Incident submission endpoint not found.';
                } else {
                    reason = errorData.message ? ': ' + errorData.message : ' (Server error ' + res.status + ')';
                }

                this.showToast('Submission failed' + reason);
                unlockUI();
                return;
            }
        } catch (err) {
            console.error('API post error:', err);
            this.showToast('Network error while reporting incident.');
            unlockUI();
            return;
        }

        document.getElementById('report-form').reset();
        this.removeEvidencePreview();
        this.closeReportModal();
        unlockUI();

        if (window.location.pathname.includes('my-reports')) {
            window.location.reload();
            return;
        }

        SpectralData.incidents.unshift(newIncident);

        if (window.SpectralMap && typeof window.SpectralMap.addIncidentMarker === 'function') {
            window.SpectralMap.addIncidentMarker(newIncident);
        } else {
            try {
                if (typeof SpectralMap.renderIncidents === 'function') SpectralMap.renderIncidents();
                if (typeof SpectralMap.updateStatsCounters === 'function') SpectralMap.updateStatsCounters();
            } catch (_) { }
            if (SpectralMap.map) {
                SpectralMap.map.flyTo([lat, lng], 15, { duration: 1.0 });
            }
        }
        try { this.inspectIncident(newIncident.id); } catch (_) { }
        this.showToast(`Incident ${newIncident.id} recorded in San Francisco Node.`);
    },

        showToast(message) {
        let toast = document.getElementById('spectral-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'spectral-toast';
            // 📏 Smaller text + tighter padding
            toast.className = 'fixed bottom-4 right-4 z-[9999] max-w-xs px-3 py-2 rounded-lg bg-[#1B222C] border border-[#8B5CF6]/60 text-white text-[10.5px] font-medium leading-snug shadow-xl flex items-start gap-2 transition-all opacity-0 translate-y-2';
            document.body.appendChild(toast);
        }

        toast.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;"><polyline points="20 6 9 17 4 12"/></svg><span class="flex-1">${message}</span>`;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        clearTimeout(toast._spectralTimeout);
        toast._spectralTimeout = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
        }, 4000);
    },

    openReportModalAtPoint(lat, lng, barangayName) {
        SpectralMap.clearAnomalyMarker();

        const latInput = document.getElementById('report-lat');
        const lngInput = document.getElementById('report-lng');
        const barangaySelect = document.getElementById('report-barangay');
        const resolvedInput = document.getElementById('report-resolved-location');
        const geocodeStatus = document.getElementById('report-geocode-status');

        if (latInput) latInput.value = parseFloat(lat).toFixed(6);
        if (lngInput) lngInput.value = parseFloat(lng).toFixed(6);
        if (barangaySelect && barangayName) {
            SpectralMap.selectBarangayOption(barangaySelect, barangayName);
        }

        if (geocodeStatus) {
            geocodeStatus.textContent = 'Resolving address...';
            geocodeStatus.className = 'text-[9px] font-mono text-amber-400 animate-pulse';
        }
        if (resolvedInput) {
            resolvedInput.value = `Resolving address for ${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}...`;
        }

        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.remove('hidden');

        SpectralMap.reverseGeocode(parseFloat(lat), parseFloat(lng)).then(geo => {
            if (resolvedInput) {
                resolvedInput.value = geo.address || geo.display;
            }
            if (geocodeStatus) {
                geocodeStatus.textContent = 'Location matched: Brgy. ' + (geo.barangay_name || barangayName);
                geocodeStatus.className = 'text-[9px] font-mono text-emerald-400';
            }
            if (barangaySelect && (geo.barangay_id || geo.barangay_name)) {
                SpectralMap.selectBarangayOption(barangaySelect, geo.barangay_id || geo.barangay_name);
            }
        });
    },

    bindEvents() {
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (SpectralMap.isPickingLocation) SpectralMap.cancelLocationPicking();
                if (SpectralMap.isStreetViewMode) SpectralMap.closeStreetView();
                SpectralMap.clearAnomalyMarker();
            }
        });
    }
};


// ════════════════════════════════════════════════════════════════════════════
// 4. INITIALIZE ON DOM READY
// ════════════════════════════════════════════════════════════════════════════
window.SpectralMap = SpectralMap;
window.SpectralUI = SpectralUI;

function bootSpectralApp() {
    try {
        if (typeof SpectralData !== 'undefined') SpectralData.init();
    } catch (e) {
        console.error('[SpectralData] init error:', e);
    }

    try {
        if (window.SpectralUI) SpectralUI.init();
    } catch (e) {
        console.error('[SpectralUI] init error:', e);
    }

    try {
        if (window.SpectralMap) {
            SpectralMap.init();
        }
    } catch (e) {
        console.error('[SpectralMap] init error:', e);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSpectralApp);
} else {
    bootSpectralApp();
}
