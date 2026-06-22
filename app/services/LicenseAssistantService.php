<?php
declare(strict_types=1);

class LicenseAssistantService
{
    private LicenseAssistantModel $model;
    private AssistantIntentResolver $resolver;

    public function __construct(LicenseAssistantModel $model, AssistantIntentResolver $resolver)
    {
        $this->model = $model;
        $this->resolver = $resolver;
    }

    public function answer(string $question, string $language = 'vi', array $context = []): array
    {
        $language = $language === 'en' ? 'en' : 'vi';
        $resolution = $this->resolver->resolve($question, $context);
        $intent = $resolution['intent'];
        $params = $resolution['params'];

        return match ($intent) {
            'active_license_count' => $this->activeLicenseResponse($language),
            'expiring_licenses' => $this->expiringResponse((int)$params['days'], $language),
            'overdue_unrevoked' => $this->overdueResponse($language),
            'top_departments' => $this->topDepartmentsResponse((int)$params['limit'], $language),
            'low_inventory' => $this->lowInventoryResponse((int)$params['threshold'], $language),
            'user_licenses' => $this->userLicensesResponse((string)$params['search'], $language),
            'risk_summary' => $this->riskSummaryResponse($language),
            'help' => $this->welcome($language),
            default => $this->noMatchResponse($language),
        };
    }

    public function welcome(string $language = 'vi'): array
    {
        $english = $language === 'en';

        return $this->response(
            'help',
            $english ? 'LicenseOS Assistant' : 'Trợ lý LicenseOS',
            $english
                ? 'Ask me about active licenses, expiry risks, inventory, departments, or a specific user.'
                : 'Bạn có thể hỏi về license đang hoạt động, nguy cơ hết hạn, tồn kho, khoa hoặc một người dùng cụ thể.',
            'info',
            [],
            [],
            null,
            $this->defaultSuggestions($language)
        );
    }

    private function activeLicenseResponse(string $language): array
    {
        $data = $this->model->getActiveOverview();
        $valid = (int)$data['valid_active'];
        $overdue = (int)$data['overdue_active'];
        $english = $language === 'en';

        return $this->response(
            'active_license_count',
            $english ? 'Active license status' : 'Trạng thái license đang hoạt động',
            $english
                ? "There are {$valid} valid active licenses. {$overdue} additional active records are already overdue and need review."
                : "Hiện có {$valid} license active còn hiệu lực. Ngoài ra có {$overdue} bản ghi vẫn Active nhưng đã quá hạn cần kiểm tra.",
            $overdue > 0 ? 'warning' : 'success',
            [
                ['label' => $english ? 'Valid active' : 'Active hợp lệ', 'value' => $valid],
                ['label' => $english ? 'Overdue active' : 'Active quá hạn', 'value' => $overdue],
            ],
            [],
            ['label' => $english ? 'Open allocations' : 'Mở trang cấp phát', 'url' => app_url('allocations')],
            $this->defaultSuggestions($language)
        );
    }

    private function expiringResponse(int $days, string $language): array
    {
        $data = $this->model->getExpiringLicenses($days);
        $english = $language === 'en';
        $items = [];

        foreach ($data['items'] as $row) {
            $items[] = [
                'primary' => $row['full_name'],
                'secondary' => $row['software_name'] . ' · ' . $row['department_name'],
                'meta' => format_datetime($row['end_date']),
                'badge' => $english ? $row['days_left'] . ' days' : 'Còn ' . $row['days_left'] . ' ngày',
                'tone' => (int)$row['days_left'] <= 7 ? 'warning' : 'info',
            ];
        }

        return $this->response(
            'expiring_licenses',
            $english ? "Expiring within {$days} days" : "License hết hạn trong {$days} ngày",
            $english
                ? "{$data['total']} active licenses will expire within the selected period."
                : "Có {$data['total']} license active sẽ hết hạn trong khoảng thời gian đã chọn.",
            $data['total'] > 0 ? 'warning' : 'success',
            [['label' => $english ? 'Expiring' : 'Sắp hết hạn', 'value' => $data['total']]],
            $items,
            ['label' => $english ? 'Review allocations' : 'Kiểm tra cấp phát', 'url' => app_url('allocations')],
            $english
                ? ['Expiring in 7 days', 'Expiring in 30 days', 'Show overdue licenses']
                : ['Hết hạn trong 7 ngày', 'Hết hạn trong 30 ngày', 'Hiện license quá hạn']
        );
    }

