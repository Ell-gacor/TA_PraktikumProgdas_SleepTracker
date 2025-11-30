<?php
class SleepTracker {
    public $stack = [];     
    public $queue = [];      
    public $notes = [];       
    public $achievements = [];
    private $dataFile = "data/users.json";
    private $username = "";

    // Constructor dengan username
    public function __construct($username = "") {
        $this->username = $username;
        if(!is_dir("data")) mkdir("data", 0755, true);
        $this->loadFromJSON();
    }

    // Load data dari JSON
    private function loadFromJSON() {
        if(!file_exists($this->dataFile)) return;
        
        $data = json_decode(file_get_contents($this->dataFile), true);
        
        if($data && isset($data[$this->username])) {
            $userData = $data[$this->username];
            $this->stack = $userData['stack'] ?? [];
            $this->notes = $userData['notes'] ?? [];
            $this->queue = $userData['queue'] ?? [];
        }
    }

    // Save data ke JSON
    public function saveToJSON() {
        $data = [];
        if(file_exists($this->dataFile)) {
            $data = json_decode(file_get_contents($this->dataFile), true);
            if(!is_array($data)) $data = [];
        }
        
        // Pertahankan field password dan created_at jika sudah ada
        $existingUserData = $data[$this->username] ?? [];
        
        $data[$this->username] = [
            'password' => $existingUserData['password'] ?? '',
            'created_at' => $existingUserData['created_at'] ?? date('Y-m-d H:i:s'),
            'stack' => $this->stack,
            'notes' => $this->notes,
            'queue' => $this->queue
        ];
        
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } 

    // enqueue input baru
    public function enqueue($sleepData, $note = "") {
        $this->queue[] = $sleepData;
        $this->notes[] = $note;
    }

    // proses queue → stack
    public function processQueue() {
        while(!empty($this->queue)) {
            $data = array_shift($this->queue);
            $this->stack[] = $data;
        }
        $this->saveToJSON();
    }

    // ambil semua data
    public function getAll() {
        return $this->stack;
    }

    // rata-rata tidur
    public function getAverage() {
        if(count($this->stack)==0) return 0;
        $total = 0;
        foreach($this->stack as $item) $total += $item["duration"];
        return round($total / count($this->stack),1);
    }

    // tips tidur
  public function getTip($hours) {
    $result = [];

    // Kasus tidak tidur sama sekali
    if($hours <= 0){
        $result['pesan'] = "Kamu sama sekali tidak tidur. Secara fisiologis ini sangat membebani sistem saraf dan hormonal. Ayo istirahat dulu.";
        $result['jurnal'] = [
            [
                "judul" => "Konsekuensi neurokognitif akibat kurang tidur total",
                "deskripsi" => "Kajian ilmiah mengenai penurunan fungsi prefrontal cortex, perhatian, dan memori kerja akibat tidak tidur."
            ],
            [
                "judul" => "Efek deprivasi tidur akut terhadap fungsi tubuh",
                "deskripsi" => "Analisis dampak deprivasi tidur terhadap sistem imun, metabolisme glukosa, dan tekanan darah."
            ],
            [
                "judul" => "Risiko kesehatan pada individu dengan tidur nol jam",
                "deskripsi" => "Pembahasan mengenai peningkatan risiko aritmia, perubahan hormon stres, dan gangguan mood."
            ]
        ];
    }

    // Sangat kurang tidur: 1–4 jam
    elseif($hours > 0 && $hours < 5){
        $result['pesan'] = "Tidur kamu sangat kurang. Tubuh belum mencapai fase restoratif penuh. Coba prioritaskan tidur malam.";
        $result['jurnal'] = [
            [
                "judul" => "Hubungan antara tidur singkat dan penurunan kinerja kognitif",
                "deskripsi" => "Menjelaskan bagaimana durasi tidur <5 jam berkorelasi dengan penurunan fokus dan kemampuan pengambilan keputusan."
            ],
            [
                "judul" => "Pengaruh durasi tidur pendek terhadap metabolisme tubuh",
                "deskripsi" => "Studi ilmiah mengenai resistensi insulin dan perubahan hormon lapar (ghrelin-leptin)."
            ],
            [
                "judul" => "Keterkaitan tidur singkat dengan kondisi psikologis",
                "deskripsi" => "Risiko peningkatan kecemasan, iritabilitas, dan stres fisiologis akibat kurang tidur."
            ]
        ];
    }

    // Kurang tidur sedang: 5–6 jam
    elseif($hours >= 5 && $hours < 7){
        $result['pesan'] = "Tidur kamu masih kurang optimal, tubuh belum mendapatkan manfaat penuh dari fase tidur dalam (deep sleep).";
        $result['jurnal'] = [
            [
                "judul" => "Efek fisiologis durasi tidur sedang",
                "deskripsi" => "Menjelaskan bagaimana tidur 5–6 jam dapat mengganggu konsolidasi memori dan proses pemulihan otot."
            ],
            [
                "judul" => "Hubungan pola tidur tidak optimal dengan produktivitas",
                "deskripsi" => "Analisis ilmiah mengenai penurunan performa kerja dan akademik akibat kurang tidur kronis."
            ],
            [
                "judul" => "Pengaruh tidur terbatas terhadap regulasi emosi",
                "deskripsi" => "Kaitan antara tidur singkat dan aktivitas berlebih di amygdala yang memicu ketidakstabilan emosi."
            ]
        ];
    }

    // Tidur ideal: 7–9 jam
    elseif($hours >= 7 && $hours <= 9){
        $result['pesan'] = "Tidur kamu sudah berada dalam rentang ideal, tubuh kamu sedang bekerja optimal.";
        $result['jurnal'] = [
            [
                "judul" => "Manfaat fisiologis tidur 7–9 jam",
                "deskripsi" => "Kajian komprehensif mengenai pemulihan jaringan, regulasi hormon, dan peningkatan fungsi imun."
            ],
            [
                "judul" => "Korelasi antara tidur cukup dan kesehatan mental",
                "deskripsi" => "Studi ilmiah mengenai peran tidur terhadap stabilitas mood, stres, dan kesehatan emosional."
            ],
            [
                "judul" => "Hubungan tidur optimal dengan performa akademik",
                "deskripsi" => "Bagaimana tidur cukup meningkatkan memori jangka panjang dan kemampuan analitis."
            ]
        ];
    }

    // Tidur berlebih: 10+ jam
    else { // > 9 jam
        $result['pesan'] = "Kamu tidur terlalu lama, tidur berlebih dapat menjadi indikator kelelahan ekstrem atau kondisi metabolik tertentu, Hati-hati.";
        $result['jurnal'] = [
            [
                "judul" => "Implikasi klinis dari tidur berlebih",
                "deskripsi" => "Analisis dampak tidur >10 jam terhadap kesehatan metabolik dan fungsi kardiovaskular."
            ],
            [
                "judul" => "Oversleeping dan gangguan ritme sirkadian",
                "deskripsi" => "Pembahasan ilmiah mengenai bagaimana tidur berlebih menghambat ritme biologis harian."
            ],
            [
                "judul" => "Hubungan durasi tidur panjang dan risiko inflamasi",
                "deskripsi" => "Studi mengenai peningkatan indikator inflamasi sistemik (CRP, IL-6) pada individu yang tidur terlalu lama."
            ]
        ];
    }

    return $result;
}



    

