<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'API Docs' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0c0d12;
            --accent-color: #6366f1;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --get-color: #10b981;
            --post-color: #3b82f6;
            --put-color: #f59e0b;
            --delete-color: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Particles/Glow background */
        body::before {
            content: '';
            position: fixed;
            top: -10%; left: -10%;
            width: 40%; height: 40%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            z-index: -1;
            filter: blur(80px);
        }

        header {
            padding: 2rem 5%;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0; z-index: 100;
        }

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }

        .app-info h1 { font-size: 2.2rem; font-weight: 600; letter-spacing: -1px; }
        .version { font-size: 0.9rem; color: var(--accent-color); font-weight: 600; }

        .endpoint-card {
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .endpoint-card:hover { transform: translateY(-3px); }

        .endpoint-header {
            padding: 1rem 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .method {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .method.GET { background: rgba(16, 185, 129, 0.1); color: var(--get-color); border: 1px solid rgba(16, 185, 129, 0.2); }
        .method.POST { background: rgba(59, 130, 246, 0.1); color: var(--post-color); border: 1px solid rgba(59, 130, 246, 0.2); }

        .path { font-family: 'Fira Code', monospace; font-weight: 500; font-size: 0.95rem; flex-grow: 1; }
        .summary { color: var(--text-muted); font-size: 0.9rem; }

        .endpoint-body {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            display: none;
            background: rgba(0,0,0,0.2);
        }

        .table-container { margin: 1.5rem 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--text-muted); font-weight: 400; }

        .try-it-session {
            margin-top: 2rem;
            background: #1a1b23;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px dashed var(--accent-color);
        }

        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px; }
        input {
            width: 100%;
            background: #252631;
            border: 1px solid var(--border-color);
            padding: 10px;
            color: white;
            border-radius: 6px;
            outline: none;
        }
        input:focus { border-color: var(--accent-color); }

        .btn-execute {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            transition: 0.3s;
        }
        .btn-execute:hover { opacity: 0.9; box-shadow: 0 0 20px rgba(99, 102, 241, 0.4); }

        .response-area {
            margin-top: 1.5rem;
            background: #000;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
            font-size: 0.85rem;
            white-space: pre-wrap;
            display: none;
            border-left: 4px solid var(--accent-color);
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-bottom: 10px;
        }
        .status-200 { background: #059669; color: white; }
        .status-401 { background: #dc2626; color: white; }

        footer { padding: 4rem; text-align: center; color: var(--text-muted); font-size: 0.8rem; }
    </style>
</head>
<body>

<header>
    <div class="app-info">
        <span class="version">v<?= $appVersion ?></span>
        <h1><?= $appName ?> <span style="color:var(--accent-color)">Explorer</span></h1>
        <p style="color:var(--text-muted); margin-top:5px;">Interactive API documentation inspired by FastAPI.</p>
    </div>
</header>

<div class="container">
    <?php if (empty($apiData)): ?>
        <div style="padding: 100px; text-align: center; color: var(--text-muted);">
            <h2>No API Endpoints Found</h2>
            <p>Add @Summary and other DocBlocks to your controllers to see them here.</p>
        </div>
    <?php else: ?>
        <?php foreach ($apiData as $api): ?>
            <div class="endpoint-card" id="card-<?= $api['id'] ?>">
                <div class="endpoint-header" onclick="toggleEndpoint('<?= $api['id'] ?>')">
                    <span class="method <?= $api['method'] ?>"><?= $api['method'] ?></span>
                    <span class="path"><?= $api['path'] ?></span>
                    <span class="summary"><?= $api['summary'] ?></span>
                </div>
                
                <div class="endpoint-body" id="body-<?= $api['id'] ?>">
                    <p style="font-size:0.9rem; color:var(--text-muted);"><?= $api['summary'] ?></p>
                    
                    <div class="table-container">
                        <h3>Parameters</h3>
                        <?php if (empty($api['parameters'])): ?>
                            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:10px;">No parameters required.</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($api['parameters'] as $p): ?>
                                        <tr>
                                            <td><strong><?= $p['name'] ?></strong> <?= $p['required'] ? '<span style="color:red">*</span>' : '' ?></td>
                                            <td><code style="color:var(--accent-color)"><?= $p['type'] ?></code></td>
                                            <td><?= $p['description'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <div class="try-it-session">
                        <h3>Try It Out</h3>
                        <form id="form-<?= $api['id'] ?>" onsubmit="executeRequest(event, '<?= $api['id'] ?>', '<?= $api['path'] ?>', '<?= $api['method'] ?>')">
                            <?php foreach ($api['parameters'] as $p): ?>
                                <div class="form-group">
                                    <label><?= ucfirst($p['name']) ?> (<?= $p['type'] ?>)</label>
                                    <input type="text" name="<?= $p['name'] ?>" placeholder="Enter value..." <?= $p['required'] ? 'required' : '' ?>>
                                </div>
                            <?php endforeach; ?>

                            <!-- Authentication Helper -->
                            <div class="form-group">
                                <label>Bearer Token (Optional)</label>
                                <input type="text" id="token-<?= $api['id'] ?>" placeholder="Paste token here for protected routes...">
                            </div>

                            <button type="submit" class="btn-execute">Execute ⚡</button>
                        </form>

                        <div class="response-area" id="response-<?= $api['id'] ?>">
                            <!-- Real-time response load here -->
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<footer>
    &copy; <?= date('Y') ?> NovaFlow Enterprise Framework. Modernization Kit.
</footer>

<script>
    function toggleEndpoint(id) {
        const body = document.getElementById('body-' + id);
        const isOpen = body.style.display === 'block';
        
        // Close others? (Optional FastAPI behavior)
        // document.querySelectorAll('.endpoint-body').forEach(el => el.style.display = 'none');
        
        body.style.display = isOpen ? 'none' : 'block';
    }

    async function executeRequest(event, id, path, method) {
        event.preventDefault();
        const display = document.getElementById('response-' + id);
        const token = document.getElementById('token-' + id).value;
        const form = document.getElementById('form-' + id);
        const formData = new FormData(form);
        
        display.style.display = 'block';
        display.innerHTML = 'Executing request...';

        const options = {
            method: method,
            headers: {}
        };

        if (token) {
            options.headers['Authorization'] = 'Bearer ' + token;
        }

        const baseUrl = '<?= $baseUrl ?>';
        let url = baseUrl + path;
        
        if (method === 'GET') {
            const params = new URLSearchParams(formData);
            url += '?' + params.toString();
        } else {
            options.body = formData;
        }

        try {
            const start = performance.now();
            const response = await fetch(url, options);
            const end = performance.now();
            const text = await response.text(); // First get text to debug
            
            try {
                const data = JSON.parse(text);
                const statusClass = response.ok ? 'status-200' : 'status-401';
                const time = (end - start).toFixed(2);

                display.innerHTML = `
                    <div class="status-badge ${statusClass}">Status: ${response.status} ${response.statusText}</div>
                    <div style="color:var(--text-muted); font-size:0.75rem; margin-bottom:10px;">Time: ${time}ms</div>
                    <code>${JSON.stringify(data, null, 4)}</code>
                `;
            } catch (jsonError) {
                // If JSON parse fails, show why
                display.innerHTML = `
                    <div class="status-badge status-401">JSON Parse Error</div>
                    <div style="color:var(--text-muted); font-size:0.75rem; margin-bottom:10px;">Raw Response:</div>
                    <pre style="background:#333; padding:10px; color:#ff79c6;">${text.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
                `;
            }
            
        } catch (error) {
            display.innerHTML = `<div class="status-badge status-401">Error</div><br><code>${error.message}</code>`;
        }
    }
</script>

</body>
</html>
