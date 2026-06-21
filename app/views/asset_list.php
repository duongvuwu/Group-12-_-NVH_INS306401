<?php 
/** * @var array $assets 
 * @var array $stats 
 */ 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Download Center</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; padding: 20px; background-color: transparent; color: #1e293b; }
        .dashboard-header { display: flex; gap: 20px; margin-bottom: 20px; align-items: stretch; }
        
        .control-panel { flex: 1; padding: 20px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; justify-content: center; gap: 15px; }
        .chart-panel { width: 300px; padding: 20px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display: flex; align-items: center; justify-content: center; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background-color: #f8fafc; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        tr:hover { background-color: #f1f5f9; }
        
        .form-group { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        input, select, button, .btn { padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s; }
        input:focus, select:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        
        button, .btn { cursor: pointer; background: #0ea5e9; color: white; border: none; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        button:hover, .btn:hover { background: #0284c7; transform: translateY(-1px); }
        .btn-success { background: #10b981; } .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; padding: 8px 12px; font-size: 13px; } .btn-danger:hover { background: #dc2626; }
        .btn-copy { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 6px 10px; font-size: 12px; font-weight: 600;} .btn-copy:hover { background: #e2e8f0; color: #0f172a; }
        
        .search-box { width: 100%; max-width: 300px; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%2364748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>'); background-repeat: no-repeat; background-position: 12px center; padding-left: 40px; }
        
        /* CSS cho các OS Badges */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
        .badge-windows { background-color: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe; }
        .badge-mac { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
        .badge-linux { background-color: #fef08a; color: #854d0e; border: 1px solid #fde047; }
        .badge-default { background-color: #f1f5f9; color: #475569; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="control-panel">
            <h3 style="margin: 0; color: #0f172a; font-size: 18px;">⚡ Quản lý Tài nguyên Cài đặt</h3>
            <form id="addAssetForm" class="form-group" style="margin: 0;">
                <input type="number" name="software_id" placeholder="ID Phần mềm" required style="width: 130px;">
                <select name="os_type" required>
                    <option value="Windows">Windows</option>
                    <option value="Mac">Mac</option>
                    <option value="Linux">Linux</option>
                </select>
                <input type="text" name="download_link" placeholder="Link Google Drive..." required style="flex: 1; min-width: 200px;">
                <button type="submit">➕ Thêm Mới</button>
            </form>
            <div class="form-group" style="margin-top: 5px;">
                <input type="text" id="liveSearchInput" class="search-box" placeholder="Tìm nhanh phần mềm...">
                <a href="index.php?page=assets&action=export" class="btn btn-success">📥 Xuất file CSV</a>
            </div>
        </div>

        <div class="chart-panel">
            <canvas id="osChart" style="max-height: 180px;"></canvas>
        </div>
    </div>

    <table id="assetTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên phần mềm</th>
                <th>Hệ điều hành</th>
                <th>Liên kết tải về</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($assets)): ?>
                <?php foreach ($assets as $a): 
                    // Xác định class CSS cho badge tùy theo Hệ điều hành
                    $os = strtolower($a['os_type']);
                    $badgeClass = 'badge-default';
                    if ($os === 'windows') $badgeClass = 'badge-windows';
                    elseif ($os === 'mac') $badgeClass = 'badge-mac';
                    elseif ($os === 'linux') $badgeClass = 'badge-linux';
                ?>
                <tr id="row-<?= $a['id'] ?>">
                    <td style="color: #64748b; font-weight: 500;">#<?= $a['id'] ?></td>
                    <td class="software-title"><strong style="color: #0f172a;"><?= htmlspecialchars($a['name'] ?? 'N/A') ?></strong></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($a['os_type']) ?></span></td>
                    <td>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <a href="<?= htmlspecialchars($a['download_link'] ?? '') ?>" target="_blank" style="color: #0ea5e9; font-weight: 500; text-decoration: none;">Link Drive ↗</a>
                            <button class="btn-copy" onclick="copyToClipboard('<?= addslashes($a['download_link'] ?? '') ?>')">Copy</button>
                        </div>
                    </td>
                    <td><button class="btn-danger" onclick="deleteAsset(<?= $a['id'] ?>)">Thùng rác</button></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr id="noDataRow"><td colspan="5" style="text-align: center; color: #94a3b8; padding: 40px;">Chưa có tài nguyên nào được tải lên.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    // 1. VẼ BIỂU ĐỒ TRÒN TRỰC TIẾP
    const statsData = <?= json_encode($stats ?? []) ?>;
    if (statsData.length > 0) {
        const ctx = document.getElementById('osChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: statsData.map(item => item.os_type),
                datasets: [{
                    data: statsData.map(item => item.total),
                    backgroundColor: ['#3b82f6', '#a855f7', '#eab308'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    }

    // 2. LIVE SEARCH
    document.getElementById('liveSearchInput').addEventListener('keyup', function() {
        let keyword = this.value.toLowerCase().trim();
        let rows = document.querySelectorAll('#assetTable tbody tr:not(#noDataRow)');
        rows.forEach(row => {
            let titleElement = row.querySelector('.software-title');
            if (titleElement) {
                row.style.display = titleElement.textContent.toLowerCase().indexOf(keyword) > -1 ? "" : "none"; 
            }
        });
    });

    // 3. COPY LINK
    function copyToClipboard(text) {
        if (!text) {
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'info', title: 'Chưa có link để copy!', showConfirmButton: false, timer: 1500 });
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: 'Đã copy link!', showConfirmButton: false, timer: 1500 });
        });
    }

    // 4. AJAX THÊM MỚI
    document.getElementById('addAssetForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('index.php?page=assets&action=add', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') Swal.fire('Thành công!', data.message, 'success').then(() => location.reload());
            else Swal.fire('Lỗi!', data.message, 'error');
        });
    });

    // 5. AJAX XÓA
    function deleteAsset(id) {
        Swal.fire({
            title: 'Xóa tài nguyên này?', text: "Hành động này không thể hoàn tác!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#94a3b8', confirmButtonText: 'Xóa ngay'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData(); formData.append('id', id);
                fetch('index.php?page=assets&action=delete', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        document.getElementById('row-' + id).remove();
                        Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: 'Đã xóa!', showConfirmButton: false, timer: 1500 });
                    }
                });
            }
        });
    }
    </script>
</body>
</html>