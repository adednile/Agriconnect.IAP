<?php
namespace App\Controllers;

use App\Models\Advisory;

class AdvisoryController {
    private $model;
    public function __construct(){
        $this->model = new Advisory();
    }

    public function listForFarmers(){
        session_start();
        $advisories = $this->model->getForFarmers();
        require_once __DIR__ . '/../../views/farmer/advisories.php';
    }

    public function postAdvisory($data){
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'agronomist') return ['error'=>'Unauthorized'];
        $author = $_SESSION['user_id'];
        $title = $data['title'] ?? '';
        $body = $data['body'] ?? '';
        $audience = $data['audience'] ?? 'farmers';
        $ok = $this->model->create($author, $title, $body, $audience);
        return ['success' => (bool)$ok];
    }
}
