<?php
if (!isset($_GET['access']) || $_GET['access'] !== 'P9jOYB6JlMcuvj5tiYD0aqz4b6kDjBiN') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Permessi mancanti';
    exit;
}
?><!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Test - bertoldi_gestionale</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1d2433;
        }
        .wrapper {
            max-width: 1200px;
            margin: 24px auto;
            padding: 0 16px 24px;
        }
        .top {
            background: #fff;
            border: 1px solid #d8deeb;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .top h1 {
            margin: 0 0 10px;
            font-size: 24px;
        }
        .top p {
            margin: 0 0 14px;
            color: #3f4b63;
        }
        .token-row {
            display: grid;
            grid-template-columns: 120px 1fr 160px;
            gap: 10px;
            align-items: center;
        }
        .token-row input {
            padding: 10px;
            border: 1px solid #c9d2e5;
            border-radius: 8px;
            font-size: 14px;
        }
        .token-row button {
            padding: 10px 12px;
            border: 0;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 14px;
        }
        .card {
            background: #fff;
            border: 1px solid #d8deeb;
            border-radius: 10px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .card h3 {
            margin: 0;
            font-size: 17px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #fff;
            background: #334155;
            margin-right: 8px;
        }
        .row {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        label {
            font-size: 12px;
            color: #4b5568;
            font-weight: 600;
        }
        input, textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 9px 10px;
            border: 1px solid #c9d2e5;
            border-radius: 8px;
            font-family: Consolas, monospace;
            font-size: 12px;
            background: #fff;
            color: #111827;
        }
        textarea {
            min-height: 108px;
            resize: vertical;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .run {
            border: 0;
            border-radius: 8px;
            padding: 9px 12px;
            background: #10b981;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .copy {
            border: 1px solid #b6c0d4;
            border-radius: 8px;
            padding: 9px 12px;
            background: #fff;
            color: #1f2937;
            font-weight: 700;
            cursor: pointer;
        }
        pre {
            margin: 0;
            background: #0f172a;
            color: #d2e2ff;
            border-radius: 8px;
            padding: 12px;
            min-height: 120px;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 12px;
            line-height: 1.35;
        }
        .hint {
            font-size: 12px;
            color: #4b5568;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="top">
        <h1>API Test Runner</h1>
        <p>Inserisci il token e usa gli esempi precompilati. Ogni blocco esegue la chiamata a <strong>/api_json/api.php</strong> e mostra la risposta.</p>
        <div class="token-row">
            <label for="token">Token API</label>
            <input id="token" type="text" placeholder="Incolla qui il token">
            <button id="saveToken">Salva Token</button>
        </div>
    </div>
    <div id="examples" class="grid"></div>
</div>

<script>
const apiBasePath = './api.php';
const tokenInput = document.getElementById('token');
const saveTokenBtn = document.getElementById('saveToken');
const examplesContainer = document.getElementById('examples');
let lastResponseJson = null;

const examples = [
    {
        title: 'Status',
        method: 'GET',
        handler: 'status',
        query: '',
        body: ''
    },
    /*
    {
        title: 'Routes',
        method: 'GET',
        handler: 'routes',
        query: '',
        body: ''
    },
    */
    {
        title: 'Experience Dates',
        method: 'POST',
        handler: 'experienceDates',
        query: '',
        body: JSON.stringify({
            lineaId: 'REPLACE_WITH_LINEA_ID',
            dataInizio: '2026-04-01',
            dataFine: '2026-04-30'
        }, null, 2)
    },
    // {
    //     title: 'Search',
    //     method: 'POST',
    //     handler: 'search',
    //     query: '',
    //     body: JSON.stringify({
    //         departureStationCode: 'C-1',
    //         arrivalStationCode: 'C-2',
    //         departureDate: '2026-04-01',
    //         passengers: {
    //             adult: 1,
    //             child: 0,
    //             infant: 0
    //         }
    //     }, null, 2)
    // },
    {
        title: 'Reservation - Create',
        method: 'POST',
        handler: 'reservation',
        query: '',
        body: JSON.stringify({
            departureDate: '2026-04-01',
            departureRide: 'REPLACE_WITH_CORSA_ID',
            departureStationCode: 'C-1 (es. 8322)',
            arrivalStationCode: 'C-2 (es. 2349)',
            passengers: [
                {
                    passengerType: 17,
                },
                {
                    passengerType: 1,
                },
                {
                    passengerType: 2,
                },
                {
                    passengerType: 17,
                },
            ],
            phoneNumber: '+391234567890',
            email: 'mario.rossi@example.com',
            customerName: 'Mario Rossi'
        }, null, 2)
    },
    {
        title: 'Reservation - Delete',
        method: 'DELETE',
        handler: 'reservation/REPLACE_WITH_RESERVATION_ID',
        query: '',
        body: ''
    },
    {
        title: 'Booking - Confirm',
        method: 'POST',
        handler: 'booking',
        query: '',
        body: JSON.stringify({
            reservationId: 'REPLACE_WITH_RESERVATION_ID'
        }, null, 2)
    },
    {
        title: 'Booking - Get',
        method: 'GET',
        handler: 'booking/REPLACE_WITH_BOOKING_ID',
        query: '',
        body: ''
    },
    {
        title: 'Booking - Delete',
        method: 'DELETE',
        handler: 'booking/REPLACE_WITH_BOOKING_ID',
        query: 'force=true',
        body: ''
    },
    {
        title: 'Get Bookings',
        method: 'GET',
        handler: 'getBookings',
        query: 'offset=0&limit=20',
        body: ''
    },
    {
        title: 'Experiences',
        method: 'GET',
        handler: 'experiences',
        query: '',
        body: ''
    },
    {
        title: 'Experiences (tipoTour)',
        method: 'GET',
        handler: 'experiences',
        query: 'tipoTour=0',
        body: ''
    },
    {
        title: 'Experience - Get',
        method: 'GET',
        handler: 'experience/REPLACE_WITH_LINEA_ID',
        query: '',
        body: ''
    },
    {
        title: 'Experience Prices',
        method: 'GET',
        handler: 'experiencePrices',
        query: 'lineaId=REPLACE_WITH_LINEA_ID&dataPartenza=2026-04-01',
        body: ''
    },
    {
        title: 'Experience Ports',
        method: 'POST',
        handler: 'experiencePorts',
        query: '',
        body: JSON.stringify({
            lineaId: 'REPLACE_WITH_LINEA_ID'
        }, null, 2)
    },
    {
        title: 'Available Rides',
        method: 'POST',
        handler: 'availableRides',
        query: '',
        body: JSON.stringify({
            comunePartenzaId: 1,
            comuneDestinazioneId: 2,
            dataPartenza: '2026-04-01',
            lineaId: 'REPLACE_WITH_LINEA_ID',
            biglietti: {
                17: 1
            }
        }, null, 2)
    }
];

function createCard(example, index) {
    const card = document.createElement('div');
    card.className = 'card';

    const methodColor = example.method === 'GET' ? '#2563eb' : (example.method === 'POST' ? '#059669' : '#c2410c');

    card.innerHTML = `
        <h3><span class="badge" style="background:${methodColor}">${example.method}</span>${example.title}</h3>
        <div class="row">
            <label>Handler</label>
            <input id="handler-${index}" type="text" value="${escapeHtml(example.handler)}">
        </div>
        <div class="row">
            <label>Query string</label>
            <input id="query-${index}" type="text" value="${escapeHtml(example.query)}" placeholder="es. offset=0&limit=20">
        </div>
        <div class="row">
            <label>Body JSON</label>
            <textarea id="body-${index}" ${example.method === 'GET' || example.method === 'DELETE' ? 'disabled' : ''}>${escapeHtml(example.body)}</textarea>
        </div>
        <div class="actions">
            <button class="run" id="run-${index}">Esegui</button>
            <button class="copy" id="fill-${index}">Compila da risposta</button>
            <button class="copy" id="copy-${index}">Copia URL</button>
        </div>
        <div class="hint" id="meta-${index}"></div>
        <pre id="result-${index}">Nessuna chiamata eseguita.</pre>
    `;

    const runBtn = card.querySelector(`#run-${index}`);
    const fillBtn = card.querySelector(`#fill-${index}`);
    const copyBtn = card.querySelector(`#copy-${index}`);
    const meta = card.querySelector(`#meta-${index}`);
    const result = card.querySelector(`#result-${index}`);

    runBtn.addEventListener('click', async () => {
        const token = tokenInput.value.trim();
        if (!token) {
            result.textContent = 'Errore: inserisci il token API.';
            return;
        }

        const handler = card.querySelector(`#handler-${index}`).value.trim();
        const query = card.querySelector(`#query-${index}`).value.trim();
        const bodyRaw = card.querySelector(`#body-${index}`).value;

        const url = buildUrl(token, handler, query);
        meta.textContent = `${example.method} ${url}`;

        const options = {
            method: example.method,
            headers: {}
        };

        if (example.method === 'POST') {
            if (example.contentType === 'form') {
                const formBody = bodyRaw.trim();
                if (!formBody) {
                    result.textContent = 'Body form non valido: inserisci parametri key=value separati da &.';
                    return;
                }
                options.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
                options.body = formBody;
            } else {
                let parsed;
                try {
                    parsed = JSON.parse(bodyRaw);
                } catch (e) {
                    result.textContent = 'Body JSON non valido: ' + e.message;
                    return;
                }
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(parsed);
            }
        }

        result.textContent = 'Esecuzione in corso...';
        try {
            const response = await fetch(url, options);
            const text = await response.text();
            let formatted = text;
            try {
                lastResponseJson = JSON.parse(text);
                formatted = JSON.stringify(lastResponseJson, null, 2);
            } catch (e) {}
            result.textContent = `HTTP ${response.status}\n\n${formatted}`;
        } catch (err) {
            result.textContent = 'Errore di rete: ' + err.message;
        }
    });

    fillBtn.addEventListener('click', () => {
        if (!lastResponseJson) {
            meta.textContent = 'Nessuna risposta precedente disponibile.';
            return;
        }

        const values = collectAutoValues(lastResponseJson);
        const replacements = {
            REPLACE_WITH_RESERVATION_ID: values.reservationId,
            REPLACE_WITH_BOOKING_ID: values.bookingId,
            REPLACE_WITH_SOLUTION_ID: values.solutionId,
            REPLACE_WITH_LINEA_ID: values.lineaId
        };

        const handlerInput = card.querySelector(`#handler-${index}`);
        const queryInput = card.querySelector(`#query-${index}`);
        const bodyInput = card.querySelector(`#body-${index}`);

        const originalHandler = handlerInput.value;
        const originalQuery = queryInput.value;
        const originalBody = bodyInput.value;

        const updatedHandler = applyPlaceholders(originalHandler, replacements);
        const updatedQuery = applyPlaceholders(originalQuery, replacements);
        const updatedBody = applyPlaceholders(originalBody, replacements);

        handlerInput.value = updatedHandler;
        queryInput.value = updatedQuery;
        bodyInput.value = updatedBody;

        const changed = (originalHandler !== updatedHandler) || (originalQuery !== updatedQuery) || (originalBody !== updatedBody);
        if (changed) {
            meta.textContent = 'Campi compilati dalla risposta precedente.';
        } else {
            meta.textContent = 'Nessun placeholder compatibile trovato.';
        }
    });

    copyBtn.addEventListener('click', async () => {
        const token = tokenInput.value.trim();
        const handler = card.querySelector(`#handler-${index}`).value.trim();
        const query = card.querySelector(`#query-${index}`).value.trim();
        const url = buildUrl(token || 'INSERISCI_TOKEN', handler, query);
        try {
            await navigator.clipboard.writeText(url);
            meta.textContent = 'URL copiato: ' + url;
        } catch (e) {
            meta.textContent = url;
        }
    });

    return card;
}

function buildUrl(token, handler, query) {
    const qs = new URLSearchParams();
    qs.set('token', token);
    qs.set('handler', handler);
    if (query) {
        const extra = new URLSearchParams(query);
        for (const [k, v] of extra.entries()) {
            qs.set(k, v);
        }
    }
    return `${apiBasePath}?${qs.toString()}`;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function applyPlaceholders(text, replacements) {
    let output = String(text);
    Object.keys(replacements).forEach((placeholder) => {
        const value = replacements[placeholder];
        if (value !== undefined && value !== null && String(value).trim() !== '') {
            output = output.split(placeholder).join(String(value));
        }
    });
    return output;
}

function collectAutoValues(source) {
    return {
        reservationId: findByKeys(source, ['reservationId', 'prenotazioneId', 'reservation_id']),
        bookingId: findByKeys(source, ['bookingId', 'booking_id', 'odcId', 'ordineId']),
        solutionId: findByKeys(source, ['outboundSolutionId', 'solutionId', 'solution_id']),
        lineaId: findByKeys(source, ['lineaId', 'linea_id'])
    };
}

function findByKeys(source, keys) {
    const wanted = new Set(keys.map(k => String(k).toLowerCase()));
    return findDeep(source, wanted);
}

function findDeep(node, wantedKeys) {
    if (node === null || node === undefined) {
        return null;
    }
    if (Array.isArray(node)) {
        for (const item of node) {
            const found = findDeep(item, wantedKeys);
            if (found !== null && found !== undefined && String(found).trim() !== '') {
                return found;
            }
        }
        return null;
    }
    if (typeof node === 'object') {
        for (const key of Object.keys(node)) {
            if (wantedKeys.has(String(key).toLowerCase())) {
                const value = node[key];
                if (value !== null && value !== undefined && (typeof value !== 'object' || JSON.stringify(value) !== '{}')) {
                    if (typeof value === 'string' || typeof value === 'number') {
                        return value;
                    }
                }
            }
        }
        for (const key of Object.keys(node)) {
            const found = findDeep(node[key], wantedKeys);
            if (found !== null && found !== undefined && String(found).trim() !== '') {
                return found;
            }
        }
    }
    return null;
}

function render() {
    examplesContainer.innerHTML = '';
    examples.forEach((example, index) => {
        examplesContainer.appendChild(createCard(example, index));
    });
}

saveTokenBtn.addEventListener('click', () => {
    localStorage.setItem('api_test_token', tokenInput.value.trim());
});

const storedToken = localStorage.getItem('api_test_token');
if (storedToken) {
    tokenInput.value = storedToken;
}

render();
</script>
</body>
</html>