    private function overdueResponse(string $language): array
    {
        $data = $this->model->getOverdueUnrevoked();
        $english = $language === 'en';
        $items = [];

        foreach ($data['items'] as $row) {
            $items[] = [
                'primary' => $row['full_name'],
                'secondary' => $row['software_name'] . ' · ' . $row['department_name'],
                'meta' => $row['email'],
                'badge' => $english ? $row['overdue_days'] . ' days overdue' : 'Quá ' . $row['overdue_days'] . ' ngày',
                'tone' => 'critical',
            ];
        }

        return $this->response(
            'overdue_unrevoked',
            $english ? 'Overdue licenses not revoked' : 'License quá hạn chưa thu hồi',
            $data['total'] > 0
                ? ($english
                    ? "{$data['total']} records are still Active after their end date. They should be reviewed immediately."
                    : "Có {$data['total']} bản ghi vẫn Active sau ngày hết hạn và cần được xử lý ngay.")
                : ($english ? 'No overdue Active licenses were found.' : 'Không phát hiện license Active nào đã quá hạn.'),
            $data['total'] > 0 ? 'critical' : 'success',
            [['label' => $english ? 'Needs review' : 'Cần xử lý', 'value' => $data['total']]],
            $items,
            ['label' => $english ? 'Open revocation workflow' : 'Mở luồng thu hồi', 'url' => app_url('allocations')],
            $this->defaultSuggestions($language)
        );
    }

    private function topDepartmentsResponse(int $limit, string $language): array
    {
        $rows = $this->model->getTopDepartments($limit);
        $english = $language === 'en';
        $items = [];

        foreach ($rows as $index => $row) {
            $items[] = [
                'primary' => ($index + 1) . '. ' . $row['name'],
                'secondary' => $english ? $row['user_count'] . ' users' : $row['user_count'] . ' người dùng',
                'meta' => '',
                'badge' => $row['active_count'] . ' active',
                'tone' => 'info',
            ];
        }

        return $this->response(
            'top_departments',
            $english ? 'Departments with highest usage' : 'Khoa sử dụng license nhiều nhất',
            $english
                ? 'Ranking is calculated from valid Active allocations, not cached statistics.'
                : 'Xếp hạng được tính trực tiếp từ allocation Active còn hiệu lực, không dùng số liệu cache.',
            'info',
            [],
            $items,
            ['label' => $english ? 'Open platform data' : 'Mở dữ liệu nền tảng', 'url' => app_url('admin')],
            $this->defaultSuggestions($language)
        );
    }

    private function lowInventoryResponse(int $threshold, string $language): array
    {
        $rows = $this->model->getLowInventory($threshold);
        $english = $language === 'en';
        $items = [];

        foreach ($rows as $row) {
            $available = (int)$row['available_keys'];
            $items[] = [
                'primary' => $row['name'],
                'secondary' => $row['vendor'] . ' · ' . $row['total_keys'] . ($english ? ' total keys' : ' key tổng'),
                'meta' => '',
                'badge' => $available . ($english ? ' available' : ' còn trống'),
                'tone' => $available === 0 ? 'critical' : 'warning',
            ];
        }

        return $this->response(
            'low_inventory',
            $english ? 'Low license inventory' : 'Kho license sắp hết',
            $english
                ? count($rows) . " software titles have {$threshold} or fewer available keys."
                : 'Có ' . count($rows) . " phần mềm còn tối đa {$threshold} key trống.",
            count($rows) > 0 ? 'warning' : 'success',
            [['label' => $english ? 'Low-stock software' : 'Phần mềm tồn kho thấp', 'value' => count($rows)]],
            $items,
            ['label' => $english ? 'Open inventory' : 'Mở kho license', 'url' => app_url('inventory')],
            $this->defaultSuggestions($language)
        );
    }

