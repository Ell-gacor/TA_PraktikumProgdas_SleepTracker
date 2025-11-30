<?php
require "SleepTracker.php";
session_start();

// cek login
if (!isset($_SESSION["login"])) header("Location: index.php");

$username = $_SESSION["user"];
$tracker = new SleepTracker($username);
$message = "";
$messageType = ""; // success atau error

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_pic"])) {
    $file = $_FILES["profile_pic"];
    $dataDir = "data/profiles";
    
    // Buat folder jika belum ada
    if(!is_dir($dataDir)) mkdir($dataDir, 0755, true);
    
    // Validasi file
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if(!in_array($ext, $allowed)) {
        $message = "Format file harus JPG, PNG, atau GIF!";
        $messageType = "error";
    } else if($file['size'] > 5 * 1024 * 1024) { // 5MB
        $message = "Ukuran file maksimal 5MB!";
        $messageType = "error";
    } else {
        // Hapus foto lama jika ada
        foreach(['jpg', 'jpeg', 'png', 'gif'] as $oldExt) {
            $oldPath = "$dataDir/$username.$oldExt";
            if(file_exists($oldPath)) unlink($oldPath);
        }
        
        // Upload foto baru
        $newFilename = "$dataDir/$username.$ext";
        if(move_uploaded_file($file['tmp_name'], $newFilename)) {
            $message = "Foto profil berhasil diupdate!";
            $messageType = "success";
        } else {
            $message = "Gagal mengupload foto!";
            $messageType = "error";
        }
    }
}

