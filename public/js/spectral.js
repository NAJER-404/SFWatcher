/**
 * ECTO//NET â€” Spectral Resource & Ward Network GIS Engine
 * Scenario 3: Enterprise Geographic Supernatural Defense & Emergency Monitoring
 * Primary Scope: San Francisco, Agusan del Sur, Philippines
 */

'use strict';

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 1. DATA STORE (Synchronized with Laravel Backend & Initial State)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
const SpectralData = {
    node: {
        name: "San Francisco Node",
        sector: "Agusan del Sur",
        province: "Agusan del Sur",
        country: "Philippines",
        coordinates: [8.5100, 125.9750],
        status: "OPERATIONAL"
    },

    // 27 Official Barangays of San Francisco, Agusan del Sur
    barangays: [
        { id: 1,  name: "Alegria",     lat: 8.4720, lng: 125.9620 },
        { id: 2,  name: "Bayugan 2",   lat: 8.5520, lng: 125.9380 },
        { id: 3,  name: "Bitan-agan",  lat: 8.4890, lng: 125.9920 },
        { id: 4,  name: "Borbon",      lat: 8.5260, lng: 125.9410 },
        { id: 5,  name: "Buenasuerte", lat: 8.4610, lng: 125.9810 },
        { id: 6,  name: "Caimpugan",   lat: 8.5710, lng: 125.9120 },
        { id: 7,  name: "Das-agan",    lat: 8.5380, lng: 125.9910 },
        { id: 8,  name: "Ebro",        lat: 8.4980, lng: 125.9420 },
        { id: 9,  name: "Hubang",      lat: 8.5310, lng: 125.9730 },
        { id: 10, name: "Karaus",      lat: 8.5180, lng: 125.9840 },
        { id: 11, name: "Ladgadan",    lat: 8.5020, lng: 125.9540 },
        { id: 12, name: "Lapinigan",   lat: 8.4420, lng: 125.9680 },
        { id: 13, name: "Lucac",       lat: 8.5290, lng: 125.9980 },
        { id: 14, name: "Mate",        lat: 8.4780, lng: 125.9320 },
        { id: 15, name: "New Visayas", lat: 8.5440, lng: 125.9580 },
        { id: 16, name: "Ormaca",      lat: 8.4550, lng: 125.9490 },
        { id: 17, name: "Pasta",       lat: 8.4830, lng: 125.9730 },
        { id: 18, name: "Pisa-an",     lat: 8.5025, lng: 125.9782 },
        { id: 19, name: "Barangay 1",  lat: 8.5098, lng: 125.9780 },
        { id: 20, name: "Barangay 2",  lat: 8.5085, lng: 125.9760 },
        { id: 21, name: "Barangay 3",  lat: 8.5070, lng: 125.9775 },
        { id: 22, name: "Barangay 4",  lat: 8.5055, lng: 125.9790 },
        { id: 23, name: "Barangay 5",  lat: 8.5065, lng: 125.9790 },
        { id: 24, name: "Rizal",       lat: 8.5340, lng: 125.9280 },
        { id: 25, name: "San Isidro",  lat: 8.4910, lng: 125.9610 },
        { id: 26, name: "Santa Ana",   lat: 8.5150, lng: 125.9520 },
        { id: 27, name: "Tagapua",     lat: 8.5630, lng: 125.9450 }
    ],

    incidents: [],
    wardStations: [],
    spectralResources: [],
    safeZones: [
        {
            id: "SAFE-01",
            name: "San Francisco Municipal Gymnasium Sanctuary",
            barangay: "Barangay 1",
            latitude: 8.5098,
            longitude: 125.9780,
            capacity: 2500,
            barrier_integrity: "100%",
            radius: 350
        },
        {
            id: "SAFE-02",
            name: "Hubang Transport Safe Haven",
            barangay: "Hubang",
            latitude: 8.5310,
            longitude: 125.9730,
            capacity: 1200,
            barrier_integrity: "98%",
            radius: 400
        }
    ],

    init() {
        if (window.INITIAL_SPECTRAL_STATE) {
            if (window.INITIAL_SPECTRAL_STATE.incidents && window.INITIAL_SPECTRAL_STATE.incidents.length > 0) {
                this.incidents = window.INITIAL_SPECTRAL_STATE.incidents.map(inc => {
                    // Format date nicely: "April 13, 2026 · 11:13 AM"
                    let reportedAt = inc.incident_date || inc.created_at;
                    try {
                        const d = new Date(reportedAt);
                        reportedAt = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                            + ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    } catch (_) {}

                    return {
                        id:            inc.incident_code || `SF-INC-${inc.id}`,
                        incident_code: inc.incident_code || `SF-INC-${inc.id}`,  // Bug #6 fix
                        db_id:         inc.id,
                        type:          inc.incident_type,
                        title:         inc.title,
                        description:   inc.description,
                        barangay:      inc.barangay ? inc.barangay.name : 'Hubang',
                        municipality:  inc.barangay ? inc.barangay.municipality : 'San Francisco',
                        province:      inc.barangay ? inc.barangay.province : 'Agusan del Sur',
                        latitude:      parseFloat(inc.latitude),
                        longitude:     parseFloat(inc.longitude),
                        severity:      inc.severity,
                        status:        inc.status,
                        anomaly_hp:            inc.anomaly_hp,
                        anomaly_max_hp:        inc.anomaly_max_hp,
                        response_progress:     inc.response_progress,
                        response_status:       inc.response_status,
                        responder_assignments: inc.responder_assignments || [],
                        reported_at:   reportedAt,       // Bug #5 fix — human-readable date
                        reported_by:   inc.reporter ? inc.reporter.name : 'Civilian Observer',
                        evidence:      (inc.evidence && inc.evidence.length > 0)
                                          ? (inc.evidence[0].file_path.startsWith('http')
                                              ? inc.evidence[0].file_path
                                              : '/storage/' + inc.evidence[0].file_path)
                                          : null,
                        investigations: inc.investigations || [],  // Bug #7 fix
                        notes:         inc.notes
                    };
                });
            }
            if (window.INITIAL_SPECTRAL_STATE.wards && window.INITIAL_SPECTRAL_STATE.wards.length > 0) {
                this.wardStations = window.INITIAL_SPECTRAL_STATE.wards.map(w => ({
                    id: w.code || `WS-${w.id}`,
                    code: w.code,
                    name: w.name,
                    barangay: w.barangay ? w.barangay.name : 'San Francisco',
                    latitude: parseFloat(w.latitude),
                    longitude: parseFloat(w.longitude),
                    power: w.energy_level || 90,
                    shield_level: w.shield_level || 95,
                    radius_meters: w.radius_meters || 1000,
                    status: w.status || 'active',
                    frequency: w.frequency || '432.8 THz'
                }));
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
            if (window.INITIAL_SPECTRAL_STATE.barangays && window.INITIAL_SPECTRAL_STATE.barangays.length > 0) {
                this.barangays = window.INITIAL_SPECTRAL_STATE.barangays.map(b => ({
                    id: b.id,
                    name: b.name,
                    lat: parseFloat(b.latitude || b.lat),
                    lng: parseFloat(b.longitude || b.lng)
                }));
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
    yaw: 172,          // Horizontal view angle in degrees (0 - 360)
    targetYaw: 172,
    pitch: 0,          // Vertical tilt (-25 to +25)
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

        // Time seed — smooth daylight cycle (noon-ish: 0.8)
        const timeOfDay = 0.82;
        const horizonY = height * 0.42 + (this.pitch * 2.5);

        // ── 1. SKY ─────────────────────────────────────────────────────────
        const skyTop    = `hsl(207,${60+timeOfDay*20}%,${18+timeOfDay*38}%)`;
        const skyMid    = `hsl(206,${55+timeOfDay*15}%,${28+timeOfDay*32}%)`;
        const skyHorizon= `hsl(205,${40+timeOfDay*10}%,${48+timeOfDay*22}%)`;
        const skyGrad = ctx.createLinearGradient(0, 0, 0, horizonY);
        skyGrad.addColorStop(0, skyTop);
        skyGrad.addColorStop(0.55, skyMid);
        skyGrad.addColorStop(1, skyHorizon);
        ctx.fillStyle = skyGrad;
        ctx.fillRect(0, 0, width, horizonY + 2);

        // ── 2. SUN (mid-morning Philippine sun) ───────────────────────────
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
            // Sun disc
            ctx.fillStyle = 'rgba(255,252,235,0.98)';
            ctx.beginPath();
            ctx.arc(sunX, sunY, 14, 0, Math.PI * 2);
            ctx.fill();
        }

        // ── 3. CLOUDS (scattered tropical cumulus) ─────────────────────────
        const cloudSeeds = [0.12, 0.35, 0.62, 0.80];
        cloudSeeds.forEach((seed, ci) => {
            const worldOffset = ((seed * 360 - this.yaw + 720) % 360);
            const cang = worldOffset > 180 ? worldOffset - 360 : worldOffset;
            if (Math.abs(cang) > 90) return;
            const cx2 = (width / 2) + (cang / 90) * width * 0.65;
            const cy2 = horizonY * (0.2 + seed * 0.25);
            ctx.globalAlpha = 0.55;
            ctx.fillStyle = '#DDEEFF';
            [[0,0,30],[-22,8,22],[22,8,22],[-10,16,18],[10,16,18]].forEach(([ox,oy,r]) => {
                ctx.beginPath();
                ctx.arc(cx2+ox, cy2+oy, r, 0, Math.PI*2);
                ctx.fill();
            });
            ctx.globalAlpha = 1;
        });

        // ── 4. MOUNTAIN RIDGE (Caraga highlands silhouettes) ───────────────
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
        drawMtnLayer('rgba(60,95,85,0.65)',  28, 22, 0.032, 0);
        drawMtnLayer('rgba(40,75,60,0.75)',  14, 18, 0.048, 1.2);
        drawMtnLayer('rgba(32,58,44,0.85)',   6, 12, 0.065, 2.4);

        // ── 5. GROUND / SHOULDER / SIDEWALK ────────────────────────────────
        // Wide concrete / asphalt road — lighter in daytime
        const groundGrad = ctx.createLinearGradient(0, horizonY, 0, height);
        groundGrad.addColorStop(0, '#7A8090');
        groundGrad.addColorStop(0.15, '#5C636D');
        groundGrad.addColorStop(0.6, '#3E434B');
        groundGrad.addColorStop(1, '#2A2D32');
        ctx.fillStyle = groundGrad;
        ctx.fillRect(0, horizonY, width, height - horizonY);

        // ── 6. ROAD SURFACE (3-D perspective wide national highway) ────────
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

            // Asphalt — warm grey Philippine concrete
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

            // Left roadside grass strip
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

            // Right roadside
            ctx.fillStyle = grassL;
            ctx.beginPath();
            ctx.moveTo(rVpR, horizonY);
            ctx.lineTo(width, horizonY);
            ctx.lineTo(width, height);
            ctx.lineTo(rright, height);
            ctx.closePath();
            ctx.fill();

            // White edge lines
            ctx.strokeStyle = 'rgba(255,255,255,0.85)';
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(rVpL, horizonY); ctx.lineTo(rleft, height);
            ctx.moveTo(rVpR, horizonY); ctx.lineTo(rright, height);
            ctx.stroke();

            // Lane divider (double yellow centerline like PH highways)
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

            // Lane dashes each side
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

            // Road name on asphalt (perspective)
            ctx.save();
            ctx.translate((rleft + rright) / 2, height * 0.76);
            ctx.transform(1, 0, (roadAng / 55) * 0.3, 0.42, 0, 0);
            ctx.font = 'bold 13px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillStyle = 'rgba(255,255,255,0.45)';
            ctx.fillText(this.roadName.toUpperCase(), 0, 0);
            ctx.restore();

            // ── UTILITY POLES (both sides, like in Burgos St photo) ─────────
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

                // Pole trunk
                ctx.strokeStyle = '#4A4A4A';
                ctx.lineWidth = Math.max(2, 4 * frac);
                ctx.beginPath();
                ctx.moveTo(px, py);
                ctx.lineTo(px + (isLeft ? -2 : 2) * frac, py - poleH);
                ctx.stroke();

                // Cross-arm
                const armW = 16 * frac;
                const armY = py - poleH * 0.95;
                const armX = px + (isLeft ? -2 : 2) * frac;
                ctx.strokeStyle = '#555';
                ctx.lineWidth = Math.max(1, 2 * frac);
                ctx.beginPath();
                ctx.moveTo(armX - armW, armY);
                ctx.lineTo(armX + armW, armY);
                ctx.stroke();

                // Insulators (small knobs)
                ['rgba(80,80,80,0.9)', 'rgba(80,80,80,0.9)'].forEach((c, ii) => {
                    ctx.fillStyle = c;
                    ctx.beginPath();
                    ctx.arc(armX + (ii === 0 ? -armW : armW), armY, Math.max(1, 2 * frac), 0, Math.PI * 2);
                    ctx.fill();
                });

                // Power lines stretching toward horizon
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

            // ── VEHICLE: Parked SUV/sedan on left shoulder ─────────────────
            {
                const vFrac = 0.55;
                const vy = horizonY + (height - horizonY) * vFrac;
                const vH = 22 * vFrac;
                const vW = 50 * vFrac;
                const vx = rleft + (rVpL - rleft) * (1 - vFrac) + 12;

                // Shadow
                ctx.fillStyle = 'rgba(0,0,0,0.25)';
                ctx.beginPath();
                ctx.ellipse(vx + vW * 0.5, vy + 3, vW * 0.6, 6 * vFrac, 0, 0, Math.PI * 2);
                ctx.fill();

                // Body (white/silver like the car in the photo)
                const bodyGrad = ctx.createLinearGradient(vx, vy - vH, vx, vy);
                bodyGrad.addColorStop(0, '#E8EAF0');
                bodyGrad.addColorStop(0.4, '#D0D4DC');
                bodyGrad.addColorStop(1, '#A8ABB4');
                ctx.fillStyle = bodyGrad;
                ctx.beginPath();
                ctx.roundRect(vx, vy - vH, vW, vH, [4, 4, 0, 0]);
                ctx.fill();

                // Roof
                ctx.fillStyle = '#C8CACE';
                ctx.beginPath();
                ctx.roundRect(vx + vW * 0.15, vy - vH * 1.45, vW * 0.68, vH * 0.55, 4);
                ctx.fill();

                // Windshield
                ctx.fillStyle = 'rgba(140,200,220,0.55)';
                ctx.beginPath();
                ctx.roundRect(vx + vW * 0.17, vy - vH * 1.38, vW * 0.28, vH * 0.45, 2);
                ctx.fill();

                // Rear window
                ctx.fillStyle = 'rgba(140,200,220,0.45)';
                ctx.beginPath();
                ctx.roundRect(vx + vW * 0.54, vy - vH * 1.38, vW * 0.28, vH * 0.45, 2);
                ctx.fill();

                // Wheels
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

            // ── VEHICLE: Approaching tricycle/jeepney on road ──────────────
            {
                const tvFrac = 0.25;
                const tvy = horizonY + (height - horizonY) * tvFrac;
                const tvH = 10 * tvFrac;
                const tvW = 14 * tvFrac;
                const tvx = vpX + (roadAng / 55) * 30;

                ctx.fillStyle = '#8B2020'; // Red tricycle body (common in Agusan)
                ctx.beginPath();
                ctx.roundRect(tvx - tvW / 2, tvy - tvH, tvW, tvH, 2);
                ctx.fill();
                // Windshield glint
                ctx.fillStyle = 'rgba(200,235,240,0.6)';
                ctx.beginPath();
                ctx.roundRect(tvx - tvW * 0.3, tvy - tvH * 0.85, tvW * 0.6, tvH * 0.4, 1);
                ctx.fill();
                // Tiny headlights
                ctx.fillStyle = '#FFEE88';
                ctx.beginPath(); ctx.arc(tvx - tvW * 0.28, tvy - tvH * 0.1, 1.5, 0, Math.PI * 2); ctx.fill();
                ctx.beginPath(); ctx.arc(tvx + tvW * 0.28, tvy - tvH * 0.1, 1.5, 0, Math.PI * 2); ctx.fill();
            }

            // ── PERSON cycling (like in the Burgos St photo) ───────────────
            {
                const pFrac = 0.42;
                const py2 = horizonY + (height - horizonY) * pFrac;
                const pH = 14 * pFrac;
                const px2 = vpX + 18 * pFrac;

                // Bicycle wheels
                ctx.strokeStyle = '#555';
                ctx.lineWidth = 1.5 * pFrac;
                [-8, 8].forEach(ow => {
                    ctx.beginPath();
                    ctx.arc(px2 + ow * pFrac, py2, 5 * pFrac, 0, Math.PI * 2);
                    ctx.stroke();
                });
                // Frame
                ctx.beginPath();
                ctx.moveTo(px2 - 8 * pFrac, py2);
                ctx.lineTo(px2 + 4 * pFrac, py2 - 7 * pFrac);
                ctx.lineTo(px2 + 8 * pFrac, py2);
                ctx.stroke();
                // Rider body
                ctx.fillStyle = '#8BC34A'; // Green shirt (like photo)
                ctx.beginPath();
                ctx.ellipse(px2 + 2 * pFrac, py2 - 9 * pFrac, 3 * pFrac, 5 * pFrac, -0.2, 0, Math.PI * 2);
                ctx.fill();
                // Head
                ctx.fillStyle = '#C8946A';
                ctx.beginPath();
                ctx.arc(px2 + 3 * pFrac, py2 - 14 * pFrac, 3 * pFrac, 0, Math.PI * 2);
                ctx.fill();
            }

            // ── ROADSIDE: Vegetation / buildings on right ──────────────────
            // Trees (tall coconut palms on right side)
            const treeXPositions = [rright + 20 * 0.75, rright + 45 * 0.75, rright + 70 * 0.75].filter(tx => tx < width + 20);
            const treeFracs = [0.7, 0.5, 0.38];
            treeXPositions.forEach((tx, ti) => {
                const tf = treeFracs[ti] || 0.4;
                const ty2 = horizonY + (height - horizonY) * tf;
                const tH = (height - horizonY) * tf * 0.8;

                // Trunk (slight lean)
                ctx.strokeStyle = '#5C3D1A';
                ctx.lineWidth = Math.max(2, 3.5 * tf);
                ctx.beginPath();
                ctx.moveTo(tx, ty2);
                ctx.quadraticCurveTo(tx + 4 * tf, ty2 - tH * 0.5, tx + 3 * tf, ty2 - tH);
                ctx.stroke();

                // Coconut fronds
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

            // Low green hedge / shrubbery row left side
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
            // ── LOOKING TO THE SIDE (buildings/shops like Burgos St) ────────
            // Ground
            ctx.fillStyle = '#606268';
            ctx.fillRect(0, horizonY, width, height - horizonY);

            // Sidewalk / shoulder
            ctx.fillStyle = '#888B94';
            ctx.fillRect(0, horizonY + (height - horizonY) * 0.55, width, height - horizonY);

            // Building facades on left (shop fronts like Nano Ceramic Tint)
            const bldColors = ['#B0824A','#CCAA72','#A09060','#D4B080'];
            let bx = 0;
            while (bx < width * 0.55) {
                const bw = 55 + Math.sin(bx * 0.3) * 20;
                const bh = (height - horizonY) * (0.55 + Math.sin(bx) * 0.2);
                ctx.fillStyle = bldColors[Math.floor(bx / 60) % bldColors.length];
                ctx.fillRect(bx, horizonY, bw - 2, bh);
                // Storefront window
                ctx.fillStyle = 'rgba(160,220,240,0.45)';
                ctx.fillRect(bx + 6, horizonY + 8, bw - 18, (bh - 20) * 0.4);
                // Sign board
                ctx.fillStyle = '#E8D060';
                ctx.fillRect(bx + 4, horizonY + 4, bw - 12, 10);
                bx += bw + 2;
            }

            // Street label
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

    // â”€â”€ Haversine great-circle distance (meters) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    haversineMeters(lat1, lng1, lat2, lng2) {
        const R = 6371000; // Earth radius in meters
        const toRad = d => d * Math.PI / 180;
        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);
        const a = Math.sin(dLat / 2) ** 2
                + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    },

    // â”€â”€ Nearest barangay using Haversine â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    resolveNearestBarangay(lat, lng) {
        let nearest = SpectralData.barangays[0];
        let minDist = Infinity;
        SpectralData.barangays.forEach(b => {
            const dist = this.haversineMeters(lat, lng, b.lat, b.lng);
            if (dist < minDist) { minDist = dist; nearest = b; }
        });
        return { barangay: nearest, distanceMeters: Math.round(minDist) };
    },

    // â”€â”€ Nominatim reverse geocode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    async reverseGeocode(lat, lng) {
        // 1. Try Laravel proxy to Nominatim (with proper User-Agent header and no CORS)
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
            console.warn('[Spectra GIS] Internal reverse geocode failed, trying direct OSM:', e.message);
        }

        // 2. Direct OpenStreetMap Nominatim fallback
        try {
            const osmUrl = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=jsonv2&addressdetails=1`;
            const res = await fetch(osmUrl, { signal: AbortSignal.timeout(5000) });
            if (res.ok) {
                const data = await res.json();
                const addr = data.address || {};
                const bName = addr.quarter || addr.suburb || addr.village || addr.neighbourhood || 'Hubang';
                const town = addr.town || addr.city || addr.municipality || 'San Francisco';
                const province = addr.state || 'Agusan del Sur';
                const country = addr.country || 'Philippines';
                const road = addr.road ? `${addr.road}, ` : '';
                return {
                    success: true,
                    address: `${road}${bName}, ${town}, ${province}, ${country}`,
                    barangay_name: bName,
                    display: data.display_name
                };
            }
        } catch (e) {
            console.warn('[Spectra GIS] Direct Nominatim failed, using municipal calculation:', e.message);
        }

        // 3. Mathematical fallback using nearest barangay
        const { barangay } = this.resolveNearestBarangay(lat, lng);
        return {
            success: true,
            address: `Brgy. ${barangay.name}, San Francisco, Agusan del Sur, Philippines`,
            barangay_name: barangay.name,
            display: `Brgy. ${barangay.name}, San Francisco, Agusan del Sur, Philippines`
        };
    },

    init() {
        const mapElement = document.getElementById('spectral-map');
        if (!mapElement) return;

        const initialCenter = SpectralData.node.coordinates;
        const initialZoom = 15; // Start zoomed into San Francisco

        this.map = L.map('spectral-map', {
            center: initialCenter,
            zoom: initialZoom,
            minZoom: 2,
            maxZoom: 21,
            zoomControl: false,
            preferCanvas: true
        });

        // ── Google Satellite (only tile layer) ───────────────────────────
        this.baseLayers.satellite = L.tileLayer(
            'https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: 'Imagery &copy; <a href="https://maps.google.com">Google</a>',
            maxZoom: 21,
            subdomains: ['0','1','2','3'],
            tileSize: 256
        }).addTo(this.map);

        this.activeBaseMap = 'satellite';

        // ── Tile error handling ──────────────────────────────────────────
        const tileErrorEl = document.getElementById('map-tile-error');
        let tileErrors = 0;
        this.map.on('tileerror', () => {
            tileErrors++;
            if (tileErrors >= 3 && tileErrorEl) {
                tileErrorEl.classList.add('visible');
                clearTimeout(this.tileErrorTimer);
                this.tileErrorTimer = setTimeout(() => {
                    tileErrorEl.classList.remove('visible');
                    tileErrors = 0;
                }, 6000);
            }
        });
        this.map.on('tileload', () => { tileErrors = 0; });

        L.control.zoom({ position: 'topright' }).addTo(this.map);

        // Initialize Layer Groups
        this.layerGroups.incidents = L.layerGroup().addTo(this.map);
        this.layerGroups.wards     = L.layerGroup().addTo(this.map);
        this.layerGroups.resources = L.layerGroup().addTo(this.map);
        this.layerGroups.safeZones = L.layerGroup().addTo(this.map);

        this.renderAllLayers();

        // ── Map click: street view mode OR picker mode OR anomaly drop ───
        this.map.on('click', (e) => this.handleMapClick(e));
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
            if (this.activeFilters.severity !== 'ALL' && inc.severity !== this.activeFilters.severity) return;

            const sevLower = inc.severity.toLowerCase();
            const pulseHtml = (inc.severity === 'CRITICAL' || inc.severity === 'HIGH')
                ? `<div class="marker-incident-pulse"></div>` : '';

            const iconHtml = `
                <div class="gis-marker-wrapper" title="${inc.id}: ${inc.title}">
                    <div class="marker-incident ${sevLower}">
                        ${pulseHtml}
                        <span style="font-size:11px;font-weight:bold;color:#FFFFFF;">â—</span>
                    </div>
                </div>`;

            const icon = L.divIcon({
                className: '',
                html: iconHtml,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -16]
            });

            const marker = L.marker([inc.latitude, inc.longitude], { icon, zIndexOffset: 800 });

            // ── Anomaly condition (HP / progress / status) — mirrors investigator dashboard ──
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

            // Investigator-perspective status: Pending, Under Investigation, Verified, Resolved, Escalated
            const statusLabel = (inc.status || '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());

            // HP/progress only revealed once the anomaly is verified — hidden while PENDING / unverified
            const isVerified = ['VERIFIED', 'RESOLVED'].includes(inc.status);

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

            marker.bindPopup(`
                <div style="padding: 12px 14px; min-width: 270px; font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px; gap: 8px; padding-right: 24px;">
                        <span style="font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; color:#8B5CF6;">${inc.id}</span>
                        <span class="badge-${sevLower}" style="font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px; flex-shrink:0;">${inc.severity}</span>
                    </div>
                    <h4 style="font-size:12px; font-weight:800; color:#FFFFFF; line-height:1.3; margin-bottom:4px; padding-right: 12px;">${inc.title}</h4>
                    <p style="font-size:11px; color:#9CA3AF; margin-bottom:8px;">${inc.barangay}, ${inc.municipality}</p>
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

    renderSafeZones() {
        if (!this.layerGroups.safeZones) return;
        this.layerGroups.safeZones.clearLayers();
        if (!this.activeFilters.safeZones) return;

        SpectralData.safeZones.forEach(sz => {
            const safeRadius = Math.round((parseFloat(sz.radius) || 800) * 0.30);
            const circle = L.circle([sz.latitude, sz.longitude], {
                radius: safeRadius,
                color: '#22C55E', weight: 1.5,
                fillColor: '#22C55E', fillOpacity: 0.12
            });

            circle.bindTooltip(sz.name, {
                permanent: true,
                direction: 'center',
                className: 'safe-zone-label'
            });

            circle.bindPopup(`
                <div style="padding: 10px 12px; min-width: 220px; box-sizing: border-box;">
                    <div style="margin-bottom: 2px; padding-right: 24px;">
                        <span style="font-size:9px; font-weight:700; color:#22C55E; font-family:'JetBrains Mono',monospace;">SAFE WARD STATION</span>
                    </div>
                    <h4 style="font-size:12px; font-weight:800; color:#FFFFFF; margin-bottom:2px; padding-right: 12px;">${sz.name}</h4>
                    <p style="font-size:11px; color:#9CA3AF;">Capacity: ${sz.capacity} civilians &bull; Status: Operational</p>
                </div>
            `);

            circle.on('click', () => {
                if (SpectralMap.map && sz.latitude && sz.longitude) {
                    const currentZoom = SpectralMap.map.getZoom();
                    const targetZoom = Math.max(currentZoom, 17);
                    SpectralMap.map.flyTo([sz.latitude, sz.longitude], targetZoom, { duration: 0.7 });
                }
            });

            this.layerGroups.safeZones.addLayer(circle);
        });
    },

    startLocationPicking() {
        this.isPickingLocation = true;
        // Remove any anomaly marker while picking
        if (this.anomalyMarker) {
            this.map.removeLayer(this.anomalyMarker);
            this.anomalyMarker = null;
        }
        const mapEl = document.getElementById('spectral-map');
        const hudEl = document.getElementById('location-picker-hud');
        if (mapEl) mapEl.classList.add('picking-location');
        if (hudEl) hudEl.classList.add('visible');

        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.add('hidden');
    },

    cancelLocationPicking() {
        this.isPickingLocation = false;
        const mapEl = document.getElementById('spectral-map');
        const hudEl = document.getElementById('location-picker-hud');
        if (mapEl) mapEl.classList.remove('picking-location');
        if (hudEl) hudEl.classList.remove('visible');

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

    // â”€â”€ Unified map click handler â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    async handleMapClick(e) {
        if (!this.isPickingLocation) {
            // Normal map navigation: clicking the map does not drop annoying probes or popups
            return;
        }

        const { lat, lng } = e.latlng;

        // Place or update location picker pin
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

        const latInput        = document.getElementById('report-lat');
        const lngInput        = document.getElementById('report-lng');
        const barangaySelect  = document.getElementById('report-barangay');
        const resolvedInput   = document.getElementById('report-resolved-location');
        const geocodeStatus   = document.getElementById('report-geocode-status');

        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
        if (barangaySelect) barangaySelect.value = barangay.name;

        if (geocodeStatus) {
            geocodeStatus.textContent = 'Resolving via Nominatim...';
            geocodeStatus.className = 'text-[9px] font-mono text-amber-400 animate-pulse';
        }
        if (resolvedInput) {
            resolvedInput.value = `Resolving address for ${lat.toFixed(5)}, ${lng.toFixed(5)}...`;
        }

        // Deactivate picker mode and show report modal
        this.isPickingLocation = false;
        const mapEl = document.getElementById('spectral-map');
        const hudEl = document.getElementById('location-picker-hud');
        if (mapEl) mapEl.classList.remove('picking-location');
        if (hudEl) hudEl.classList.remove('visible');

        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.remove('hidden');

        // Async: Nominatim reverse geocode
        this.reverseGeocode(lat, lng).then(geo => {
            if (resolvedInput) {
                resolvedInput.value = geo.address || geo.display;
            }
            if (geocodeStatus) {
                geocodeStatus.textContent = 'Auto-detected (Nominatim)';
                geocodeStatus.className = 'text-[9px] font-mono text-emerald-400';
            }
            if (geo.barangay_name && barangaySelect) {
                for (let i = 0; i < barangaySelect.options.length; i++) {
                    const opt = barangaySelect.options[i];
                    if (opt.value === geo.barangay_name || opt.text.includes(geo.barangay_name)) {
                        barangaySelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    },

    // ── Left-Side Street View Reconnaissance & 360 Engine ────────────────

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

        // Slide in left panel
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

        // Nearest barangay
        const { barangay, distanceMeters } = this.resolveNearestBarangay(lat, lng);

        // Update UI fields
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

        // Google Maps Street View deep link
        if (deepLink) {
            deepLink.href = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${lat},${lng}`;
        }

        let guessedRoad = barangay.name === 'Hubang' ? 'Davao-Agusan National Hwy' : `San Francisco - ${barangay.name} Rd`;
        if (roadTitle) roadTitle.textContent = guessedRoad;
        if (roadMeta) roadMeta.textContent = `${barangay.name}, San Francisco · Updated 2026`;

        // Update Pegman pin on satellite map
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

        // Initialize 360 panorama road viewer (procedural fallback)
        StreetView360.init();
        StreetView360.setLocation(lat, lng, guessedRoad, barangay.name);

        // Check and load real Google Street View photos if an API key is connected
        this.loadRealGoogleStreetView(lat, lng);

        // Async: reverse-geocode exact road name via Nominatim
        this.reverseGeocode(lat, lng).then(geo => {
            if (geo.ok && (geo.village || geo.city)) {
                const actualRoad = geo.village || geo.city || guessedRoad;
                if (roadTitle) roadTitle.textContent = actualRoad;
                StreetView360.roadName = actualRoad;
            }
        });

        // Let Leaflet map adjust smoothly alongside the left panel
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
                    // Fallback to procedural 360 viewer if no Street View car passed this exact coordinate
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
            world:         { center: [13.0, 122.0],                   zoom: 3  },
            philippines:   { center: [12.8797, 121.7740],             zoom: 6  },
            mindanao:      { center: [7.8500, 124.9000],              zoom: 8  },
            agusan:        { center: [8.5000, 125.8500],              zoom: 10 },
            // Zoom 16 = tight view of just San Francisco municipality (town proper)
            san_francisco: { center: [8.5100, 125.9750],              zoom: 16 }
        };
        const target = presets[level] || presets.san_francisco;
        this.map.flyTo(target.center, target.zoom, { duration: 1.4 });
    },

    setBaseMap(theme) {
        if (!this.map) return;

        // Remove all base layers including the esri satellite fallback
        const allLayers = [...Object.values(this.baseLayers)];
        allLayers.forEach(layer => {
            if (this.map.hasLayer(layer)) this.map.removeLayer(layer);
        });

        // Add requested layer; fall back to dark if unknown
        const layer = this.baseLayers[theme] || this.baseLayers.dark;
        layer.addTo(this.map);
        this.activeBaseMap = theme;

        // Update switcher button active states
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
        this.renderAllLayers();
        this.updateLayerToggleButtons();
    },

    updateLayerToggleButtons() {
        ['incidents', 'wards', 'resources', 'safeZones'].forEach(key => {
            const btn = document.getElementById(`layer-btn-${key}`);
            if (btn) btn.classList.toggle('active', !!this.activeFilters[key]);
        });
    },

    updateStatsCounters() {
        const totalIncidents     = SpectralData.incidents.length;
        const underInvestigation = SpectralData.incidents.filter(i => i.status === 'UNDER INVESTIGATION').length;
        const totalWards         = SpectralData.wardStations.length;
        const totalResources     = SpectralData.spectralResources.length;

        const elInc  = document.getElementById('stat-active-incidents');
        const elInv  = document.getElementById('stat-investigating');
        const elWard = document.getElementById('stat-ward-stations');
        const elRes  = document.getElementById('stat-resources');

        if (elInc)  elInc.textContent  = String(totalIncidents).padStart(2, '0');
        if (elInv)  elInv.textContent  = String(underInvestigation).padStart(2, '0');
        if (elWard) elWard.textContent = String(totalWards).padStart(2, '0');
        if (elRes)  elRes.textContent  = String(totalResources).padStart(2, '0');

        const elMapInc  = document.getElementById('map-hud-incidents');
        const elMapWards = document.getElementById('map-hud-wards');
        const elMapRes  = document.getElementById('map-hud-resources');

        if (elMapInc)  elMapInc.textContent  = totalIncidents;
        if (elMapWards) elMapWards.textContent = totalWards;
        if (elMapRes)  elMapRes.textContent  = totalResources;
    }
};


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 3. UI CONTROLLER & WORKFLOW (Vanilla JS)




// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 3. UI CONTROLLER & WORKFLOW (Vanilla JS)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
const SpectralUI = {
    currentRole: 'reporter', // 'reporter' | 'investigator'
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
            opt.value = b.name;
            opt.textContent = `Brgy. ${b.name}`;
            if (b.name === 'Hubang') opt.selected = true;
            select.appendChild(opt);
        });
    },

    inspectIncident(incidentId) {
        this.selectedIncidentId = incidentId;
        // Bug #1 fix: normalized data stores incident_code as `id`, also support db_id lookup
        const inc = SpectralData.incidents.find(i =>
            i.id === incidentId || i.incident_code === incidentId || i.db_id === incidentId
        );
        if (!inc) return;

        const defaultOverview = document.getElementById('panel-default-overview');
        const inspectorView   = document.getElementById('panel-incident-inspector');

        if (defaultOverview) defaultOverview.classList.add('hidden');
        if (inspectorView)   inspectorView.classList.remove('hidden');

        // On mobile, expand infopanel so the user sees the details immediately
        if (window.innerWidth < 768) {
            this.expandMobileInfoPanel();
            const mobTitle = document.getElementById('mobile-infopanel-title');
            if (mobTitle) mobTitle.textContent = 'Incident: ' + (inc.incident_code || inc.id);
        }

        // Store current incident coords for focusOnInspected and zoom in on map
        this._inspectedLat = inc.latitude;
        this._inspectedLng = inc.longitude;

        if (SpectralMap.map && inc.latitude && inc.longitude) {
            const currentZoom = SpectralMap.map.getZoom();
            const targetZoom = Math.max(currentZoom, 17);
            SpectralMap.map.flyTo([inc.latitude, inc.longitude], targetZoom, { duration: 0.7 });
        }

        document.getElementById('insp-id').textContent       = inc.incident_code || inc.id;
        document.getElementById('insp-type').textContent     = inc.type;
        document.getElementById('insp-title').textContent    = inc.title;
        document.getElementById('insp-location').textContent = `${inc.barangay}, ${inc.municipality}, ${inc.province}`;
        document.getElementById('insp-coords').textContent   = `${parseFloat(inc.latitude).toFixed(6)}° N, ${parseFloat(inc.longitude).toFixed(6)}° E`;
        document.getElementById('insp-date').textContent     = inc.reported_at;
        document.getElementById('insp-reporter').textContent = inc.reported_by;

        const sevBadge  = document.getElementById('insp-severity-badge');
        const statBadge = document.getElementById('insp-status-badge');

        if (sevBadge) {
            sevBadge.className   = `inline-block badge-${inc.severity.toLowerCase()} text-[10px] font-bold px-2.5 py-1 rounded-md font-mono`;
            sevBadge.textContent = `${inc.severity} SEVERITY`;
        }

        if (statBadge) {
            statBadge.className   = `badge-${this.getStatusBadgeClass(inc.status)} text-[10px] font-bold px-2 py-0.5 rounded-full font-mono`;
            statBadge.textContent = inc.status;
        }



        // Status Timeline
        this._buildTimeline(inc);

        // Investigator actions
        const investigatorSection = document.getElementById('insp-investigator-actions');
        if (investigatorSection) {
            if (this.currentRole === 'investigator') {
                investigatorSection.classList.remove('hidden');
                const statSel  = document.getElementById('investigator-status-select');
                const sevSel   = document.getElementById('investigator-severity-select');
                const notesInp = document.getElementById('investigator-notes-input');
                if (statSel)  statSel.value  = inc.status;
                if (sevSel)   sevSel.value   = inc.severity;
                if (notesInp) notesInp.value = inc.notes || '';
            } else {
                investigatorSection.classList.add('hidden');
            }
        }
    },

    _buildTimeline(inc) {
        const container = document.getElementById('insp-timeline');
        if (!container) return;

        const isReported = true;
        const isInvestigating = ['UNDER INVESTIGATION', 'VERIFIED', 'RESOLVED'].includes(inc.status) || (inc.investigations && inc.investigations.length > 0);
        const isConfirmed = inc.investigation_result === 'CONFIRMED' || ['VERIFIED', 'RESOLVED'].includes(inc.status) || (inc.responder_assignments && inc.responder_assignments.length > 0);
        const latestAssignment = (inc.responder_assignments && inc.responder_assignments.length > 0) ? inc.responder_assignments[inc.responder_assignments.length - 1] : null;
        const isResponderAssigned = !!(latestAssignment && latestAssignment.responder_id);
        const isUnderResponse = (latestAssignment && ['ACTIVE', 'COMPLETED', 'CRITICAL', 'SUPPORT_REQUIRED'].includes(latestAssignment.status)) || inc.status === 'RESOLVED';

        container.innerHTML = `
            <div class="space-y-2 text-xs pt-1 font-mono">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full ${isReported ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600'}"></span>
                    <span class="${isReported ? 'text-white font-bold' : 'text-slate-500'}">Reported</span>
                    <span class="text-[10px] text-slate-500 ml-auto">${inc.reported_at || ''}</span>
                </div>
                <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full ${isInvestigating ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600'}"></span>
                    <span class="${isInvestigating ? 'text-white font-bold' : 'text-slate-500'}">Under Investigation</span>
                </div>
                <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full ${isConfirmed ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600'}"></span>
                    <span class="${isConfirmed ? 'text-white font-bold' : 'text-slate-500'}">Confirmed</span>
                </div>
                <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full ${isResponderAssigned ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600'}"></span>
                    <span class="${isResponderAssigned ? 'text-white font-bold' : 'text-slate-500'}">Responder Assigned</span>
                </div>
                <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full ${isUnderResponse ? 'bg-[#8B5CF6] ring-2 ring-[#8B5CF6]/30 animate-pulse' : 'bg-slate-600'}"></span>
                    <span class="${isUnderResponse ? (inc.status === 'RESOLVED' ? 'text-emerald-400 font-bold' : 'text-[#A78BFA] font-bold') : 'text-slate-500'}">
                        ${inc.status === 'RESOLVED' ? 'Neutralized / Resolved' : 'Under Response'}
                    </span>
                </div>
            </div>
        `;
    },

    closeInspector() {
        this.selectedIncidentId = null;
        const defaultOverview = document.getElementById('panel-default-overview');
        const inspectorView   = document.getElementById('panel-incident-inspector');

        if (defaultOverview) defaultOverview.classList.remove('hidden');
        if (inspectorView)   inspectorView.classList.add('hidden');

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
        // Bug #2 fix: mirror the same triple-key lookup
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

        // Persist to Laravel API
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
        this.inspectIncident(this.selectedIncidentId); // Bug #3 fix: use stored selectedIncidentId
        this.showToast(`Incident ${inc.id} updated to ${newStatus}`);
    },

    openReportModal() {
        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.remove('hidden');
    },

    closeReportModal() {
        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.add('hidden');
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

        const type        = document.getElementById('report-type').value;
        const title       = document.getElementById('report-title').value;
        const barangayName = document.getElementById('report-barangay').value || 'Hubang';
        const lat         = parseFloat(document.getElementById('report-lat').value) || 8.5310;
        const lng         = parseFloat(document.getElementById('report-lng').value) || 125.9730;
        const severity    = document.getElementById('report-severity').value;
        const description = document.getElementById('report-desc').value;
        // Bug #4 fix: removed dead report-resolved-location reference (element was deleted)
        const nowIso      = new Date().toISOString();
        // Bug #8 fix: use real logged-in user name injected by server
        const authUserName = window.INITIAL_SPECTRAL_STATE?.auth_user || 'Citizen Field Reporter';
        const fileInput   = document.getElementById('report-evidence-file');
        const previewImg  = document.getElementById('report-evidence-preview');

        // Generate incident code based on existing count
        const nextNum = SpectralData.incidents.length + 1;
        const newCode = `SF-INC-${String(nextNum).padStart(3, '0')}`;

        // Format date for display
        let formattedDate = nowIso;
        try {
            const d = new Date(nowIso);
            formattedDate = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                + ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        } catch (_) {}

        const newIncident = {
            id: newCode,
            db_id: null,
            type: type,
            incident_code: newCode,
            title:         title,
            description:   description,
            barangay:      barangayName,
            municipality:  'San Francisco',
            province:      'Agusan del Sur',
            latitude:      lat,
            longitude:     lng,
            severity:      severity,
            status:        'PENDING',
            reported_at:   formattedDate,
            reported_by:   authUserName, // Bug #8 fix — real user name
            evidence:      previewImg && previewImg.src && !previewImg.src.includes('data:,') ? previewImg.src : null,
            investigations: [],
            notes:         null
        };

        // Post to Laravel API
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const formData = new FormData();
            formData.append('incident_type', type);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('latitude', lat);
            formData.append('longitude', lng);
            formData.append('incident_date', nowIso);
            formData.append('severity', severity);
            // Send barangay_id from the selected option's data-id
            const barangaySelect = document.getElementById('report-barangay');
            const barangayId = barangaySelect?.options[barangaySelect.selectedIndex]?.dataset?.id;
            if (barangayId) formData.append('barangay_id', barangayId);
            if (fileInput && fileInput.files[0]) {
                formData.append('evidence', fileInput.files[0]);
            }

            const res = await fetch('/incidents', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
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
                console.error('Failed to submit incident:', errorData);
                this.showToast('Submission error. Please ensure all required fields are filled.');
                return;
            }
        } catch (err) {
            console.error('API post error:', err);
            this.showToast('Network error while reporting incident.');
            return;
        }

        document.getElementById('report-form').reset();
        this.removeEvidencePreview();
        this.closeReportModal();

        // If on the My Reports page, reload so the new report is shown immediately
        if (window.location.pathname.includes('my-reports')) {
            window.location.reload();
            return;
        }

        SpectralData.incidents.unshift(newIncident);

        SpectralMap.renderIncidents();
        SpectralMap.updateStatsCounters();

        if (SpectralMap.map) {
            SpectralMap.map.flyTo([lat, lng], 15, { duration: 1.0 });
        }
        this.inspectIncident(newIncident.id);
        this.showToast(`Incident ${newIncident.id} recorded in San Francisco Node.`);
    },

    showToast(message) {
        let toast = document.getElementById('spectral-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'spectral-toast';
            toast.className = 'fixed bottom-5 right-5 z-[9999] px-4 py-2.5 rounded-xl bg-[#1B222C] border border-[#8B5CF6] text-white text-xs font-bold shadow-2xl flex items-center gap-2 transition-all opacity-0 translate-y-2';
            document.body.appendChild(toast);
        }

        toast.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg><span>${message}</span>`;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
        }, 3500);
    },

    // Pre-fills report form with coordinates and reverse geocodes then opens the modal
    openReportModalAtPoint(lat, lng, barangayName) {
        SpectralMap.clearAnomalyMarker();

        const latInput       = document.getElementById('report-lat');
        const lngInput       = document.getElementById('report-lng');
        const barangaySelect = document.getElementById('report-barangay');
        const resolvedInput  = document.getElementById('report-resolved-location');
        const geocodeStatus  = document.getElementById('report-geocode-status');

        if (latInput)       latInput.value = parseFloat(lat).toFixed(6);
        if (lngInput)       lngInput.value = parseFloat(lng).toFixed(6);
        if (barangaySelect) barangaySelect.value = barangayName;

        if (geocodeStatus) {
            geocodeStatus.textContent = 'Resolving via Nominatim...';
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
                geocodeStatus.textContent = 'Auto-detected (Nominatim)';
                geocodeStatus.className = 'text-[9px] font-mono text-emerald-400';
            }
            if (geo.barangay_name && barangaySelect) {
                for (let i = 0; i < barangaySelect.options.length; i++) {
                    const opt = barangaySelect.options[i];
                    if (opt.value === geo.barangay_name || opt.text.includes(geo.barangay_name)) {
                        barangaySelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    },

    bindEvents() {
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (SpectralMap.isPickingLocation)  SpectralMap.cancelLocationPicking();
                if (SpectralMap.isStreetViewMode)   SpectralMap.closeStreetView();
                SpectralMap.clearAnomalyMarker();
            }
        });
    }
};

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 4. INITIALIZE ON DOM READY
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
window.SpectralMap = SpectralMap;
window.SpectralUI  = SpectralUI;

document.addEventListener('DOMContentLoaded', () => {
    SpectralUI.init();
    SpectralMap.init();
});
