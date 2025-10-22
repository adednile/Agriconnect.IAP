<?php
namespace App\Controllers;

use App\Models\Driver;

class DriverController {
    private $model;
    public function __construct(){
        $this->model = new Driver();
    }

    public function listDrivers(){
        session_start();
        $drivers = $this->model->getAll();
        require_once __DIR__ . '/../../views/drivers/list.php';
    }

    public function createDriver($data){
        // admin only
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') return ['error'=>'Unauthorized'];
        $ok = $this->model->create($data['name'],$data['phone'] ?? null,$data['vehicle'] ?? null);
        return ['success' => (bool)$ok];
    }
}
