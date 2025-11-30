<?php
class Auth {
    private $dataFile = "data/users.json";
    private $defaultUsers = [
        "ell" => "cadeltampan",
        "admin" => "12345",
        "admin1" => "12345"
    ];

    public function __construct() {
        if(!is_dir("data")) mkdir("data", 0755, true);
        
        // Initialize JSON jika belum ada
        if(!file_exists($this->dataFile)) {
            $initialData = [];
            foreach($this->defaultUsers as $user => $pass) {
                $initialData[$user] = [
                    'password' => $pass,
                    'created_at' => date('Y-m-d H:i:s'),
                    'stack' => [],
                    'notes' => [],
                    'queue' => []
                ];
            }
            file_put_contents($this->dataFile, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    public function login($username, $password) {
        $data = json_decode(file_get_contents($this->dataFile), true);
        return isset($data[$username]) && isset($data[$username]['password']) && $data[$username]['password'] === $password;
    }

    public function register($username, $password) {
        $data = json_decode(file_get_contents($this->dataFile), true);
        
        if(isset($data[$username])) return false; // User sudah ada
        
        $data[$username] = [
            'password' => $password,
            'created_at' => date('Y-m-d H:i:s'),
            'stack' => [],
            'notes' => [],
            'queue' => []
        ];
        
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }

    public function userExists($username) {
        $data = json_decode(file_get_contents($this->dataFile), true);
        return isset($data[$username]);
    }
}
