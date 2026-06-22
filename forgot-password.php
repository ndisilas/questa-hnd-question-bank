<?php
require_once 'includes/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'If this email is registered, you will receive a password reset link shortly.';
        } else {
            $error = 'No account found with this email address.';
        }
    } else {
        $error = 'Please enter your email address.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Forgot Password · Questa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.02) 40%, transparent 70%);
            top: -300px;
            right: -200px;
            border-radius: 50%;
            pointer-events: none;
            animation: pulseGlow 8s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.02) 40%, transparent 70%);
            bottom: -250px;
            left: -200px;
            border-radius: 50%;
            pointer-events: none;
            animation: pulseGlow2 10s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
        }

        @keyframes pulseGlow2 {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 0.9; }
        }

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(245, 158, 11, 0.3);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        .particle:nth-child(1) { left: 10%; animation-duration: 12s; }
        .particle:nth-child(2) { left: 25%; animation-duration: 18s; animation-delay: 2s; }
        .particle:nth-child(3) { left: 40%; animation-duration: 14s; animation-delay: 4s; }
        .particle:nth-child(4) { left: 55%; animation-duration: 20s; animation-delay: 1s; }
        .particle:nth-child(5) { left: 70%; animation-duration: 16s; animation-delay: 3s; }
        .particle:nth-child(6) { left: 85%; animation-duration: 13s; animation-delay: 5s; }
        .particle:nth-child(7) { left: 15%; animation-duration: 22s; animation-delay: 7s; }
        .particle:nth-child(8) { left: 50%; animation-duration: 17s; animation-delay: 6s; }

        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        .forgot-wrapper {
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 2;
            animation: fadeUp 0.7s ease-out;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 76px;
            height: 76px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 40px rgba(245, 158, 11, 0.4), 0 0 60px rgba(245, 158, 11, 0.15);
            transition: all 0.3s ease;
            animation: logoPulse 3s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { box-shadow: 0 8px 40px rgba(245, 158, 11, 0.4), 0 0 60px rgba(245, 158, 11, 0.15); }
            50% { box-shadow: 0 8px 60px rgba(245, 158, 11, 0.6), 0 0 100px rgba(245, 158, 11, 0.25); }
        }

        .logo-icon:hover {
            transform: scale(1.05) rotate(-3deg);
        }

        .logo-icon i {
            font-size: 36px;
            color: white;
            filter: drop-shadow(0 2px 10px rgba(0,0,0,0.2));
        }

        .logo-section h1 {
            font-size: 32px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }

        .logo-section h1 span {
            background: linear-gradient(135deg, #f59e0b, #d97706, #fbbf24);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            background-size: 200% 200%;
            animation: gradientShift 4s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .logo-section p {
            color: #94a3b8;
            font-size: 14px;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .forgot-card {
            background: rgba(18, 18, 26, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 32px;
            padding: 44px 40px;
            border: 1px solid rgba(245, 158, 11, 0.15);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255,255,255,0.03);
            position: relative;
            overflow: hidden;
        }

        .forgot-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(245, 158, 11, 0.03), transparent, rgba(139, 92, 246, 0.03), transparent);
            animation: spinGlow 20s linear infinite;
            pointer-events: none;
        }

        @keyframes spinGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .forgot-card .header {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }

        .forgot-card .header h2 {
            font-size: 26px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }

        .forgot-card .header p {
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper .input-icon {
            position: absolute;
            left: 16px;
            color: #64748b;
            font-size: 16px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(15, 15, 23, 0.6);
            border: 1.5px solid rgba(42, 42, 58, 0.8);
            border-radius: 16px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: white;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1), 0 0 30px rgba(245, 158, 11, 0.05);
            background: rgba(20, 20, 30, 0.8);
        }

        .input-wrapper input::placeholder {
            color: #475569;
        }

        .input-wrapper input:focus ~ .input-icon {
            color: #f59e0b;
        }

        .reset-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            color: white;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            z-index: 1;
            box-shadow: 0 4px 25px rgba(245, 158, 11, 0.3);
            position: relative;
            overflow: hidden;
        }

        .reset-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s ease;
        }

        .reset-btn:hover::before {
            left: 100%;
        }

        .reset-btn i {
            font-size: 18px;
        }

        .reset-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 40px rgba(245, 158, 11, 0.5), 0 0 60px rgba(245, 158, 11, 0.15);
        }

        .back-link {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .back-link a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-link a:hover {
            color: #f59e0b;
            text-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
        }

        .back-link a i {
            font-size: 14px;
        }

        .message-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 16px;
            padding: 14px 18px;
            margin-bottom: 24px;
            color: #34d399;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .message-success i {
            font-size: 16px;
            color: #10b981;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 16px;
            padding: 14px 18px;
            margin-bottom: 24px;
            color: #f87171;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .error-message i {
            font-size: 16px;
            color: #ef4444;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            position: relative;
            z-index: 1;
        }

        .footer a {
            color: #f59e0b;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            text-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .forgot-card {
                padding: 28px 20px;
                border-radius: 24px;
            }
            .forgot-card .header h2 {
                font-size: 22px;
            }
            .logo-icon {
                width: 60px;
                height: 60px;
            }
            .logo-icon i {
                font-size: 28px;
            }
            .logo-section h1 {
                font-size: 26px;
            }
            .input-wrapper input {
                padding: 12px 16px 12px 44px;
                font-size: 14px;
            }
            .reset-btn {
                padding: 14px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>

<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="forgot-wrapper">
    <div class="logo-section">
        <div class="logo-icon">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h1>Questa <span>HIPTEX</span></h1>
        <p>Your Digital Sanctuary of Knowledge</p>
    </div>

    <div class="forgot-card">
        <div class="header">
            <h2>🔐 Forgot Password?</h2>
            <p>Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        <?php if ($message): ?>
            <div class="message-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email address</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>
            </div>

            <button type="submit" class="reset-btn">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <div class="back-link">
            <a href="login.php">
                <i class="fas fa-arrow-left"></i> Back to Sign in
            </a>
        </div>

        <div class="footer">
            <p>Remember your password? <a href="login.php">Sign in</a></p>
        </div>
    </div>
</div>

</body>
</html>