    // status tidur
    public function getSleepStatus($hours) {
        if($hours >=7) return "Bagus";
        elseif($hours >=5) return "Cukup";
        return "Kurang";
    }

    // leaderboard
    public function getLeaderboard() {
        $data = $this->stack;
        usort($data,function($a,$b){ return $b["duration"] - $a["duration"]; });
        return $data;
    }

    // achievements
    public function checkAchievements() {
        $avg = $this->getAverage();
        $count = count($this->stack);
        $this->achievements = [];
        if($avg >=7) $this->achievements[] = "Mahasiswa Normal";
        if($count >=3) $this->achievements[] = "Mahasiswa Santuy";
        if($count >=7) $this->achievements[] = "Mahasigma";
        return $this->achievements;
    }

    // weekly summary
    public function getWeeklySummary() {
        $weeklySummary = [];
        
        foreach($this->stack as $item) {
            $date = new DateTime($item['date']);
            $weekStart = clone $date;
            $weekStart->modify('monday this week');
            $weekEnd = clone $weekStart;
            $weekEnd->modify('+6 days');
            
            $weekKey = $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d');
            
            if(!isset($weeklySummary[$weekKey])) {
                $weeklySummary[$weekKey] = [
                    'weekStart' => $weekStart->format('Y-m-d'),
                    'weekEnd' => $weekEnd->format('Y-m-d'),
                    'totalDuration' => 0,
                    'count' => 0,
                    'data' => []
                ];
            }
            
            $weeklySummary[$weekKey]['totalDuration'] += $item['duration'];
            $weeklySummary[$weekKey]['count'] += 1;
            $weeklySummary[$weekKey]['data'][] = $item;
        }
        
        // Hitung average per minggu
        foreach($weeklySummary as &$week) {
            $week['average'] = $week['count'] > 0 ? round($week['totalDuration'] / $week['count'], 1) : 0;
            $week['maxSleep'] = $week['count'] > 0 ? max(array_column($week['data'], 'duration')) : 0;
            $week['minSleep'] = $week['count'] > 0 ? min(array_column($week['data'], 'duration')) : 0;
        }
        
        // Sort by week (newest first)
        krsort($weeklySummary);
        
        return $weeklySummary;
    }

    // get status untuk weekly avg
    public function getWeeklyStatus($avg) {
        if($avg >= 7) return "Bagus";
        elseif($avg >= 5) return "Cukup";
        return "Kurang";
    }
}

