<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRnexa Employee Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e5490;
            --secondary-color: #2874a6;
            --success-color: #27ae60;
            --danger-color: #dc3545;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --light-bg: #f8f9fa;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 25px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand h5 {
            margin: 0;
            font-weight: 700;
            font-size: 18px;
        }

        .sidebar-brand i {
            font-size: 24px;
            color: #ffc107;
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
        }

        .sidebar-menu .menu-section {
            padding: 15px 15px 5px;
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255, 193, 7, 0.1);
            color: white;
            border-left-color: #ffc107;
            padding-left: 25px;
        }

        .sidebar-menu a.active {
            background-color: #ffc107;
            color: var(--primary-color);
            border-left-color: #ffc107;
            font-weight: 600;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
        }

        .sidebar-toggle {
            display: none;
            font-size: 20px;
            color: var(--primary-color);
            margin-right: 15px;
            cursor: pointer;
        }

        .topbar-left h5 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 700;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), var(--info-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        /* KPI Cards */
        .kpi-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 140px;
        }

        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .kpi-card::after {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 80px;
            opacity: 0.1;
            transition: all 0.3s ease;
        }
        
        .kpi-card.card-primary { border-top: 5px solid var(--primary-color); }
        .kpi-card.card-success { border-top: 5px solid var(--success-color); }
        .kpi-card.card-info { border-top: 5px solid var(--info-color); }
        .kpi-card.card-warning { border-top: 5px solid var(--warning-color); }

        .kpi-card.card-primary::after { content: '\f274'; } /* calendar-check */
        .kpi-card.card-success::after { content: '\f017'; } /* clock */
        .kpi-card.card-info::after { content: '\f570'; }    /* file-invoice */
        .kpi-card.card-warning::after { content: '\f005'; } /* star */

        .kpi-value {
            font-size: 32px;
            font-weight: 800;
            color: #2c3e50;
            margin: 5px 0;
            z-index: 2;
        }

        .kpi-label {
            font-size: 13px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            z-index: 2;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 15px;
            padding: 35px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(30, 84, 144, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .welcome-banner h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-banner p {
            opacity: 0.9;
            font-size: 16px;
            max-width: 600px;
        }

        /* Section Card */
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--secondary-color);
            font-size: 22px;
        }

        .info-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* Data Table */
        .data-table {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table table {
            font-size: 14px;
            min-width: 600px; /* Force minimum width to allow scrolling instead of squeezing */
        }

        .data-table thead {
            background-color: var(--light-bg);
        }

        .data-table th {
            font-weight: 600;
            color: var(--primary-color);
            border: none;
            padding: 12px;
        }

        .data-table td {
            padding: 12px;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background-color: rgba(40, 116, 166, 0.05);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 116, 166, 0.25);
        }

        .quick-action-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #eee;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .quick-action-card:hover {
            border-color: var(--secondary-color);
            background-color: rgba(40, 116, 166, 0.05);
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .quick-action-card i {
            font-size: 24px;
            color: var(--secondary-color);
        }

        .quick-action-card span {
            font-weight: 600;
            font-size: 14px;
            color: #444;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
                width: 280px;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-toggle {
                display: block;
            }

            .dashboard-content {
                padding: 15px;
            }
            
            .welcome-banner {
                padding: 25px 20px;
            }
            
            .welcome-banner h1 {
                font-size: 24px;
            }
            
            .topbar {
                padding: 10px 15px;
            }

            .page-header h1 {
                font-size: 22px;
            }
            
            .kpi-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>