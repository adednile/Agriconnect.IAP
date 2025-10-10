<?php
namespace App\Controllers;

use App\Models\User;
use Exception;

use App\Config\Database;
use App\Config\MailService;
use PDO;

require_once __DIR__ . '/../../vendor/autoload.php';

class AuthController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Register user and send OTP
    public function register($name, $email, $password, $role) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16));
        $otp = rand(100000, 999999);
        $otpExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Check duplicate
        $stmt = $this->conn->prepare("SELECT id, is_verified FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['is_verified']) {
                return ["success"=>false,"msg"=>"Email already registered and verified."];
            } else {
                return ["success"=>false,"msg"=>"Email already registered but not verified. Resend OTP?"];
            }
        }

        $stmt = $this->conn->prepare("
            INSERT INTO users (name,email,password,role,verify_token,otp_code,otp_expires_at,is_verified)
            VALUES (?,?,?,?,?,?,?,0)
        ");
        $stmt->execute([$name,$email,$hashed,$role,$token,$otp,$otpExpires]);

        $mail = new MailService();
        $verifyLink = $_ENV['APP_URL'] . "views/auth/verify.php?token=".$token;
        $body = "
            <h3>Welcome $name!</h3>
            <p>Click to verify:</p>
            <a href='$verifyLink'>$verifyLink</a>
            <p>Or use this OTP:</p>
            <h2>$otp</h2>
            <small>Expires in 10 minutes</small>
        ";

        if ($mail->send($email, "Verify Your AgriMarket Account", $body)) {
            return ["success"=>true,"msg"=>"Registration successful! Verification email sent."];
        }
        return ["success"=>true,"msg"=>"Registration saved, but failed to send email."];
    }

    // Verify OTP
    public function verifyOTP($email, $otp) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=? AND otp_code=?");
        $stmt->execute([$email,$otp]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && strtotime($user['otp_expires_at']) > time()) {
            $update = $this->conn->prepare("UPDATE users SET is_verified=1, otp_code=NULL, otp_expires_at=NULL WHERE email=?");
            $update->execute([$email]);
            return true;
        }
        return false;
    }

    // Resend OTP
    public function resendOTP($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user) return ["success"=>false,"msg"=>"Email not found."];
        if($user['is_verified']) return ["success"=>false,"msg"=>"Account already verified."];

        $otp = rand(100000, 999999);
        $otpExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $update = $this->conn->prepare("UPDATE users SET otp_code=?, otp_expires_at=? WHERE email=?");
        $update->execute([$otp,$otpExpires,$email]);

        $mail = new MailService();
        $body = "
            <h3>Hello {$user['name']}</h3>
            <p>Your new OTP:</p>
            <h2>$otp</h2>
            <small>Expires in 10 minutes.</small>
        ";
        $sent = $mail->send($email,"Your new AgriMarket OTP",$body);
        if($sent) return ["success"=>true,"msg"=>"New OTP sent to your email."];
        return ["success"=>false,"msg"=>"Failed to send OTP."];
    }
    public function login($email, $password) {
    $db = new \App\Config\Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return 'invalid';
    }

    if ($user['is_verified'] == 0) {
        return 'not_verified';
    }

    return [
        'id' => $user['id'],
        'name' => $user['name'],
        'role' => $user['role']
    ];
}

}
