<?php
/**
 * API Routes - Handles AJAX requests and returns JSON responses
 * Format: 'url' => ['controller' => 'ControllerName', 'action' => 'methodName']
 */

return [
    '/api/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/api/punch' => ['controller' => 'AttendanceController', 'action' => 'processPunch'],
    '/api/apply-leave' => ['controller' => 'LeaveController', 'action' => 'submitLeave'],
    '/api/upload-doc' => ['controller' => 'DocumentController', 'action' => 'uploadDocument'],
    '/api/update-contact' => ['controller' => 'EmployeeController', 'action' => 'saveEmergencyContact'],
];