    private function userLicensesResponse(string $search, string $language): array
    {
        $english = $language === 'en';
        if ($search === '') {
            return $this->response(
                'user_licenses',
                $english ? 'Who should I look up?' : 'Bạn muốn tìm người dùng nào?',
                $english
                    ? 'Enter a full name or VNU email, for example: licenses for caonhattam497@vnu.edu.vn.'
                    : 'Hãy nhập họ tên hoặc email VNU, ví dụ: license của caonhattam497@vnu.edu.vn.',
                'info',
                [],
                [],
                null,
                $this->defaultSuggestions($language),
                'NoMatch'
            );
        }

        $users = $this->model->findUsers($search);
        if (!$users) {
            return $this->response(
                'user_licenses',
                $english ? 'User not found' : 'Không tìm thấy người dùng',
                $english ? "No user matches \"{$search}\"." : "Không có người dùng phù hợp với \"{$search}\".",
                'warning',
                [],
                [],
                ['label' => $english ? 'Open users' : 'Mở danh sách người dùng', 'url' => app_url('admin')],
                $this->defaultSuggestions($language),
                'NoMatch'
            );
        }

        if (count($users) > 1) {
            $items = array_map(static fn(array $user): array => [
                'primary' => $user['full_name'],
                'secondary' => $user['email'],
                'meta' => $user['department_name'],
                'badge' => role_label($user['role']),
                'tone' => 'info',
                'prompt' => 'License của ' . $user['email'],
            ], $users);

            return $this->response(
                'user_licenses',
                $english ? 'Multiple users found' : 'Tìm thấy nhiều người dùng',
                $english ? 'Select an email to view the correct license history.' : 'Hãy chọn đúng email để xem lịch sử license.',
                'info',
                [],
                $items,
                null,
                $this->defaultSuggestions($language)
            );
        }

        $user = $users[0];
        $licenses = $this->model->getUserLicenses((int)$user['id']);
        $items = [];
        $activeCount = 0;

        foreach ($licenses as $license) {
            $isActive = $license['status'] === 'Active' && strtotime($license['end_date']) >= time();
            $isOverdueActive = $license['status'] === 'Active' && strtotime($license['end_date']) < time();
            if ($isActive) {
                $activeCount++;
            }
            $items[] = [
                'primary' => $license['software_name'],
                'secondary' => $license['vendor'],
                'meta' => format_date($license['start_date']) . ' → ' . format_date($license['end_date']),
                'badge' => $isOverdueActive
                    ? ($english ? 'Overdue active' : 'Quá hạn chưa thu hồi')
                    : ($english ? $license['status'] : status_label($license['status'])),
                'tone' => $isActive ? 'success' : ($license['status'] === 'Revoked' || $isOverdueActive ? 'critical' : 'warning'),
            ];
        }

        return $this->response(
            'user_licenses',
            $user['full_name'],
            $english
                ? $user['email'] . ' · ' . $user['department_name']
                : $user['email'] . ' · ' . $user['department_name'],
            $activeCount > 0 ? 'info' : 'success',
            [
                ['label' => $english ? 'Active licenses' : 'License active', 'value' => $activeCount],
                ['label' => $english ? 'Allocation history' : 'Lịch sử cấp phát', 'value' => count($licenses)],
            ],
            $items,
            ['label' => $english ? 'Open user management' : 'Mở quản lý người dùng', 'url' => app_url('admin')],
            $this->defaultSuggestions($language)
        );
    }

    private function riskSummaryResponse(string $language): array
    {
        $risk = $this->model->getRiskSummary();
        $english = $language === 'en';
        $critical = $risk['overdue'] > 0;
        $warning = $risk['expiring_7_days'] > 0 || $risk['low_inventory'] > 0;

        return $this->response(
            'risk_summary',
            $english ? 'License system health check' : 'Kiểm tra sức khỏe hệ thống',
            $critical
                ? ($english ? 'Immediate action is required for overdue Active licenses.' : 'Cần xử lý ngay các license đã quá hạn nhưng vẫn Active.')
                : ($warning
                    ? ($english ? 'The system is stable, with several items requiring attention.' : 'Hệ thống ổn định nhưng có một số mục cần chú ý.')
                    : ($english ? 'No urgent license risks were detected.' : 'Không phát hiện rủi ro license khẩn cấp.')),
            $critical ? 'critical' : ($warning ? 'warning' : 'success'),
            [
                ['label' => $english ? 'Overdue active' : 'Active quá hạn', 'value' => $risk['overdue']],
                ['label' => $english ? 'Expire in 7 days' : 'Hết hạn trong 7 ngày', 'value' => $risk['expiring_7_days']],
                ['label' => $english ? 'Low inventory' : 'Tồn kho thấp', 'value' => $risk['low_inventory']],
            ],
            [],
            ['label' => $english ? 'Open dashboard' : 'Mở Dashboard', 'url' => app_url('dashboard')],
            $this->defaultSuggestions($language)
        );
    }

    private function noMatchResponse(string $language): array
    {
        $english = $language === 'en';

        return $this->response(
            'no_match',
            $english ? 'I could not identify that request' : 'Mình chưa hiểu yêu cầu này',
            $english
                ? 'Try one of the suggested questions. I only run approved, read-only license queries.'
                : 'Hãy thử một câu hỏi gợi ý. Trợ lý chỉ thực hiện các truy vấn license đọc đã được kiểm soát.',
            'info',
            [],
            [],
            null,
            $this->defaultSuggestions($language),
            'NoMatch'
        );
    }

    private function defaultSuggestions(string $language): array
    {
        return $language === 'en'
            ? ['How many licenses are active?', 'Show overdue licenses', 'Which software is low on keys?', 'System health check']
            : ['Có bao nhiêu license đang active?', 'Hiện license quá hạn', 'Phần mềm nào sắp hết key?', 'Kiểm tra rủi ro hệ thống'];
    }

    private function response(
        string $intent,
        string $title,
        string $summary,
        string $severity,
        array $metrics,
        array $items,
        ?array $action,
        array $suggestions,
        string $status = 'Success'
    ): array {
        return [
            'intent' => $intent,
            'title' => $title,
            'summary' => $summary,
            'severity' => $severity,
            'metrics' => $metrics,
            'items' => $items,
            'action' => $action,
            'suggestions' => $suggestions,
            'status' => $status,
        ];
    }
}
