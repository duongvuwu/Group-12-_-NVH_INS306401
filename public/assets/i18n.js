(function () {
    const translations = {
        // Global navigation and layout.
        'Nền tảng': 'Platform',
        'Luật cấp phát': 'Allocation Rules',
        'Kho license': 'License Inventory',
        'Cấp phát': 'Allocations',
        'Dashboard vận hành': 'Operations Dashboard',
        'Tổng quan SaaS license': 'SaaS License Overview',
        'Quản trị nền tảng': 'Platform Administration',
        'Transaction cấp key, prepared statements và audit log đang bảo vệ các thao tác nhạy cảm.': 'Key allocation transactions, prepared statements, and audit logs protect sensitive operations.',
        'Đổi giao diện sáng/tối': 'Switch light/dark theme',
        'Ngôn ngữ': 'Language',
        'Tiếng Việt': 'Vietnamese',
        'Xác nhận thao tác': 'Confirm action',
        'Lý do thu hồi': 'Revocation reason',
        'Ví dụ: Hết nhu cầu sử dụng, chuyển khoa, vi phạm chính sách...': 'Example: No longer needed, department transfer, policy violation...',
        'Hủy': 'Cancel',
        'Xác nhận': 'Confirm',
        'Xác nhận thực hiện thao tác này?': 'Confirm this action?',
        'Cần nhập lý do thu hồi.': 'A revocation reason is required.',
        'Trước': 'Previous',
        'Sau': 'Next',
        'Không có nội dung phù hợp': 'No matching content',

        // Dashboard.
        'Dashboard vận hành License': 'License Operations Dashboard',
        'Theo dõi tồn kho key, license đang dùng, nhu cầu theo khoa và cảnh báo hết hạn trong một màn hình điều phối gọn, mượt, đúng chuẩn XAMPP localhost.': 'Monitor key inventory, active licenses, department demand, and expiry alerts from one streamlined XAMPP-ready workspace.',
        'Khoa/Phòng ban': 'Departments',
        'Đơn vị đang được quản lý': 'Managed organizational units',
        'Tổng số Key': 'Total Keys',
        'Serial đã nhập vào kho': 'Serials imported into inventory',
        'Key còn trống': 'Available Keys',
        'Sẵn sàng cấp phát': 'Ready for allocation',
        'License active': 'Active Licenses',
        'Đang gắn cho người dùng': 'Assigned to users',
        'Phần mềm': 'Software',
        'Danh mục phần mềm bản quyền': 'Licensed software catalog',
        'Tỷ lệ sử dụng': 'Usage Rate',
        'Key đã cấp trên tổng key': 'Allocated keys out of total keys',
        'Tồn kho theo phần mềm': 'Inventory by Software',
        'Nhu cầu theo khoa': 'Demand by Department',
        'License sắp hết hạn trong 14 ngày': 'Licenses Expiring Within 14 Days',
        'Người dùng': 'Users',
        'Khoa': 'Department',
        'Còn lại': 'Remaining',
        'Không có license sắp hết hạn.': 'No licenses are nearing expiration.',
        'Đã cấp': 'Allocated',
        'Còn trống': 'Available',
        'Phần mềm được cấp nhiều nhất': 'Most Allocated Software',
        'Khoa sử dụng license nhiều nhất': 'Departments with Highest License Usage',
        'Phần mềm chưa từng được cấp': 'Software Never Allocated',
        'Tất cả phần mềm đã có lịch sử cấp phát.': 'Every software title has allocation history.',

        // Platform and master data.
        'Nền tảng vận hành': 'Platform Operations',
        'Quản lý khoa/phòng ban, người dùng và danh mục phần mềm trước khi bật luật cấp phát license.': 'Manage departments, users, and software before enabling allocation rules.',
        'Khoa/phòng ban': 'Department',
        'Nền phân quyền theo đơn vị': 'Department-based access scope',
        'Tên khoa': 'Department name',
        'Mô tả': 'Description',
        'Ví dụ: Khoa Công nghệ thông tin': 'Example: Faculty of Information Technology',
        'Ghi chú ngắn': 'Short description',
        'Thêm khoa': 'Add Department',
        'Sinh viên, giảng viên, quản trị': 'Students, lecturers, and administrators',
        'Họ tên': 'Full name',
        'Sinh viên': 'Student',
        'Giảng viên': 'Lecturer',
        'Quản trị': 'Administrator',
        'Tất cả': 'All',
        'Thêm người dùng': 'Add User',
        'Danh mục tài sản bản quyền': 'Licensed asset catalog',
        'Tên phần mềm': 'Software name',
        'Nhà phát hành': 'Vendor',
        'Ví dụ: MATLAB': 'Example: MATLAB',
        'Ví dụ: MathWorks': 'Example: MathWorks',
        'Thêm phần mềm': 'Add Software',
        'Danh sách khoa/phòng ban': 'Department List',
        'Chặn xóa nếu còn dữ liệu phụ thuộc.': 'Deletion is blocked while dependent data exists.',
        'Lọc khoa...': 'Filter departments...',
        'Vai trò': 'Role',
        'License': 'Licenses',
        'Hành động': 'Actions',
        'Lọc người dùng...': 'Filter users...',
        'Xuất danh sách': 'Export Users',
        'Chi tiết': 'Details',
        'Chi tiết người dùng': 'User Details',
        'Đang tải dữ liệu...': 'Loading data...',
        'Lịch sử license': 'License History',
        'Thời hạn': 'Period',
        'Người dùng chưa có lịch sử license.': 'This user has no license history.',
        'Không thể tải chi tiết người dùng.': 'Unable to load user details.',
        'Danh mục phần mềm': 'Software Catalog',
        'Không xóa phần mềm đã có dữ liệu phụ thuộc.': 'Software with dependent data cannot be deleted.',
        'Lọc phần mềm...': 'Filter software...',
        'Tổng': 'Total',
        'Trống': 'Available',
        'Xóa': 'Delete',
        'Xóa khoa/phòng ban này? Thao tác chỉ thành công nếu chưa có dữ liệu phụ thuộc.': 'Delete this department? This succeeds only when no dependent data exists.',
        'Xóa người dùng này? Người dùng đã có lịch sử cấp phát sẽ được hệ thống chặn xóa.': 'Delete this user? Users with allocation history cannot be deleted.',
        'Xóa phần mềm này? Hệ thống sẽ chặn nếu phần mềm đang có dữ liệu phụ thuộc.': 'Delete this software? The system blocks deletion when dependent data exists.',

        // Software download center.
        'Quản lý phiên bản, hệ điều hành và link tải an toàn cho từng phần mềm đã được cấp license.': 'Manage versions, operating systems, and secure download links for licensed software.',
        'Phần mềm có link': 'Software with Links',
        'Thêm tài nguyên cài đặt': 'Add Installation Asset',
        'Một phiên bản cho từng hệ điều hành': 'One version per operating system',
        'Phiên bản': 'Version',
        'Hệ điều hành': 'Operating System',
        'Ví dụ: R2026a': 'Example: R2026a',
        'Thêm tài nguyên': 'Add Asset',
        'Tài nguyên theo hệ điều hành': 'Assets by Operating System',
        'Biểu đồ cập nhật trực tiếp từ software_assets.': 'Chart updated directly from software_assets.',
        'Danh sách tài nguyên': 'Asset List',
        'Tìm kiếm, sao chép hoặc xuất toàn bộ link cài đặt.': 'Search, copy, or export installation links.',
        'Tìm phần mềm, phiên bản, OS...': 'Search software, version, OS...',
        'Xuất CSV': 'Export CSV',
        'Link tải': 'Download Link',
        'Sao chép link': 'Copy Link',
        'Xóa tài nguyên cài đặt này?': 'Delete this installation asset?',
        'Chưa có tài nguyên cài đặt.': 'No installation assets yet.',
        'Đã sao chép link tải.': 'Download link copied.',
        'Không thể sao chép link tải.': 'Unable to copy download link.',

        // Allocation rules.
        'Luật cấp phát thông minh': 'Smart Allocation Rules',
        'Định nghĩa ai được dùng phần mềm nào theo khoa và vai trò; backend chặn trùng luật và chặn xóa luật đang có license active.': 'Define software access by department and role; the backend prevents duplicate rules and protects rules with active licenses.',
        'Tạo luật cấp phát': 'Create Allocation Rule',
        'Phần mềm + khoa + vai trò là duy nhất': 'Software + department + role must be unique',
        'Đối tượng': 'Target Role',
        'Tất cả vai trò': 'All Roles',
        'Thêm luật cấp phát': 'Add Allocation Rule',
        'Gợi ý tự động theo tên khoa': 'Automatic Suggestions by Department',
        'Chưa có phần mềm gợi ý trong danh mục.': 'No suggested software is available in the catalog.',
        'Luật đẹp nhưng dữ liệu phải chắc': 'Clean Rules, Reliable Data',
        'Model dùng prepared statements, kiểm tra trùng tổ hợp, và không cho xóa luật khi vẫn còn allocation active phụ thuộc. Luồng cấp phát thực tế dùng transaction + khóa dòng key trống.': 'The model uses prepared statements, rejects duplicate combinations, and protects rules with active allocations. Actual allocation uses transactions and row-level key locking.',
        'Luật hiện có': 'Current Rules',
        'Khoa/phòng ban': 'Department',
        'Danh sách luật cấp phát': 'Allocation Rule List',
        'Lọc luật...': 'Filter rules...',
        'Đang dùng': 'Active',
        'Xóa luật': 'Delete Rule',
        'Xóa luật cấp phát này? Hệ thống sẽ chặn nếu còn license active phụ thuộc.': 'Delete this allocation rule? Active dependent licenses will block deletion.',

        // Inventory.
        'Kho license vận hành': 'License Inventory Operations',
        'Quản lý pool, key thật và link cài đặt. Số lượng còn trống luôn được đồng bộ từ license_keys.': 'Manage pools, real keys, and installation links. Availability is always synchronized from license_keys.',
        'Tạo pool license': 'Create License Pool',
        'Pool quản lý số lượng và hạn sử dụng': 'Pools manage quantities and expiration',
        'Số lượng': 'Quantity',
        'Tổng số license mua': 'Total purchased licenses',
        'Ngày mua': 'Purchase Date',
        'Ngày hết hạn': 'Expiration Date',
        'Cho phép tái sử dụng key sau thu hồi': 'Allow key reuse after revocation',
        'Tạo pool': 'Create Pool',
        'Nhập key chi tiết': 'Import License Keys',
        'Mỗi dòng một key, không vượt sức chứa pool': 'One key per line, within pool capacity',
        'Nhập key': 'Import Keys',
        'Link cài đặt': 'Installation Link',
        'Asset theo phiên bản và hệ điều hành': 'Assets by version and operating system',
        'Phiên bản, ví dụ R2026a': 'Version, for example R2026a',
        'Thêm link': 'Add Link',
        'Tổng quan tồn kho': 'Inventory Overview',
        'Số liệu tính trực tiếp từ pool, key và allocation.': 'Metrics are calculated directly from pools, keys, and allocations.',
        'Lọc tồn kho...': 'Filter inventory...',
        'Pool': 'Pool',
        'Assets': 'Assets',
        'Pool license': 'License Pools',
        'Pool ghi số lượng tổng; key thật nằm ở license_keys.': 'Pools store total capacity; actual keys live in license_keys.',
        'Key': 'Keys',
        'Còn': 'Available',
        'Hết hạn': 'Expires',
        'Software assets': 'Software Assets',
        'Link tải hiển thị khi người dùng nhận license.': 'Download links appear when users receive a license.',
        'Asset': 'Asset',
        'Mở link tải': 'Open Download Link',
        'Xóa link': 'Delete Link',
        'Xóa link cài đặt này?': 'Delete this installation link?',

        // Allocations.
        'Cấp phát license': 'License Allocation',
        'Kiểm tra luật, rút key còn trống bằng transaction, ghi nhận kích hoạt và thu hồi key theo vòng đời license.': 'Validate rules, reserve an available key transactionally, record activations, and revoke keys across the license lifecycle.',
        'Luật hợp lệ mới được rút key': 'A valid rule is required before reserving a key',
        'Thời hạn': 'Duration',
        'Cấp phát ngay': 'Allocate Now',
        'Kho chưa có key trống': 'No Available Keys',
        'Đang active': 'Active',
        'Đã thu hồi': 'Revoked',
        'Luồng cấp phát dùng prepared statements, transaction và': 'Allocation uses prepared statements, transactions, and',
        'Khi thu hồi license reusable, key được trả về kho và số lượng còn trống được đồng bộ từ dữ liệu thật.': 'When a reusable license is revoked, its key returns to inventory and availability is synchronized from actual key records.',
        'Lịch sử cấp phát': 'Allocation History',
        'Theo dõi key, kích hoạt, hết hạn và thu hồi.': 'Track keys, activations, expirations, and revocations.',
        'Lọc cấp phát...': 'Filter allocations...',
        'Trạng thái': 'Status',
        'Kích hoạt': 'Activations',
        'Chưa có asset': 'No assets',
        'Ghi nhận kích hoạt': 'Record Activation',
        'Thu hồi': 'Revoke',
        'Đã đóng': 'Closed',
        'Chưa có giao dịch cấp phát.': 'No allocation transactions yet.',
        'Ghi nhận một lượt kích hoạt cho license này?': 'Record an activation for this license?',
        'Thu hồi license này? Nếu pool cho phép tái sử dụng, key sẽ được trả lại kho.': 'Revoke this license? If the pool allows reuse, the key will return to inventory.',

        // Common server feedback.
        'Thao tác không hợp lệ.': 'Invalid action.',
        'Đã thêm khoa/phòng ban mới.': 'Department added successfully.',
        'Đã xóa khoa/phòng ban.': 'Department deleted successfully.',
        'Đã thêm người dùng mới.': 'User added successfully.',
        'Đã xóa người dùng.': 'User deleted successfully.',
        'Đã thêm phần mềm mới.': 'Software added successfully.',
        'Đã xóa phần mềm.': 'Software deleted successfully.',
        'Đã thêm luật cấp phát mới.': 'Allocation rule added successfully.',
        'Đã xóa luật cấp phát.': 'Allocation rule deleted successfully.',
        'Đã tạo pool license mới. Hãy nhập key chi tiết cho pool này.': 'License pool created. Import its individual keys next.',
        'Đã thêm link cài đặt phần mềm.': 'Installation link added successfully.',
        'Đã xóa link cài đặt.': 'Installation link deleted successfully.',
        'Đã cấp phát license thành công.': 'License allocated successfully.',
        'Đã ghi nhận kích hoạt license.': 'License activation recorded.',
        'Đã thu hồi license.': 'License revoked successfully.',
        'Người dùng chưa đủ điều kiện theo luật cấp phát.': 'The user is not eligible under the current allocation rules.',
        'Người dùng đang có license active cho phần mềm này.': 'The user already has an active license for this software.',
        'Kho không còn key trống phù hợp với thời hạn yêu cầu.': 'No available key matches the requested duration.'
    };

    const reverseTranslations = Object.fromEntries(
        Object.entries(translations).map(([vietnamese, english]) => [english, vietnamese])
    );
    const originalText = new WeakMap();
    const originalAttributes = new WeakMap();
    const translatedAttributes = ['placeholder', 'title', 'aria-label', 'data-confirm-message'];
    let currentLanguage = localStorage.getItem('license-language') === 'en' ? 'en' : 'vi';

    function translatePattern(value, language) {
        if (language === 'vi') return value;

        let match = value.match(/^Hiển thị (\d+)-(\d+) \/ (\d+) nội dung$/);
        if (match) return `Showing ${match[1]}-${match[2]} of ${match[3]} items`;

        match = value.match(/^Trang (\d+)$/);
        if (match) return `Page ${match[1]}`;

        match = value.match(/^(\d+) ngày$/);
        if (match) return `${match[1]} days`;

        match = value.match(/^(.+) · (\d+) key trống(?: · Hết key)?$/);
        if (match) return `${match[1]} · ${match[2]} available keys${value.includes('Hết key') ? ' · Out of stock' : ''}`;

        match = value.match(/^(.+) · (.+) · (Sinh viên|Giảng viên|Quản trị)$/);
        if (match) return `${match[1]} · ${match[2]} · ${translations[match[3]]}`;

        match = value.match(/^(.+) · (Sinh viên|Giảng viên|Quản trị)$/);
        if (match) return `${match[1]} · ${translations[match[2]]}`;

        match = value.match(/^(\d+) ngày$/);
        if (match) return `${match[1]} days`;

        match = value.match(/^Đã nhập (\d+) key mới vào kho\.$/);
        if (match) return `${match[1]} new keys were imported into inventory.`;

        match = value.match(/^Đã chọn gợi ý (.+)\.$/);
        if (match) return `Selected suggestion ${match[1]}.`;

        return value;
    }

    function canonicalValue(value) {
        return reverseTranslations[value] || value;
    }

    function translate(value, language = currentLanguage) {
        const canonical = canonicalValue(value);
        if (language === 'vi') return canonical;
        return translations[canonical] || translatePattern(canonical, language);
    }

    function translateTextNode(node) {
        if (!originalText.has(node)) {
            originalText.set(node, canonicalValue(node.nodeValue.trim()));
        }

        const raw = node.nodeValue;
        const trimmed = raw.trim();
        if (!trimmed) return;

        const leading = raw.match(/^\s*/)?.[0] || '';
        const trailing = raw.match(/\s*$/)?.[0] || '';
        const target = translate(originalText.get(node));
        const nextValue = `${leading}${target}${trailing}`;

        if (node.nodeValue !== nextValue) {
            node.nodeValue = nextValue;
        }
    }

    function translateElementAttributes(element) {
        let originals = originalAttributes.get(element);
        if (!originals) {
            originals = {};
            originalAttributes.set(element, originals);
        }

        translatedAttributes.forEach((attribute) => {
            if (!element.hasAttribute(attribute)) return;
            if (!(attribute in originals)) {
                originals[attribute] = canonicalValue(element.getAttribute(attribute));
            }
            element.setAttribute(attribute, translate(originals[attribute]));
        });
    }

    function translateTree(root) {
        if (!root) return;

        if (root.nodeType === Node.TEXT_NODE) {
            translateTextNode(root);
            return;
        }

        if (root.nodeType !== Node.ELEMENT_NODE && root.nodeType !== Node.DOCUMENT_NODE) return;
        if (root.nodeType === Node.ELEMENT_NODE) translateElementAttributes(root);

        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT);
        let node = walker.nextNode();
        while (node) {
            if (node.nodeType === Node.TEXT_NODE) {
                const parentTag = node.parentElement?.tagName;
                if (parentTag !== 'SCRIPT' && parentTag !== 'STYLE') translateTextNode(node);
            } else {
                translateElementAttributes(node);
            }
            node = walker.nextNode();
        }
    }

    function updateLanguageControl() {
        document.querySelectorAll('[data-language-option]').forEach((button) => {
            const isActive = button.dataset.languageOption === currentLanguage;
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function applyLanguage() {
        document.documentElement.lang = currentLanguage;
        translateTree(document.body);
        updateLanguageControl();
    }

    function setLanguage(language) {
        currentLanguage = language === 'en' ? 'en' : 'vi';
        localStorage.setItem('license-language', currentLanguage);
        applyLanguage();
        document.dispatchEvent(new CustomEvent('app:languagechange', { detail: { language: currentLanguage } }));
    }

    window.appI18n = {
        t: translate,
        getLanguage: () => currentLanguage,
        setLanguage,
        apply: applyLanguage
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-language-option]').forEach((button) => {
            button.addEventListener('click', () => setLanguage(button.dataset.languageOption));
        });

        applyLanguage();

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => translateTree(node));
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
