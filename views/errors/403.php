<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden | HRnexa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e5490;
            --secondary-color: #2874a6;
            --danger-color: #dc3545;
            --bg-color: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--danger-color), #b21f2d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0;
            line-height: 1;
        }

        h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 15px;
        }

        p {
            color: #6c757d;
            font-size: 18px;
            margin-bottom: 30px;
        }

        .btn-home {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            display: inline-block;
        }

        .btn-home:hover {
            background-color: var(--secondary-color);
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(30, 84, 144, 0.3);
            color: white;
        }

        .illustration {
            position: relative;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% { transform: translate(0, 0px); }
            50% { transform: translate(0, 15px); }
            100% { transform: translate(0, -0px); }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="illustration floating text-danger">
            <i class="fas fa-lock error-icon" style="font-size: 100px; color: var(--danger-color); opacity: 0.2; position: absolute;"></i>
            <div class="error-code">403</div>
        </div>
        <h1>Access Denied!</h1>
        <p>You don't have permission to access this resource. Please contact your administrator if you believe this is an error.</p>
        <a href="../../index.php" class="btn-home">
            <i class="fas fa-arrow-left me-2"></i>Go Back Home
        </a>
    </div>
</body>
</html>