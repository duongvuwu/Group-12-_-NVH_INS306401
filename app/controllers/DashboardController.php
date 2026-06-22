<?php
declare(strict_types=1);

class DashboardController
{
    private DashboardModel $model;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->model = new DashboardModel($db);
    }

    public function index(): void
    {
        $stats = $this->model->getStats();
        $inventoryChart = $this->model->getInventoryBySoftware();
        $departmentUsage = $this->model->getDepartmentUsage();
        $expiringAllocations = $this->model->getExpiringAllocations(14);
        $topSoftware = $this->model->getTopSoftware();
        $topDepartments = $this->model->getTopDepartments();
        $unusedSoftware = $this->model->getUnusedSoftware();

        require BASE_PATH . '/app/views/dashboard_view.php';
    }
}
