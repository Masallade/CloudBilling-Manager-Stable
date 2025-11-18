<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
#ci-debugbar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #1e1e1e;
    color: #fff;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 11px;
    z-index: 99999;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
    max-height: 50vh;
    overflow-y: auto;
}

#ci-debugbar-header {
    background: #2d2d2d;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #444;
    cursor: pointer;
    user-select: none;
}

#ci-debugbar-header:hover {
    background: #333;
}

#ci-debugbar-tabs {
    display: flex;
    gap: 2px;
    flex-wrap: wrap;
}

.ci-debugbar-tab {
    padding: 6px 12px;
    background: #3a3a3a;
    border-radius: 3px 3px 0 0;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
}

.ci-debugbar-tab:hover {
    background: #4a4a4a;
}

.ci-debugbar-tab.active {
    background: #1e1e1e;
    border-bottom: 2px solid #4CAF50;
}

.ci-debugbar-tab .badge {
    background: #4CAF50;
    color: #fff;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    margin-left: 6px;
}

.ci-debugbar-tab .badge.warning {
    background: #ff9800;
}

.ci-debugbar-content {
    display: none;
    padding: 12px;
    max-height: 400px;
    overflow-y: auto;
}

.ci-debugbar-content.active {
    display: block;
}

.ci-debugbar-toggle {
    background: #4CAF50;
    color: #fff;
    border: none;
    padding: 4px 8px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 10px;
}

.ci-debugbar-toggle:hover {
    background: #45a049;
}

.ci-debugbar-stats {
    display: flex;
    gap: 15px;
    margin-left: auto;
    margin-right: 15px;
}

.ci-debugbar-stat {
    display: flex;
    align-items: center;
    gap: 5px;
}

.ci-debugbar-stat-label {
    color: #aaa;
    font-size: 10px;
}

.ci-debugbar-stat-value {
    color: #4CAF50;
    font-weight: bold;
}

table.ci-debugbar-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

table.ci-debugbar-table th,
table.ci-debugbar-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #444;
}

table.ci-debugbar-table th {
    background: #2d2d2d;
    color: #4CAF50;
    font-weight: bold;
}

table.ci-debugbar-table tr:hover {
    background: #2d2d2d;
}

.ci-debugbar-query {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 10px;
    background: #2d2d2d;
    padding: 8px;
    border-radius: 3px;
    margin: 5px 0;
    overflow-x: auto;
}

.ci-debugbar-query-time {
    color: #ff9800;
    margin-left: 10px;
}

.ci-debugbar-message {
    padding: 6px;
    margin: 4px 0;
    border-left: 3px solid #4CAF50;
    background: #2d2d2d;
    border-radius: 2px;
}

.ci-debugbar-message.error {
    border-left-color: #f44336;
}

.ci-debugbar-message.warning {
    border-left-color: #ff9800;
}

.ci-debugbar-message.info {
    border-left-color: #2196F3;
}

.ci-debugbar-collapsed #ci-debugbar-content-wrapper {
    display: none;
}

pre {
    background: #2d2d2d;
    padding: 10px;
    border-radius: 3px;
    overflow-x: auto;
    margin: 5px 0;
}

.json-key {
    color: #9cdcfe;
}

.json-string {
    color: #ce9178;
}

.json-number {
    color: #b5cea8;
}
</style>

