<?php
declare(strict_types=1);

class AllocationRuleController
{
    private $model;
    private $audit;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->model = new AllocationRuleModel($db);
        $this->audit = new AuditLogModel($db);
    }

    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        $rules = $this->model->getAllRules();
        $softwares = $this->model->getSoftwares();
        $departments = $this->model->getDepartments();
        $suggestionsByDepartment = [];

        foreach ($departments as $department) {
            $suggestionsByDepartment[$department['id']] = $this->model->suggestSoftwareForDepartment($department['name']);
        }

        require BASE_PATH . '/app/views/allocation_rules_view.php';
    }

    private function handlePost(): void
    {
        try {
            require_csrf();
            $action = $_POST['action'] ?? '';

            if ($action === 'add') {
                $id = $this->model->addRule(
                    positive_int($_POST['software_id'] ?? null, 'Phần mềm'),
                    positive_int($_POST['department_id'] ?? null, 'Khoa/phòng ban'),
                    $_POST['target_role'] ?? ''
                );
                $this->audit->record('create_allocation_rule', 'allocation_rule', $id, [
                    'software_id' => $_POST['software_id'] ?? null,
                    'department_id' => $_POST['department_id'] ?? null,
                    'target_role' => $_POST['target_role'] ?? null,
                ]);
                redirect_with_flash('rules', 'success', 'Đã thêm luật cấp phát mới.');
            }

            if ($action === 'delete') {
                $id = positive_int($_POST['id'] ?? null, 'ID luật');
                $this->model->deleteRule($id);
                $this->audit->record('delete_allocation_rule', 'allocation_rule', $id);
                redirect_with_flash('rules', 'success', 'Đã xóa luật cấp phát.');
            }

            redirect_with_flash('rules', 'error', 'Thao tác không hợp lệ.');
        } catch (Throwable $exception) {
            redirect_with_flash('rules', 'error', $exception->getMessage());
        }
    }
}
