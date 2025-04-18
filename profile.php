<?php
require_once 'config.php';

if (!isset($_SESSION["loggedin"])) {
    header("location: login.php");
    exit;
}

// Fetch complete user data
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all transactions
$sql = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Loyalty Rewards</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4A6FA5;
            --secondary: #166088;
            --accent: #4FC3F7;
            --light: #F8F9FA;
            --dark: #343A40;
        }

        .profile-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--accent);
            margin-right: 30px;
        }

        .profile-info h2 {
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .membership-badge {
            display: inline-block;
            padding: 5px 15px;
            background: linear-gradient(135deg, #FFC107, #FF9800);
            color: white;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--light);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h3 {
            color: var(--secondary);
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }

        .transaction-history {
            margin-top: 30px;
        }

        .transaction-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .transaction-points.earned {
            color: #4CAF50;
            font-weight: bold;
        }

        .transaction-points.redeemed {
            color: #F44336;
            font-weight: bold;
        }

        .edit-profile-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?> <!-- Create a header.php for consistent navigation -->
    
    
    <div class="profile-container">
        <div class="profile-header">
            <img src="<?php echo !empty($user['profile_pic']) ? 'profile_pics/' . htmlspecialchars($user['profile_pic']) : 'profile_pics/default.jpg'; ?>" class="profile-pic" alt="Profile Picture">
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <p>Member since: <?php echo date('F Y', strtotime($user['join_date'] ?? $user['reg_date'])); ?></p>
                <span class="membership-badge"><?php echo $user['membership_level']; ?> Member</span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Points</h3>
                <div class="stat-value"><?php echo $user['points']; ?></div>
                <p>Available for redemption</p>
            </div>
            <div class="stat-card">
                <h3>Points Earned</h3>
                <div class="stat-value">
                    <?php 
                    $earned = array_reduce($transactions, function($carry, $item) {
                        return $carry + ($item['type'] == 'earned' ? $item['points'] : 0);
                    }, 0);
                    echo $earned;
                    ?>
                </div>
                <p>Lifetime total</p>
            </div>
            <div class="stat-card">
                <h3>Rewards Redeemed</h3>
                <div class="stat-value">
                    <?php 
                    $redeemed = array_reduce($transactions, function($carry, $item) {
                        return $carry + ($item['type'] == 'redeemed' ? $item['points'] : 0);
                    }, 0);
                    echo $redeemed;
                    ?>
                </div>
                <p>Total points spent</p>
            </div>
        </div>

        <div class="transaction-history">
            <h3>Recent Activity</h3>
            <?php if (count($transactions) > 0): ?>
                <?php foreach ($transactions as $transaction): ?>
                    <div class="transaction-item">
                        <div>
                            <p><?php echo htmlspecialchars($transaction['description']); ?></p>
                            <small><?php echo date('M j, Y g:i a', strtotime($transaction['transaction_date'])); ?></small>
                        </div>
                        <div class="transaction-points <?php echo $transaction['type']; ?>">
                            <?php echo ($transaction['type'] == 'earned' ? '+' : '-') . $transaction['points']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No transaction history found.</p>
            <?php endif; ?>
        </div>

        <button class="edit-profile-btn" onclick="window.location.href='edit_profile.php'">
            <i class="fas fa-user-edit"></i> Edit Profile
        </button>
    </div>
</body>
</html>