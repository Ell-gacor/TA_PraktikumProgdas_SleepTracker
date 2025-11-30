<?php
require "SleepTracker.php";
session_start();

// cek login
if (!isset($_SESSION["login"])) header("Location: index.php");

$username = $_SESSION["user"];
$tracker = new SleepTracker($username);
$_SESSION["tracker"] = $tracker;
$message = "";

// input data tidur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["duration"])) {
    $duration = (int)$_POST["duration"];
    $note = $_POST["note"] ?? "";

    $tracker->enqueue(["duration" => $duration, "date" => date("Y-m-d")], $note);
    $tracker->processQueue();
    $message = "Data tidur berhasil ditambahkan!";
}

// Cari foto profil
$profilePic = "assets/default-avatar.png";
$dataDir = "data/profiles";
if(is_dir($dataDir)) {
    foreach(['jpg', 'jpeg', 'png', 'gif'] as $ext) {
        $path = "$dataDir/$username.$ext";
        if(file_exists($path)) {
            $profilePic = $path . "?" . time();
            break;
        }
    }
}

$feature = $_GET["feature"] ?? "";
$avg = $tracker->getAverage();
$leaderboard = $tracker->getLeaderboard();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Sleep Tracker</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        
        <!-- LEFT PANEL -->
        <div class="dashboard-left">
            <!-- Header dengan Profil -->
            <div class="dashboard-header">
                <div class="dashboard-profile">
                    <a href="profile.php" class="profile-pic-wrapper">
                        <img src="<?= $profilePic ?>" alt="Profile" class="dashboard-profile-pic">
                    </a>
                    <div class="dashboard-greeting">
                        <p class="greeting-text">Selamat Datang</p>
                        <h2 class="greeting-name"><?= htmlspecialchars($username) ?></h2>
                    </div>
                </div>
            </div>

            <!-- Feature Navigation -->
            <div class="feature-nav">
                <h3>Pilih Fitur</h3>
                <nav class="feature-links">
                    <a href="?feature=average" class="feature-link" data-feature="average">📊 Rata-rata Tidur</a>
                    <a href="?feature=weekly" class="feature-link" data-feature="weekly">📈 Summary Mingguan</a>
                    <a href="?feature=tips" class="feature-link" data-feature="tips">💡 Tips Tidur</a>
                    <a href="?feature=notes" class="feature-link" data-feature="notes">📝 Sleep Notes</a>
                    <a href="?feature=leaderboard" class="feature-link" data-feature="leaderboard">🏆 Leaderboard</a>
                </nav>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="dashboard-right">
            <!-- Input Form -->
            <div class="input-card">
                <h3>Catat Tidurmu</h3>
                <form method="POST" class="sleep-input-form">
                    <div class="form-group">
                        <label>Jam tidur (jam):</label>
                        <input type="number" name="duration" required step="0.5" min="0">
                    </div>
                    <div class="form-group">
                        <label>Sleep Note:</label>
                        <input type="text" name="note" placeholder="Contoh: tidur larut nonton film">
                    </div>
                    <button type="submit" class="submit-btn">Tambah</button>
                </form>
                <?php if ($message) echo "<p class='success-msg'>$message</p>"; ?>
            </div>

            <!-- Content Area -->
            <div class="content-card">
                <?php
                switch ($feature) {

                    // average
                    case "average":
                ?>
                        <h3 class="title-elegant">Rata-rata Durasi Tidur</h3>
                        <div class="feature-stat-box">
                            <div class="stat-main-value"><?= number_format($tracker->getAverage(), 1) ?></div>
                            <div class="stat-main-label">jam per hari</div>
                            <div class="stat-description">
                                <?php
                                $avg = $tracker->getAverage();
                                if ($avg >= 7 && $avg <= 9) {
                                    echo "✨ Tidur Sempurna! Pertahankan kebiasaan ini.";
                                } elseif ($avg > 9) {
                                    echo "😴 Sedikit berlebihan, coba kurangi untuk produktivitas maksimal.";
                                } else {
                                    echo "⚠️ Kurang tidur, tingkatkan jam istirahat Anda.";
                                }
                                ?>
                            </div>
                        </div>
                    <?php
                        break;

                        // weekly summary
                    case "weekly":
                        echo "<h3 class='title-elegant'>Summary Mingguan</h3>";
                        
                        $weeklySummary = $tracker->getWeeklySummary();
                        
                        if(empty($weeklySummary)) {
                            echo "<div class='empty-state'><p>📈 Belum ada data mingguan, coba catat tidur dulu yaa.</p></div>";
                            break;
                        }
                        
                        foreach($weeklySummary as $weekKey => $week) {
                            $status = $tracker->getWeeklyStatus($week['average']);
                            $statusColor = $status == "Bagus" ? "#4CAF50" : ($status == "Cukup" ? "#FFC107" : "#F44336");
                            
                            echo "<div class='weekly-card'>";
                            
                            echo "<div class='weekly-header'>";
                            echo "<h4 class='weekly-date'>".$week['weekStart']." - ".$week['weekEnd']."</h4>";
                            echo "<span class='weekly-status' style='background:$statusColor;'>$status</span>";
                            echo "</div>";
                            
                            echo "<div class='weekly-stats'>";
                            
                            echo "<div class='weekly-stat'>";
                            echo "<p class='weekly-stat-label'>Rata-rata</p>";
                            echo "<p class='weekly-stat-value'>".$week['average']." jam</p>";
                            echo "</div>";
                            
                            echo "<div class='weekly-stat'>";
                            echo "<p class='weekly-stat-label'>Total Hari</p>";
                            echo "<p class='weekly-stat-value'>".$week['count']." hari</p>";
                            echo "</div>";
                            
                            echo "<div class='weekly-stat'>";
                            echo "<p class='weekly-stat-label'>Max</p>";
                            echo "<p class='weekly-stat-value'>".$week['maxSleep']." jam</p>";
                            echo "</div>";
                            
                            echo "<div class='weekly-stat'>";
                            echo "<p class='weekly-stat-label'>Min</p>";
                            echo "<p class='weekly-stat-value'>".$week['minSleep']." jam</p>";
                            echo "</div>";
                            
                            echo "</div>";
                            echo "</div>";
                        }
                    break;

                    // tips tidur
                    case "tips":
                        echo "<h3 class='title-elegant'>💡 Tips Tidur</h3>";

                        $allData = $tracker->getAll();
                        if(empty($allData)) {
                            echo "<div class='empty-state'><p>Belum ada data tidur untuk mendapatkan tips.</p></div>";
                            break;
                        }

                        foreach ($allData as $item) {
                            $tip = $tracker->getTip($item["duration"]);

                            echo "<div class='tip-card'>";

                            echo "<div class='tip-header'>";
                            echo "<span class='tip-date'>".$item['date']."</span>";
                            echo "<span class='tip-duration'>".$item['duration']." jam</span>";
                            echo "</div>";
                            
                            echo "<p class='tip-message'>".$tip["pesan"]."</p>";

                            echo "<div class='tip-journal'>";
                            foreach ($tip["jurnal"] as $j) {
                                echo "<div class='journal-item'>";
                                echo "<b class='journal-title'>".$j['judul']."</b>";
                                echo "<p class='journal-desc'>".$j['deskripsi']."</p>";
                                echo "</div>";
                            }
                            echo "</div>";

                            echo "</div>";
                        }
                        break;

                    // sleep notes
                    case "notes":
                        echo "<h3 class='title-elegant'>Sleep Notes</h3>";

                        if (empty($tracker->notes)) {
                            echo "<div class='empty-state'><p>📝 Belum ada catatan tidur, coba tulis satu dulu ya.</p></div>";
                        } else {
                            echo "<div class='notes-container'>";
                            foreach ($tracker->notes as $i => $note) {
                                $date = $tracker->stack[$i]['date'] ?? date('Y-m-d');
                                $duration = $tracker->stack[$i]['duration'] ?? 0;
                                echo "
                                <div class='note-card'>
                                    <div class='note-header'>
                                        <span class='note-date'>📅 $date</span>
                                        <span class='note-duration'>😴 $duration jam</span>
                                    </div>
                                    <div class='note-content'>$note</div>
                                </div>
                                ";
                            }
                            echo "</div>";
                        }
                    break;

                    // leaderboard
                    case "leaderboard":
                        echo "<h3 class='title-elegant'>Leaderboard Tidur Terbaik</h3>";

                        $data = $tracker->getLeaderboard();

                        if (empty($data)) {
                            echo "<div class='empty-state'><p>🏆 Belum ada data leaderboard, coba catat tidur dulu yaa.</p></div>";
                            break;
                        }

                        echo "<div class='leaderboard-container'>";
                        $rank = 1;
                        foreach ($data as $item) {
                            $medals = ['🥇', '🥈', '🥉'];
                            $medal = $medals[$rank - 1] ?? '⭐';
                            $bgColor = $rank == 1 ? '#FFF8DC' : ($rank == 2 ? '#F5F5F5' : '#FFFBF8');
                            
                            echo "
                            <div class='leaderboard-item' style='background-color: $bgColor;'>
                                <div class='leaderboard-rank'>$medal</div>
                                <div class='leaderboard-info'>
                                    <div class='leaderboard-date'>".$item['date']."</div>
                                    <div class='leaderboard-duration'>".$item['duration']." jam tidur</div>
                                </div>
                            </div>
                            ";
                            $rank++;
                        }
                        echo "</div>";
                    break;
                   
                    // default
                    default:
                        echo "<h3 class='title-elegant'>Selamat Datang di Dashboard</h3>";
                        echo "<p style='font-size:14px; color:#e1e1e1; line-height:1.6;'>";
                        echo "Pilih fitur di sebelah kiri untuk melihat data tidurmu. Atau mulai catat tidurmu sekarang!<br><br>";
                        echo "📊 <strong>Rata-rata Tidur</strong> - Lihat rata-rata durasi tidurmu<br>";
                        echo "📈 <strong>Summary Mingguan</strong> - Analisis pola tidur mingguan<br>";
                        echo "💡 <strong>Tips Tidur</strong> - Dapatkan rekomendasi berdasarkan tidurmu<br>";
                        echo "📝 <strong>Sleep Notes</strong> - Catatan pribadi tentang kualitas tidurmu<br>";
                        echo "🏆 <strong>Leaderboard</strong> - Lihat rekor tidur terbaik";
                        echo "</p>";
                }
                ?>
            </div>


        </div>

    </div>

    <style>
        .content-card {
            overflow-y: auto;
            padding-right: 8px;
        }

        .content-card::-webkit-scrollbar {
            width: 6px;
        }

        .content-card::-webkit-scrollbar-track {
            background: #1a2847;
            border-radius: 10px;
        }

        .content-card::-webkit-scrollbar-thumb {
            background: #5E6C8C;
            border-radius: 10px;
        }

        .content-card::-webkit-scrollbar-thumb:hover {
            background: #8D99AE;
        }

        /* Feature Styling */
        .feature-stat-box {
            background: linear-gradient(135deg, #283B73 0%, #1a2847 100%);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            margin: 8px 0;
            border: 1px solid #5E6C8C;
        }

        .stat-main-value {
            font-size: 48px;
            font-weight: 900;
            color: #F8F9FA;
            margin-bottom: 4px;
        }

        .stat-main-label {
            font-size: 13px;
            color: #8D99AE;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-description {
            font-size: 12px;
            color: #8D99AE;
            font-weight: 500;
            line-height: 1.4;
        }

        /* Weekly Summary Styling */
        .weekly-card {
            background: #1a2847;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border: 1px solid #5E6C8C;
        }

        .weekly-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #5E6C8C;
        }

        .weekly-date {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            color: #F8F9FA;
        }

        .weekly-status {
            color: #0E1A40;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            background: #8D99AE;
        }

        .weekly-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .weekly-stat {
            background: #283B73;
            padding: 6px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #5E6C8C;
        }

        .weekly-stat-label {
            margin: 0;
            font-size: 10px;
            color: #8D99AE;
            font-weight: 600;
        }

        .weekly-stat-value {
            margin: 2px 0 0 0;
            font-size: 12px;
            font-weight: bold;
            color: #F8F9FA;
        }

        /* Tips Styling */
        .tip-card {
            background: #1a2847;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 8px;
            border-left: 3px solid #8D99AE;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border: 1px solid #5E6C8C;
        }

        .tip-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #5E6C8C;
        }

        .tip-date {
            font-size: 11px;
            font-weight: 600;
            color: #8D99AE;
        }

        .tip-duration {
            font-size: 11px;
            font-weight: 600;
            color: #F8F9FA;
            background: #5E6C8C;
            padding: 1px 6px;
            border-radius: 8px;
        }

        .tip-message {
            margin: 6px 0;
            font-size: 12px;
            color: #F8F9FA;
            line-height: 1.3;
        }

        .tip-journal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .journal-item {
            background: #283B73;
            padding: 6px;
            border-radius: 6px;
            border-left: 2px solid #8D99AE;
            border: 1px solid #5E6C8C;
        }

        .journal-title {
            font-size: 10px;
            color: #8D99AE;
            display: block;
            margin-bottom: 2px;
            font-weight: 600;
        }

        .journal-desc {
            font-size: 10px;
            color: #8D99AE;
            margin: 0;
            line-height: 1.2;
        }

        /* Notes Styling */
        .notes-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .note-card {
            background: #1a2847;
            border-radius: 10px;
            padding: 12px;
            border-left: 3px solid #8D99AE;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            border: 1px solid #5E6C8C;
        }

        .note-card:hover {
            box-shadow: 0 6px 18px rgba(141, 153, 174, 0.3);
            transform: translateY(-1px);
            border-color: #8D99AE;
        }

        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #5E6C8C;
        }

        .note-date {
            font-size: 11px;
            font-weight: 600;
            color: #8D99AE;
        }

        .note-duration {
            font-size: 11px;
            font-weight: 600;
            color: #F8F9FA;
            background: #5E6C8C;
            padding: 2px 6px;
            border-radius: 8px;
        }

        .note-content {
            font-size: 12px;
            color: #F8F9FA;
            line-height: 1.4;
        }

        /* Leaderboard Styling */
        .leaderboard-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #5E6C8C;
            background: #1a2847;
            transition: all 0.3s ease;
        }

        .leaderboard-item:hover {
            border-color: #8D99AE;
            box-shadow: 0 4px 12px rgba(141, 153, 174, 0.3);
        }

        .leaderboard-rank {
            font-size: 22px;
            min-width: 32px;
            text-align: center;
        }

        .leaderboard-info {
            flex: 1;
        }

        .leaderboard-date {
            font-size: 11px;
            font-weight: 600;
            color: #000000ff;
            margin-bottom: 1px;
        }

        .leaderboard-duration {
            font-size: 12px;
            font-weight: 700;
            color: #000000ff;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 20px 10px;
            color: #1a2847;
            font-size: 13px;
        }

        .empty-state p {
            margin: 0;
        }

        /* Title */
        .title-elegant {
            font-size: 15px;
            font-weight: 700;
            color: #F8F9FA;
            margin: 8px 0 8px 0;
        }
    </style>
</body>
</html>