// Cari foto profil
$profilePic = "assets/default-avatar.png"; // Default avatar
$dataDir = "data/profiles";
if(is_dir($dataDir)) {
    foreach(['jpg', 'jpeg', 'png', 'gif'] as $ext) {
        $path = "$dataDir/$username.$ext";
        if(file_exists($path)) {
            $profilePic = $path . "?" . time(); // Add timestamp untuk refresh cache
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile - Sleep Tracker</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .profile-page-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
            gap: 0;
            overflow: hidden;
        }

        .profile-sidebar {
            background: linear-gradient(180deg, #283B73 0%, #1a2847 100%);
            padding: 30px 25px;
            display: flex;
            flex-direction: column;
            gap: 30px;
            overflow-y: auto;
            border-right: 1px solid #5E6C8C;
        }

        .profile-sidebar h2 {
            font-size: 18px;
            color: #F8F9FA;
            margin: 0 0 20px 0;
            font-weight: bold;
        }

        .profile-sidebar a {
            padding: 12px 15px;
            background: rgba(141, 153, 174, 0.1);
            border: 1px solid #5E6C8C;
            border-radius: 12px;
            color: #F8F9FA;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
        }

        .profile-sidebar a:hover {
            background: #8D99AE;
            color: #0E1A40;
            border-color: #8D99AE;
            transform: translateX(3px);
        }

        .profile-content-wrapper {
            background-image: url('Gambar/download (7).jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            padding: 30px 35px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
            height: 100vh;
        }

        .profile-content-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        .profile-content-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }

        .profile-content-wrapper::-webkit-scrollbar-thumb {
            background: #5E6C8C;
            border-radius: 3px;
        }

        .profile-content-wrapper::-webkit-scrollbar-thumb:hover {
            background: #8D99AE;
        }

        .profile-header-section {
            background: #283B73;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 25px;
            align-items: start;
            border: 1px solid #5E6C8C;
        }

        .profile-pic-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .profile-pic-container {
            position: relative;
            width: 160px;
            height: 160px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 25px;
            object-fit: cover;
            border: 5px solid #8D99AE;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .profile-pic:hover {
            filter: brightness(1.15);
            transform: scale(1.05);
        }

        .profile-pic-overlay {
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
            opacity: 0;
            cursor: pointer;
            transition: opacity 0.3s ease;
            font-size: 48px;
        }

        .profile-pic-container:hover .profile-pic-overlay {
            opacity: 1;
        }

        .upload-hint {
            color: #8D99AE;
            font-size: 12px;
            text-align: center;
        }

        .profile-info-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-title {
            font-size: 24px;
            font-weight: bold;
            color: #F8F9FA;
            margin: 0;
        }

        .profile-info-box {
            background: #1a2847;
            padding: 20px;
            border-radius: 15px;
            border-left: 5px solid #8D99AE;
            border: 1px solid #5E6C8C;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid #5E6C8C;
        }

        .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #e8ebf0ff;
            font-size: 13px;
        }

        .info-value {
            color: #ebe6e3ff;
            font-size: 14px;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #283B73 0%, #1a2847 100%);
            padding: 25px 20px;
            border-radius: 12px;
            border: 1px solid #5E6C8C;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(141, 153, 174, 0.3);
            border-color: #8D99AE;
        }

        .stat-card-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-card-value {
            font-size: 22px;
            font-weight: bold;
            color: #F8F9FA;
            margin-bottom: 8px;
        }

        .stat-card-label {
            font-size: 12px;
            color: #8D99AE;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .message {
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            text-align: center;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }

        .back-btn, .logout-btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .back-btn {
            background: white;
            color: #0E1A40;
            border: 1px solid #e0d5cc;
        }

        .back-btn:hover {
            background: #0E1A40;
            color: #EFEDEA;
            border-color: #5A4D40;
        }

        .logout-btn {
            background: #F44336;
            color: white;
            border: none;
        }

        .logout-btn:hover {
            background: #d32f2f;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        @media (max-width: 1024px) {
            .profile-page-container {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                border-right: none;
                border-bottom: 1px solid #e0d5cc;
            }

            .profile-header-section {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .profile-pic-section {
                align-items: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .profile-sidebar,
            .profile-content-wrapper {
                padding: 20px;
            }

            .profile-header-section {
                padding: 20px;
            }

            .profile-pic {
                width: 120px;
                height: 120px;
            }

            .profile-pic-overlay {
                width: 120px;
                height: 120px;
            }

            .profile-title {
                font-size: 18px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="profile-page-container">
        <div class="profile-sidebar">
            <h2>Sleep Tracker</h2>
        </div>

        <div class="profile-content-wrapper">
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-header-section">
                <div class="profile-pic-section">
                    <div class="profile-pic-container" onclick="document.getElementById('hiddenFileInput').click()">
                        <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="profile-pic">
                        <div class="profile-pic-overlay">📷</div>
                    </div>
                    <div class="upload-hint">Klik untuk ubah foto</div>
                </div>
                
                <div class="profile-info-section">
                    <h1 class="profile-title">Profil <?= htmlspecialchars($username) ?></h1>
                    
                    <div class="profile-info-box">
                        <div class="info-row">
                            <span class="info-label">👤 Username</span>
                            <span class="info-value"><?= htmlspecialchars($username) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">📊 Total Entri</span>
                            <span class="info-value"><?= count($tracker->getAll()) ?> data</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">😴 Rata-rata</span>
                            <span class="info-value"><?= number_format($tracker->getAverage(), 1) ?> jam/hari</span>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-card-icon">⬆️</div>
                            <div class="stat-card-value"><?php 
                                $all = $tracker->getAll();
                                echo !empty($all) ? max(array_column($all, 'duration')) : '0';
                            ?> h</div>
                            <div class="stat-card-label">Max Sleep</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-icon">⬇️</div>
                            <div class="stat-card-value"><?php 
                                $all = $tracker->getAll();
                                echo !empty($all) ? min(array_column($all, 'duration')) : '0';
                            ?> h</div>
                            <div class="stat-card-label">Min Sleep</div>
                        </div>
                    </div>

                    <div class="button-group">
                        <a href="dashboard.php" class="back-btn">← Kembali</a>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden file input -->
    <form method="POST" enctype="multipart/form-data" id="uploadForm" style="display:none;">
        <input type="file" id="hiddenFileInput" name="profile_pic" accept="image/*" onchange="document.getElementById('uploadForm').submit()">
    </form>
</body>
</html>
