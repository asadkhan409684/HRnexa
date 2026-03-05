<?php
/**
 * HomeController - Handles main landing page and basic navigation
 */
class HomeController {
    public function index() {
        // Redirect or include the public index page
        require_once __DIR__ . '/../../public/index.php';
    }
}
