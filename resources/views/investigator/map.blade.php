@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const incidentsData = @json($incidents);
    const wardsData = @json($wardStations);
    // 🌐 Dynamic Safe Zones from the Controller
    const safeWardStationsData = @json($safeWardStations ?? []);
    const resourcesData = @json($resources);

    const map = L.map('full-investigator-map', {
        center: [8.5100, 125.9750],
        zoom: 14,
        zoomControl: false,
        attributionControl: false
    });
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: 'Imagery &copy; Google',
        maxZoom: 21,
        subdomains: ['0','1','2','3'],
        tileSize: 256
    }).addTo(map);

    const layers = {
        incidents: L.layerGroup().addTo(map),
        wards: L.layerGroup().addTo(map),
        safeZones: L.layerGroup().addTo(map),
        resources: L.layerGroup().addTo(map)
    };

    // ── Helper functions for Safe Zone Popup ───────────────────────────────
    function formatProtectionTime(isoString) {
        if (!isoString) return { text: 'No protection set', expired: true };
        const expires = new Date(isoString);
        const now = new Date();
        const diffMs = expires - now;
        if (diffMs <= 0) return { text: 'Protection Expired', expired: true };

        const totalSeconds = Math.floor(diffMs / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (days > 0) return { text: `${days}d ${String(hours).padStart(2,'0')}h ${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
        if (hours > 0) return { text: `${String(hours).padStart(2,'0')}h ${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
        return { text: `${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
    }

    function suppliesBadge(status) {
        const map = {
            'available':    { color: '#22C55E', bg: 'rgba(34,197,94,0.15)',  border: 'rgba(34,197,94,0.4)',  label: 'Available' },
            'limited':      { color: '#EAB308', bg: 'rgba(234,179,8,0.15)',  border: 'rgba(234,179,8,0.4)',  label: 'Limited' },
            'out_of_stock': { color: '#EF4444', bg: 'rgba(239,68,68,0.15)',  border: 'rgba(239,68,68,0.4)',  label: 'Out of stock' }
        };
        const s = map[status] || map['available'];
        return `<span style="display:inline-flex;align-items:center;justify-content:center;background:${s.bg};color:${s.color};border:1px solid ${s.border};font-size:10px;font-weight:700;padding:3px 10px;border-radius:5px;min-width:84px;height:22px;box-sizing:border-box;">${s.label}</span>`;
    }

    function icon(name, color, size = 12) {
        const icons = {
            clock:   `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
            box:     `<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>`,
            coffee:  `<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>`,
            droplet: `<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>`,
            firstaid:`<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="14"/><line x1="9" y1="11" x2="15" y2="11"/>`,
            heart:   `<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>`
        };
        return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">${icons[name] || ''}</svg>`;
    }

    // 1. Incidents
    incidentsData.forEach(inc => {
        const sevLower = (inc.severity || 'low').toLowerCase();
        const iconHtml = `
            <div class="gis-marker-wrapper" title="${inc.incident_code}: ${inc.title}">
                <div class="marker-incident ${sevLower}">
                    <span style="font-size:10px;font-weight:bold;color:#FFFFFF;">●</span>
                </div>
            </div>`;

        const icon = L.divIcon({
            className: '',
            html: iconHtml,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -16]
        });

        const m = L.marker([inc.latitude, inc.longitude], { icon });
        const isVerified = inc.status === 'VERIFIED' || inc.status === 'RESOLVED' || inc.response_status === 'ACTIVE' || (inc.investigation_result === 'CONFIRMED');

        const defaultHpMap = { CRITICAL: 150, HIGH: 110, MEDIUM: 80, LOW: 50 };
        const defaultHp = defaultHpMap[inc.severity] || 80;
        const assignment = inc.responder_assignments && inc.responder_assignments.length > 0
            ? inc.responder_assignments[0]
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

        let conditionHtml = '';
        if (isVerified) {
            conditionHtml = `
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
                    <div style="width:100%; height:5px; background:#11161D; border-radius:3px; overflow:hidden; border:1px solid #2A3440;">
                        <div style="width:${progress}%; height:100%; background:#10B981; transition:width 0.5s;"></div>
                    </div>
                </div>`;
        }

        const repDate = inc.created_at ? new Date(inc.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Unknown date';
        const reviewUrl = `/investigator/incidents/${inc.id}/review`;

        m.bindPopup(`
            <div style="padding: 12px 14px; min-width: 250px; font-family:'Plus Jakarta Sans',sans-serif;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                    <span style="font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; color:#8B5CF6;">${inc.incident_code}</span>
                    <span class="badge-${sevLower}" style="font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px;">${inc.severity}</span>
                </div>
                <h4 style="font-size:12px; font-weight:700; color:#FFFFFF; line-height:1.3; margin-bottom:4px;">${inc.title}</h4>
                <p style="font-size:10px; color:#9CA3AF; margin-bottom:4px;">Brgy. ${inc.barangay ? inc.barangay.name : 'San Francisco'} &bull; ${repDate}</p>

                ${conditionHtml}

                <div style="display:flex; justify-content:space-between; align-items:center; padding-top:6px; border-top:1px solid #2A3440; margin-top:6px;">
                    <span style="font-size:10px; color:#64748B;">Status: <strong style="color:${isVerified ? '#10B981' : '#D1D5DB'}; font-family:'JetBrains Mono',monospace;">${inc.status}</strong></span>
                    <a href="${reviewUrl}" style="background:#8B5CF6; color:#FFFFFF; padding:5px 10px; border-radius:6px; font-size:10px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                        <span>${isVerified ? 'ASSIGN' : 'REVIEW'}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        `);

        layers.incidents.addLayer(m);
    });

    // 2. Safe Ward Stations (🌐 Dynamic from Database + Green Radius + Detailed Popup)
    safeWardStationsData.forEach(sz => {
        // Green Safe Zone Perimeter Circle
        const safeZoneCircle = L.circle([sz.lat, sz.lng], {
            radius: Math.round(sz.radius),
            color: '#22C55E',
            weight: 1.5,
            fillColor: '#22C55E',
            fillOpacity: 0.08
        });

        const shieldIcon = L.divIcon({
            className: '',
            html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="30" height="30" fill="#22C55E" style="filter:drop-shadow(0 0 6px #22C55E99)"><path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2z"/></svg>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15],
        });

        const m = L.marker([sz.lat, sz.lng], { icon: shieldIcon });

        // Detailed Popup HTML
        const time = formatProtectionTime(sz.protection_expires_at);
        const timeColor = time.expired ? '#EF4444' : '#22C55E';
        const statusLabel = time.expired ? 'WARD OFFLINE' : 'WARD ACTIVE';
        const statusDot = `<span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:${timeColor};box-shadow:0 0 6px ${timeColor};margin-right:5px;vertical-align:middle;"></span>`;
        const services = sz.assistance_services || [];

        const servicesHtml = services.length > 0
            ? services.map(s => `<li style="display:flex;align-items:center;gap:7px;font-size:11px;color:#CBD5E1;margin-bottom:5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>${s}</span>
                </li>`).join('')
            : '<li style="font-size:11px;color:#64748B;font-style:italic;">No assistance listed</li>';

        const popupHtml = `
            <div style="padding:14px 16px;min-width:290px;max-width:340px;font-family:'Plus Jakarta Sans',sans-serif;color:#F3F4F6;">
                <div style="margin-bottom:12px;">
                    <span style="display:inline-flex;align-items:center;color:${timeColor};font-size:9px;font-weight:800;font-family:'JetBrains Mono',monospace;letter-spacing:0.08em;">
                        ${statusDot}${statusLabel}
                    </span>
                    <h4 style="font-size:14px;font-weight:800;color:#FFFFFF;margin:5px 0 3px 0;line-height:1.25;">${sz.name}</h4>
                    <p style="font-size:10.5px;color:#9CA3AF;margin:0;">${sz.description || sz.address}</p>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:5px;">
                        ${icon('clock', '#A78BFA', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Protection Remaining</span>
                    </div>
                    <p style="font-size:16px;font-weight:800;color:${timeColor};font-family:'JetBrains Mono',monospace;margin:0;line-height:1;">${time.text}</p>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
                        ${icon('box', '#2DD4BF', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Relief Supplies</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('coffee', '#9CA3AF', 12)}<span>Food &amp; Water</span></span>
                        ${suppliesBadge(sz.relief_food)}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('droplet', '#9CA3AF', 12)}<span>Drinking Water</span></span>
                        ${suppliesBadge(sz.relief_water)}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('firstaid', '#9CA3AF', 12)}<span>First-aid Kits</span></span>
                        ${suppliesBadge(sz.relief_first_aid)}
                    </div>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
                        ${icon('heart', '#22C55E', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Free Assistance</span>
                    </div>
                    <ul style="list-style:none;padding:0;margin:0;">${servicesHtml}</ul>
                </div>
            </div>`;

        m.bindPopup(popupHtml, { className: 'gis-leaflet-popup', maxWidth: 360 });
        m.bindTooltip(sz.name, { direction: 'top', offset: [0, -14] });

        safeZoneCircle.on('click', () => {
            m.openPopup();
            map.flyTo([sz.lat, sz.lng], Math.max(map.getZoom(), 16), { duration: 0.7 });
        });
        m.on('click', () => {
            map.flyTo([sz.lat, sz.lng], Math.max(map.getZoom(), 16), { duration: 0.7 });
        });

        layers.safeZones.addLayer(safeZoneCircle);
        layers.safeZones.addLayer(m);
    });

    window.toggleMapLayer = function(layerKey) {
        const btnMap = {
            incidents: document.getElementById('toggle-incidents-btn'),
            safeZones: document.getElementById('toggle-safe-btn')
        };

        const targetLayer = layers[layerKey];
        const btn = btnMap[layerKey];

        if (map.hasLayer(targetLayer)) {
            map.removeLayer(targetLayer);
            if (btn) btn.style.opacity = '0.4';
        } else {
            map.addLayer(targetLayer);
            if (btn) btn.style.opacity = '1';
        }
    };
});
</script>
@endpush
