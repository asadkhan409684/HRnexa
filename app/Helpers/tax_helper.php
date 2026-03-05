<?php
/**
 * Tax Helper for HRnexa Payroll
 */

if (!function_exists('calculateTax')) {
    /**
     * Calculate monthly tax deduction based on gross salary.
     * This is a simplified example and can be extended for complex tax slabs.
     * 
     * @param float $gross_salary
     * @return float
     */
    function calculateTax($gross_salary) {
        $tax = 0;
        
        // Simplified Tax Slabs Example:
        // Assume first 30,000 is tax-free
        // Next 20,000 at 5%
        // Next 50,000 at 10%
        // Above 100,000 at 15%
        
        if ($gross_salary > 100000) {
            $tax += ($gross_salary - 100000) * 0.15;
            $gross_salary = 100000;
        }
        
        if ($gross_salary > 50000) {
            $tax += ($gross_salary - 50000) * 0.10;
            $gross_salary = 50000;
        }
        
        if ($gross_salary > 30000) {
            $tax += ($gross_salary - 30000) * 0.05;
        }
        
        return round($tax, 2);
    }
}
?>