<div id="ci-debugbar" class="ci-debugbar-collapsed">
    <div id="ci-debugbar-header" onclick="toggleDebugbar()">
        <div id="ci-debugbar-tabs">
            <div class="ci-debugbar-tab active" onclick="showTab(event, 'overview')">
                Overview
            </div>
            <div class="ci-debugbar-tab" onclick="showTab(event, 'queries')">
                Queries
                <?php if ($queries_count > 0): ?>
                    <span class="badge"><?php echo $queries_count; ?></span>
                <?php endif; ?>
            </div>
            <div class="ci-debugbar-tab" onclick="showTab(event, 'messages')">
                Messages
                <?php if (count($messages) > 0): ?>
                    <span class="badge"><?php echo count($messages); ?></span>
                <?php endif; ?>
            </div>
            <div class="ci-debugbar-tab" onclick="showTab(event, 'views')">
                Views
                <?php if (count($views) > 0): ?>
                    <span class="badge"><?php echo count($views); ?></span>
                <?php endif; ?>
            </div>
            <div class="ci-debugbar-tab" onclick="showTab(event, 'request')">
                Request
            </div>
            <div class="ci-debugbar-tab" onclick="showTab(event, 'files')">
                Files
                <?php if (count($files) > 0): ?>
                    <span class="badge"><?php echo count($files); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="ci-debugbar-stats">
            <div class="ci-debugbar-stat">
                <span class="ci-debugbar-stat-label">Time:</span>
                <span class="ci-debugbar-stat-value"><?php echo $execution_time; ?>ms</span>
            </div>
            <div class="ci-debugbar-stat">
                <span class="ci-debugbar-stat-label">Memory:</span>
                <span class="ci-debugbar-stat-value"><?php echo $memory_usage; ?></span>
            </div>
            <div class="ci-debugbar-stat">
                <span class="ci-debugbar-stat-label">Peak:</span>
                <span class="ci-debugbar-stat-value"><?php echo $memory_peak; ?></span>
            </div>
        </div>
        <button class="ci-debugbar-toggle" onclick="event.stopPropagation(); toggleDebugbar()">Toggle</button>
    </div>
    
    <div id="ci-debugbar-content-wrapper">
        <div id="overview" class="ci-debugbar-content active">
            <h3 style="color: #4CAF50; margin-top: 0;">Application Overview</h3>
            <table class="ci-debugbar-table">
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Execution Time</td>
                    <td><?php echo $execution_time; ?> ms</td>
                </tr>
                <tr>
                    <td>Memory Usage</td>
                    <td><?php echo $memory_usage; ?></td>
                </tr>
                <tr>
                    <td>Memory Peak</td>
                    <td><?php echo $memory_peak; ?></td>
                </tr>
                <tr>
                    <td>Database Queries</td>
                    <td><?php echo $queries_count; ?></td>
                </tr>
                <tr>
                    <td>Controller</td>
                    <td><?php echo $server['controller']; ?>/<?php echo $server['method']; ?></td>
                </tr>
                <tr>
                    <td>URI</td>
                    <td><?php echo $server['uri']; ?></td>
                </tr>
                <tr>
                    <td>Method</td>
                    <td><?php echo $server['method']; ?></td>
                </tr>
                <tr>
                    <td>IP Address</td>
                    <td><?php echo $server['ip']; ?></td>
                </tr>
            </table>
        </div>
        
        <div id="queries" class="ci-debugbar-content">
            <h3 style="color: #4CAF50; margin-top: 0;">Database Queries (<?php echo $queries_count; ?>)</h3>
            <?php if (count($queries) > 0): ?>
                <?php foreach ($queries as $index => $query): ?>
                    <div class="ci-debugbar-query">
                        <strong>Query #<?php echo $index + 1; ?>:</strong>
                        <span class="ci-debugbar-query-time"><?php echo number_format($query['time'] * 1000, 2); ?>ms</span>
                        <pre style="margin-top: 5px; color: #ce9178;"><?php echo htmlspecialchars($query['query']); ?></pre>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No database queries executed.</p>
            <?php endif; ?>
        </div>
        
        <div id="messages" class="ci-debugbar-content">
            <h3 style="color: #4CAF50; margin-top: 0;">Messages (<?php echo count($messages); ?>)</h3>
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="ci-debugbar-message <?php echo $message['type']; ?>">
                        <strong>[<?php echo strtoupper($message['type']); ?>]</strong>
                        <?php echo htmlspecialchars($message['message']); ?>
                        <span style="color: #aaa; float: right;"><?php echo number_format($message['time'] * 1000, 2); ?>ms</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages logged.</p>
            <?php endif; ?>
        </div>
        
        <div id="views" class="ci-debugbar-content">
            <h3 style="color: #4CAF50; margin-top: 0;">Loaded Views (<?php echo count($views); ?>)</h3>
            <?php if (count($views) > 0): ?>
                <table class="ci-debugbar-table">
                    <tr>
                        <th>View</th>
                        <th>Time</th>
                    </tr>
                    <?php foreach ($views as $view): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($view['view']); ?></td>
                            <td><?php echo number_format($view['time'] * 1000, 2); ?>ms</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No views loaded.</p>
            <?php endif; ?>
        </div>
        
        <div id="request" class="ci-debugbar-content">
            <h3 style="color: #4CAF50; margin-top: 0;">Request Information</h3>
            <h4 style="color: #aaa;">GET Parameters</h4>
            <?php if (!empty($get)): ?>
                <pre><?php echo htmlspecialchars(json_encode($get, JSON_PRETTY_PRINT)); ?></pre>
            <?php else: ?>
                <p>No GET parameters.</p>
            <?php endif; ?>
            
            <h4 style="color: #aaa;">POST Parameters</h4>
            <?php if (!empty($post)): ?>
                <pre><?php echo htmlspecialchars(json_encode($post, JSON_PRETTY_PRINT)); ?></pre>
            <?php else: ?>
                <p>No POST parameters.</p>
            <?php endif; ?>
            
            <h4 style="color: #aaa;">Session Data</h4>
            <?php if (!empty($session)): ?>
                <pre><?php echo htmlspecialchars(json_encode($session, JSON_PRETTY_PRINT)); ?></pre>
            <?php else: ?>
                <p>No session data.</p>
            <?php endif; ?>
        </div>
        
        <div id="files" class="ci-debugbar-content">
            <h3 style="color: #4CAF50; margin-top: 0;">Included Files (<?php echo count($files); ?>)</h3>
            <table class="ci-debugbar-table">
                <tr>
                    <th>File</th>
                    <th>Size</th>
                </tr>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['file']); ?></td>
                        <td><?php echo number_format($file['size'] / 1024, 2); ?> KB</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<script>
function toggleDebugbar() {
    var debugbar = document.getElementById('ci-debugbar');
    debugbar.classList.toggle('ci-debugbar-collapsed');
}

function showTab(event, tabName) {
    event.stopPropagation();
    
    // Hide all tab contents
    var contents = document.getElementsByClassName('ci-debugbar-content');
    for (var i = 0; i < contents.length; i++) {
        contents[i].classList.remove('active');
    }
    
    // Remove active class from all tabs
    var tabs = document.getElementsByClassName('ci-debugbar-tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show selected tab content
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked tab
    event.currentTarget.classList.add('active');
}
</script>





