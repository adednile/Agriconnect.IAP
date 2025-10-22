<?php
require_once __DIR__ . '/../models/Farmer.php';

class ProfileController {

    public function updateProfile($data, $files) {
        $farmer = new Farmer();

        $result = $farmer->update(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['location']
        );

        if ($result) {
            header("Location: /views/farmer/profile.php?success=1");
            exit;
        } else {
            header("Location: /views/farmer/profile.php?error=1");
            exit;
        }
    }
}
?>
